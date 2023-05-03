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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\StepsRequestType;
use App\Service\ZipFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\String\Slugger\SluggerInterface;

class StepsController extends AbstractController
{
    public const PATH_DOCS = 'uploads/images/docs/';
    public const PATH_ZIP = 'uploads/zip/';

    public function __construct(private ZipFile $zip){}

    #[Route('/steps', name: 'app_steps')]
    public function index(SluggerInterface $slugger, AdminUrlGenerator $adminUrlGenerator, StepsRequestRepository $steps, Request $request, EntityManagerInterface $em): Response
    {
        $stepsList = $steps->findBy(array(),  ['id' => 'desc'], 10, 0);
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
        
        $stepsRequest = new StepsRequest;

        $form = $this->createForm(StepsRequestType::class, $stepsRequest);

        $form->handleRequest($request);
       

        if ($form->isSubmitted()) {

            $files = $form->get('files')->getData();

            $name = $request->get('_name');
            $agency =  $request->get('_agence');
            $email = $request->get('_email');
            $phone = $request->get('_phone');

            $agency = $em->getRepository(Agency::class)->findOneByName($agency);

           

            $stepsRequest->setName($name);
            $stepsRequest->setEmail($email);
            $stepsRequest->setPhone($phone);
            $stepsRequest->setAgency($agency);            
            $stepsRequest->setCreatedAt(new \DateTimeImmutable());    
            
            $zip_name = microtime(true);

            if ($files) {
                for ($i=0;$i < count($files);$i++) {

                    $originalFilename []= pathinfo($files[$i]->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename []= $slugger->slug($originalFilename[$i]);
                    $newFilename []= $safeFilename[$i].'-'.uniqid().'.'.$files[$i]->guessExtension();

                    try {
                        $files[$i]->move(
                            $this->getParameter('doc_dir'),
                            $newFilename[$i]
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    $stepsRequest->setFile($newFilename);
                }
            }

            $em->persist($stepsRequest);
            $em->flush();
            
        }
        
        $url =  $adminUrlGenerator
                    ->setRoute('app_addsteprequest')
                    ->generateUrl();

        return $this->render('steps/index.html.twig', [
            'steps' => $stepsList,
            'categories' => $categories,
            'url' => $url,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/addRequest', name:'app_addrequest')]
    public function newRequest(Request $request, EntityManagerInterface $em) {

        if ($request->isXmlHttpRequest()) {

            $idreq = $request->get('idrequest');

            $stepsReq = $em->getRepository(StepsRequest::class)->find($idreq);

            return new Response(json_encode(['name' => $stepsReq->getName(), 
                                'phone' => $stepsReq->getPhone(),
                                'email' => $stepsReq->getEmail(),
                                'agence' => $stepsReq->getAgency()->getName()]));
        }
    }

    public function ziperFile($files, $pathfile): void {

        $i = 0 ;

        while ( count( $files ) > $i )   {
            $fo = fopen(self::PATH_DOCS.$files[$i],'r') ; //on ouvre le fichier
            $contenu = fread($fo, filesize(self::PATH_DOCS.$files[$i])) ; //on enregistre le contenu
            fclose($fo) ; //on ferme fichier
            $this->zip->addfile($contenu, $files[$i]) ; //on ajoute le fichier
            $i++; //on incrémente i
        }

        $archive = $this->zip->file() ; // on associe l'archive
        // on enregistre l'archive dans un fichier
        $open = fopen( $pathfile , "wb");
        fwrite($open, $archive);
        fclose($open);

    }

}
