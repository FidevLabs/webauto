<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\StepsRequest;
use App\Repository\CategoryRepository;
use App\Repository\StepsRequestRepository;

class BusinessController extends AbstractController
{
    #[Route('/business', name: 'app_business')]
    public function index(CategoryRepository $category, StepsRequestRepository $stepsrequest): Response
    {
        
        $categories = $category->findAll();

        $categNom = [];
        $categColor = [];
        $categCount= [];

        foreach ($categories as $categorie) {

            $categNom[] = $categorie->getName();
            $categColor[] = $categorie->getColor();
            $categCount[] = count($categorie->getStepsRequests());
        }

        $stepsrequests = $stepsrequest->countByDate();

        $dates = [];
        $stepsCount = [];

        foreach ($stepsrequests as $stepsrequest) {

            $dates[] = $stepsrequest['datesteps']; 
            $stepsCount[] = $stepsrequest['count'];

        }

        return $this->render('business/index.html.twig', [
            'categNom' => json_encode($categNom),
            'categColor' => json_encode($categColor),
            'categCount' => json_encode($categCount),
            'dates' => json_encode($dates),
            'stepsCount' => json_encode($stepsCount)
        ]);
    }
}
