<?php

namespace App\Controller;

use App\Entity\Datas;
use App\Entity\Users;
use App\Form\uploadType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("register", name="register")
     * Method to register an user
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param TokenStorageInterface $storage
     * @return void
     */

    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, TokenStorageInterface $storage)
    {

        if (!empty($this->getUser())) {
            return $this->redirectToRoute('home');
        }
        $user = new Users();


        $form = $this->createFormBuilder($user)
            ->add('pseudo')
            ->add('password', PasswordType::class)
            ->add('email')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsAdmin(0);
            $user->setPremium(0);
            $user->setCreatedAt(new \DateTime());

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            $message = "Inscription confirmée !";
            $state = "alert-success";


            // work over, redirection to the login's page



            return $this->render('login.html.twig', array(
                'message' => trim($message),
                'state' => trim($state),
                'last_username' => trim($user->getEmail())
            ));
        } else {
            return $this->render('register.html.twig', [
                'formUser' => $form->createView()
            ]);
        }

        // form not submitted, display this form
        /*return $this->render('register.html.twig', [
            'formUser' => $form->createView()
        ]);*/
    }

    /**
     * @Route("login", name = "login")
     * Auth's method  
     * @param AuthenticationUtils $authenticationUtils
     * @return void
     */

    public function login(AuthenticationUtils $authenticationUtils)
    {
        $lastEmail = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('login.html.twig', [
            'last_username' => $lastEmail,
            'error' => $error
        ]);

    }

    /**
     * @Route("deconnexion", name="logout")
     * Logout's method
     * @return void
     */
    
    public function logout()
    {

    }

}
