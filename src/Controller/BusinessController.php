<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\{Category, StepsRequest};
use App\Repository\{CategoryRepository, StepsRequestRepository, UserRepository};

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

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(CategoryRepository $category, StepsRequestRepository $stepsrequest, UserRepository $user): Response
    {
        $client = 2;

        $clients = $user->findBy(['actor' => $client, 'agency' => $this->getUser()->getAgency()]);
        $new_request = $stepsrequest->findBy(['state' => null, 'agency' => $this->getUser()->getAgency()]);
        $current_request = $stepsrequest->findBy(['price' => null, 'agency' => $this->getUser()->getAgency()], ['id' => 'desc'], 5, 0);

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

        //dd(count($current_request));

        return $this->render('business/dashboard.html.twig', [ 
                                'customs' => $clients,
                                'new_request' => $new_request,
                                'current_request' => $current_request,
                                'categNom' => json_encode($categNom),
                                'categColor' => json_encode($categColor),
                                'categCount' => json_encode($categCount),
                                'dates' => json_encode($dates),
                                'stepsCount' => json_encode($stepsCount)                            
                            ]);
    }
}
