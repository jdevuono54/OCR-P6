<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * Affiche la page d'accueil
     *
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('base/homepage.html.twig');
    }
}
