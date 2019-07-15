<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PreniumController extends AbstractController
{
    /**
     * @Route("/premium", name="premium")
     */
    /**
     * Display premium's page
     *
     * @return void
     */
    public function index()
    {
        return $this->render('prenium/index.html.twig', [
            'controller_name' => 'PreniumController',
        ]);
    }
}
