<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\StepsRequestRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\{TemplatedEmail, Email};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{Address, Category, Agency, ClientMessage, StepsRequest};
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class StepsController extends AbstractController
{
    #[Route('/steps', name: 'app_steps')]
    public function index(StepsRequestRepository $steps, Request $request, EntityManagerInterface $em): Response
    {
        $stepsList = $steps->findAll();
        $categories = $em->getRepository(Category::class)->findAll();

        $email = $em->getRepository(Address::class)->findOneByIsActived(1);

        /**
         * Cette requete Ajax envoi le mail au client 
         */
        if ($request->isXmlHttpRequest()) {
            
            $current_step = $request->get('idstep');
            $stepRequest = $steps->find($current_step);

            $requestCategory = $em->getRepository(Category::class)->find($stepRequest->getId());

            
            $date = $stepRequest->getCreatedAt();
            $client_message = $em->getRepository(ClientMessage::class)->findBy(['agency' => $this->getUser()->getAgency(), 'active' => true]);
            
            //dd($stepRequest->getName());

            ($client_message)? $client_message : $client_message = 'Bonjour, votre dossier est prêt, le traitement a été avec succès';

            $to = $request->get('email');

            $subject = 'Suivi de dossier | WebAutoDemarche';
            
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            
            $message = '
                        <html>
                            <head>
                                <title>$subject</title>
                            </head>
                            <body>
                                <div style="font-size: 1.1em">
                                    '.$client_message.'.<br/>

                                    <br/>

                                    Détails du dossier :
                                </div>

                                <br/>
                                <div>
                                    <table class="table">
                                        <tr>
                                            <th style="text-align: left;">Intitulé :</th>
                                            <td class="text-bold">'.$stepRequest->getName().'</td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;">Numéro de demande :</th>
                                            <td class="text-bold">...</td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;">Date d\'émission :</th>
                                            <td class="text-bold">'.date_format($date, 'd-m-Y H:i:s' ).'</td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;">Type de demande :</th>
                                            <td class="text-bold">'.$requestCategory->getName().'</td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;">Prix :</th>
                                            <td class="text-bold">'.$stepRequest->getPrice().' € </td>
                                        </tr>                                        
                                    </table>
                                    <br/><br/>
                                        Merci de prendre contact avec l\'agence pour plus d\'informations.

                                    <br/><br/>
                                    Cordialement.                                    
                                </p>
                                <br/>                                
                            </body>
                        </html>
                        ';

                if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                    return new Response('ok');
                } else {
                    return new Response('Echec !');
                }               

            }
        
            

        return $this->render('steps/index.html.twig', [
            'steps' => $stepsList,
            'categories' => $categories,
        ]);
    }

    #[Route('/addRequest', name:'app_addrequest')]
    public function newRequest(Request $request, EntityManagerInterface $em) {

        if ($request->isXmlHttpRequest()) {

            $idreq = $request->get('idrequest');

            $stepsReq = $em->getRepository(StepsRequest::class)->find($idreq);

            return  new JsonEncode();
        }
    }
}
