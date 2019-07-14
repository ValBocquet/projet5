<?php
namespace App\Controller;

use App\Entity\Users;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */

    public function index(EntityManagerInterface $entityManager, Request $request, UsersRepository $repository, UserPasswordEncoderInterface $encoder) : Response {
        $form = $this->createFormBuilder(array('allow_extra_fields' => true))
            ->add('avatar_img', FileType::class)
            ->getForm();

        $formPass = $this->createFormBuilder(array('allow_extra_fields' => true))
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => true,
                'first_options' => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Confirmez le mot de passe :'],
            ])
            ->getForm();

        $form->handleRequest($request);

        $message = "";
        $state = "";

        if ($form->isSubmitted() && $form->isValid()) {



            $user = $this->getUser();

            $file = $form->get('avatar_img')->getData();
            $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

            $extensionsAutorisees = array('png', 'jpg', 'jpeg');
            if(!in_array($extension, $extensionsAutorisees)) {
                $message = "Extension non autorisée.";
                $state = "alert-warning";
            } else {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored

                /* on peut ajouter un avatar */



                $file->move($this->getParameter('upload_directory'), $fileName);


                $user->setAvatar($fileName);


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // size img avatar

                $manager = new ImageManager();
                $image = $manager->make('upload/' .$fileName)->fit(40,40);
                $image->save('upload/resize_' .$fileName);

                $newNameImage = 'resize_'.$user->getAvatar();
                $user->setAvatar($newNameImage);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // suppression de la grosse image
                $fileSystem = new Filesystem();
                $fileSystem->remove('upload/'.$fileName);

                $message = "Avatar mis à jour";
                $state = "alert-success";


                return $this->render('user/index.html.twig', [
                    'message' => $message,
                    'state' => $state,
                    'form' => $form->createView(),
                    'formPass' => $formPass->createView()
                ]);

            }

        }


        $formPass->handleRequest($request);

        if($formPass->isSubmitted() && $formPass->isValid()) {
            $user = $this->getUser();
            $pass = $formPass->get('password')->getData();


            $hash = $encoder->encodePassword($user, $pass);

            $user->setPassword($hash);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = "Mot de passe mis à jour";
            $state = "alert-success";

            return $this->render('user/index.html.twig', [
                'message' => $message,
                'state' => $state,
                'form' => $form->createView(),
                'formPass' => $formPass->createView()
            ]);
        }


        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            'formPass' => $formPass->createView(),
            'message' => $message,
            'state' => $state
        ]);
    }


        /**
         * @Route("mdp_forgot", name="mdp_forgot")
         */
        public function mdp_forgot(Request $request, UsersRepository $usersRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer) {
            $form = $this->createFormBuilder()
                ->add('email', EmailType::class, array(
                    'attr' => array('class' => 'form_control')
                ))
                ->getForm();
            $form->handleRequest($request);

            $message = "";
            $state = "";
            if($form->isSubmitted() && $form->isValid()) {
                $emailSent = $form->getData()['email'];
                $user = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->findOneByEmail(array('email' => $emailSent));



               if($user != null) {
                   // j'ai une entrée qui correspond à l'adresse email saisie
                   // je créé un nouveau mot de passe pour l'user en question
                   // mot de passe pour se connecter : $newMdp
                   $newMdp = password_hash(uniqid(), PASSWORD_DEFAULT);



                    // encodage du nouveau mot de passe via bcrypt
                   $hash = $encoder->encodePassword($user, $newMdp);


                    $user->setPassword($hash);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    // mise à jour de la base de données

                   // on peut envoyer le mail avec le new mdp


                   $message = (new \Swift_Message('Hello Email'))
                       ->setFrom('admin@valentinbocquet.fr')
                       ->setTo($user->getEmail())
                       ->setBody('Voici votre nouveau mot de passe : ' . $newMdp)


                   ;

                   $mailer->send($message);


                   $message = "Nouveau mot de passe envoyé par e-mail";
                   $state = "alert-success";

               } else {
                   // j'ai pas d'entree
                   $message = "Cette adresse email n'existe pas chez nous";
                   $state = "alert-danger";

                   return $this->render('user/mdp_forgot.html.twig', [
                       'message' => $message,
                       'state' => $state,
                       'form' => $form->createView()
                   ]);

               }
            }



            return $this->render('user/mdp_forgot.html.twig', [
                'form' => $form->createView(),
                'message' => $message,
                'state' => $state
            ]);
        }

}
