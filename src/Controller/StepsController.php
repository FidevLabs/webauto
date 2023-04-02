<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\StepsRequestRepository;

class StepsController extends AbstractController
{
    #[Route('/steps', name: 'app_steps')]
    public function index(StepsRequestRepository $steps): Response
    {
        $stepsList = $steps->findAll();


        return $this->render('steps/index.html.twig', [
            'steps' => $stepsList,
        ]);
    }
}
