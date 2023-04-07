<?php

namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{Product, Category, StepsRequest, Agency, Address};
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\Security\Core\Security;

class AdminSubscriber implements  EventSubscriberInterface {

    public const PATH_DOCS = 'uploads/images/docs/';

    public function __construct(private Security $security, private EntityManagerInterface $entityManager){}

    public static function getSubscribedEvents() {

        return [
            BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            BeforeEntityUpdatedEvent::class => ['setUpdatedAt'],
            AfterEntityUpdatedEvent::class => ['setReference'],
        ];

    }


    public function setCreatedAt(BeforeEntityPersistedEvent $event) {

        $emailAdmin = $this->entityManager->getRepository(Address::class)->findOneByIsActived(1);

        $entityInstance = $event->getEntityInstance();

        if (!$entityInstance instanceof Product && !$entityInstance instanceof Category && !$entityInstance instanceof StepsRequest ) return;

        $this->sendMail(
                    $entityInstance->getFile(), 
                    'Bonjour, une nouvelle demande a été déposée par '.$entityInstance->getName(),
                    'Demande :'.$entityInstance->getCategory()->getName(),
                    $emailAdmin->getEmail()
                );

        $entityInstance->setCreatedAt(new \DateTimeImmutable());
    }


    public function setUpdatedAt(BeforeEntityUpdatedEvent $event) {

        $entityInstance = $event->getEntityInstance();

        if (!$entityInstance instanceof Product && !$entityInstance instanceof Category && !$entityInstance instanceof StepsRequest ) return;

        if ($entityInstance instanceof Product && $entityInstance instanceof Category) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
    }


    public function setReference(AfterEntityUpdatedEvent $event) {
        $entityInstance = $event->getEntityInstance();

        if ($entityInstance instanceof StepsRequest) {

            //dd($entityInstance);

                ($entityInstance->getReference()) ? $reference = $entityInstance->getReference(): $reference = 'Pas encore attribué';

                $destinataire = $entityInstance->getEmail();

                $subject = 'Notification du dossier :'.$entityInstance->getCategory()->getName();;
                
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                
                $message = '
                            <html>
                                <head>
                                    <title>'.$subject.'</title>
                                </head>
                                <body>
                                    <div style="font-size: 1.1em">
                                        Bonjour, une mise à jour a été effectuée sur le dossier.<br/>

                                        <br/>

                                        Détails du dossier :
                                    </div>

                                    <br/>
                                    <div>
                                        <table class="table">
                                            <tr>
                                                <th style="font-size: 1.1em">Intitulé :</th>
                                                <td class="text-bold" style="font-size: 1.1em">'.$entityInstance->getName().'</td>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 1.1em">Numéro de demande :</th>
                                                <td class="text-bold" style="font-size: 1.1em"><mark>'.$reference.'</mark></td>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 1.1em">Date d\'émission :</th>
                                                <td class="text-bold" style="font-size: 1.1em">'.date_format($entityInstance->getCreatedAt(), 'd-m-Y H:i:s' ).'</td>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 1.1em">Type de demande :</th>
                                                <td style="font-size: 1.1em" class="text-bold">'.$entityInstance->getName().'</td>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 1.1em">Prix :</th>
                                                <td style="font-size: 1.1em" class="text-bold">'.$entityInstance->getPrice().'</td>
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

                mail($destinataire, $subject, $message, implode("\r\n", $headers));
        }

    }


    public function sendMail(
                                $fileAttachment,
                                string $mailMessage,
                                string $subject,
                                string $destinataire,

                            ): void {

        //permet de définir les différentes parties du mail
        $limite = "----=_Part_" . md5(uniqid(microtime(), TRUE));

        //headers du mail 
        $mail_mime  = "MIME-Version: 1.0\r\n"; 
        $mail_mime .= "Content-Type: multipart/mixed; boundary=\"".$limite."\"\r\n"; 

        //le corps du message(html)
        //$texte_mail correspond a votre message au format html <html><head>.....</html>

        $texte = "This is a multi-part message in MIME format.\n"; 
        $texte .= "Ceci est un message au format MIME.\n"; 

        //défini la première partie du mail
        $texte .= "--".$limite."\n"; 
        $texte .= "Content-Type: text/html; charset=\"iso-8859-1\"\n"; 
        $texte .= "Content-Transfer-Encoding: quoted-printable\n ";
        $texte .= "Content-Disposition: inline \n\n ";
        $texte .= $mailMessage;
        $texte .= "\n\n";
        $texte .= "\n\n";

        //indice de boucle permettant d'ajouter tous les fichiers joints
        $i=0;

        //les fichiers joints a attacher
        $attachement = '';

            
        //Boucle permettant l'ajout de toutes les pieces jointes
        while($i < sizeof($fileAttachment))
        {
            //permet de récupérer l'extension du fichier afin de définir le type mime
            $longeur = strlen ($fileAttachment[$i]);
            $longeur -=3;

        //on vérifie l'extension
        /*
            Pour les besoins de mon formulaire je devait savoir si le fichier était un .doc ou un .pdf
            il suffit uniquement de modifier cette partie pour ajout n'importe quel type de fichier
        */

        $extension = substr($fileAttachment[$i],$longeur);

        switch ($extension) {

            case "jpg":                
                $type = "application/jpg";
                break;

            case "jpeg":
                $type = "application/jpeg";
                break;

            case "pdf":
                $type = "application/pdf";
                break;

            case "png":
                $type = "application/png";
                break;
            
        }

            //on Ajout chaque parties suivantes du mail avec les pieces jointes  
            $attachement .= "--".$limite."\n";

            $attachement .= "Content-Type: ".$type." name=".$fileAttachment[$i]."\n"; 
            $attachement .= "Content-Transfer-Encoding: base64\n"; 
            $attachement .= "Content-Disposition: attachment; filename=".$fileAttachment[$i]."\n\n"; 
            
            //On lit le fichier présent sur le serveur
            //"rb" permet de lire des fichiers en mode binaire (utile sous windows)
            $fd = fopen( self::PATH_DOCS.$fileAttachment[$i], "rb" ); 
            $contenu = fread( $fd, filesize( self::PATH_DOCS.$fileAttachment[$i] ) ); 

            //encodage en base64 pour que le fichier soit lisible
            $attachement .= chunk_split(base64_encode($contenu)); 
            
            $i++;					 

        }

        //on ferme ensuite toutes les parties du mail
        $attachement .= "\n\n--".$limite."--\n\n";

        if ( mail($destinataire,$subject,$texte.$attachement,$mail_mime) ) 
        {
            //on affiche un message indiquant l'envoi du message
            echo '<p align="left"><font color="green"face="Arial, Helvetica, sans-serif">Demande envoyée </font></p>';
        }
            else 
        {
            //on affiche un message indiquant l'echec de l'envoi du message	
            echo '<p align="left"><font color="red" face="Arial, Helvetica, sans-serif"> Echec de l\'envoi de la demande</font></p>';
        }

    }
}