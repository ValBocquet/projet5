<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionController extends AbstractController
{
    /**
     * @Route("exception", name="exception")
     * Function for catch exception url and return a template
     *
     * @return void
     */
    public function showException()
    {
        return $this->render('exception/index.html.twig', [
            'controller_name' => 'ExceptionController',
        ]);
    }
}
