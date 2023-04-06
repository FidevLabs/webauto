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
use App\Entity\{Address, Category, Agency};

class StepsController extends AbstractController
{
    #[Route('/steps', name: 'app_steps')]
    public function index(StepsRequestRepository $steps, Request $request, EntityManagerInterface $em): Response
    {
        $stepsList = $steps->findAll();

        $email = $em->getRepository(Address::class)->findOneByIsActived(1);


        if ($request->isXmlHttpRequest()) {

            $current_step = $request->get('idstep');
            $stepRequest = $steps->find($current_step);

            $requestCategory = $em->getRepository(Category::class)->find($stepRequest->getId());

            $stepRequest->getName();
            $date = $stepRequest->getCreatedAt();
            
            //dd($stepRequest->getName());

            $to = $request->get('email');

            $subject = 'Suivi de dossier | WebAutoDemarche';
            
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
            
            $message = '
                        <html>
                            <head>
                                <title>$subject</title>
                            </head>
                            <body>
                                <div style="font-size: 1.1em">
                                    Bonjour, votre dossier est prêt, le traitement a été avec succès.<br/>

                                    <br/>

                                    Détail du dossier :
                                </div>

                                <br/>
                                <div>
                                    <table class="table">
                                        <tr>
                                            <th>Intitulé :</th>
                                            <td class="text-bold"></td>
                                        </tr>
                                        <tr>
                                            <th>Numéro de demande :</th>
                                            <td class="text-bold">...</td>
                                        </tr>
                                        <tr>
                                            <th>Date d\'émission :</th>
                                            <td class="text-bold">'.date_format($date, 'd-m-Y H:i:s' ).'</td>
                                        </tr>
                                        <tr>
                                            <th>Type de demande :</th>
                                            <td class="text-bold">'.$requestCategory->getName().'</td>
                                        </tr>
                                        <tr>
                                            <th>Prix :</th>
                                            <td class="text-bold">'.$stepRequest->getPrice().'</td>
                                        </tr>                                        
                                    </table>

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
        ]);
    }
}
