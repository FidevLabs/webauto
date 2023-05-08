<?php

namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{Actor, Product, Category, StepsRequest, Agency, Address, User};
use App\Service\ZipFile;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class AdminSubscriber implements  EventSubscriberInterface {

    public const PATH_DOCS = 'uploads/images/docs/';
    public const PATH_ZIP = 'uploads/zip/';

    public function __construct( private ZipFile $zip, 
                                private Security $security, 
                                private EntityManagerInterface $entityManager,
                                private UserPasswordHasherInterface $userPasswordHasher){
                                }

    public static function getSubscribedEvents() {

        return [
            BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            BeforeEntityUpdatedEvent::class => ['setUpdatedAt'],
            AfterEntityUpdatedEvent::class => ['setReference'],
        ];

    }


    public function setCreatedAt(BeforeEntityPersistedEvent $event) {

        $entityInstance = $event->getEntityInstance();

        if (!$entityInstance instanceof Product && !$entityInstance instanceof Category && !$entityInstance instanceof StepsRequest ) return;

        if ($entityInstance instanceof StepsRequest) {

            $zip_name = microtime(true);

            $this->ziperFile($entityInstance->getFile(), self::PATH_ZIP.$zip_name.'.gz');

            $entityInstance->setArchive($zip_name);

            $emailAdmin = $this->entityManager->getRepository(Address::class)->findOneByAgency($this->security->getUser()->getAgency());

            $destinataires = $emailAdmin->getEmail();
            $subject = 'Demande :'.$entityInstance->getCategory()->getName();

            $this->sendMail(
                        $entityInstance->getFile(), 
                        'Bonjour, une nouvelle demande a été déposée par '.$entityInstance->getName(),
                        $subject,
                        $destinataires
                    );

            /**
             * Creation du compte de l'utilisateur
             */
            $username     = $entityInstance->getName();
            $usermail     = $entityInstance->getEmail();
            $userphone    = $entityInstance->getPhone();
            $useractor    = $this->entityManager->getRepository(Actor::class)->findOneByName('Client');
            $roles        = ['ROLE_USER'];

            $user = new User;

            $passhasher = $this->userPasswordHasher;

            $user->setName($username);
            $user->setNickname(' ');
            $user->setEmail($usermail);
            $user->setActor($useractor);
            $user->setAgency($this->security->getUser()->getAgency());
            $user->setRoles($roles);
            $user->setPassword(
                $passhasher->hashPassword(
                    $user,
                    $userphone
                )
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            
            $body = 'Vos dossiers ont été ajoutés et sont en attente d’approbation par les agents.<br />
                    Vous pouvez suivre les démarche en vous connectant avec les identifiant suivant:  
                        ------------
                        ------------
                        Login : '.$entityInstance->getEmail() .'----
                        Mot de passe : '.$entityInstance->getPhone().'
                        
                        ------------
                        
                        Merci de rester en contact !';
            // Email pour le client
            mail($usermail, $subject, $body);
        }

        $entityInstance->setCreatedAt(new \DateTimeImmutable());
    }


    public function setUpdatedAt(BeforeEntityUpdatedEvent $event) {

        $entityInstance = $event->getEntityInstance();

        if (!$entityInstance instanceof Product && !$entityInstance instanceof Category && !$entityInstance instanceof StepsRequest ) return;

        if ($entityInstance instanceof Product && $entityInstance instanceof Category) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        if ($entityInstance instanceof StepsRequest) {

            $step_req = $this->entityManager->getRepository(StepsRequest::class)->find($entityInstance->getId());

            if ( $step_req->getArchive() == null && $entityInstance->getFile() != null ) {
                $zip_name = uniqid('file'.$entityInstance->getId().'-');
                $this->ziperFile($entityInstance->getFile(), self::PATH_ZIP.$zip_name.'.gz');
                $entityInstance->setArchive($zip_name.'.gz');
            }
        }
    }


    public function setReference(AfterEntityUpdatedEvent $event) {
        $entityInstance = $event->getEntityInstance();

        if ($entityInstance instanceof StepsRequest) {

                ($entityInstance->getReference()) ? $reference = $entityInstance->getReference(): $reference = 'Pas encore attribué';

                $destinataire = $entityInstance->getEmail();

                $subject = 'Notification du dossier :'.$entityInstance->getCategory()->getName();;
                
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=utf-8';
                
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
                                                <td style="font-size: 1.1em" class="text-bold">'.$entityInstance->getPrice().' €</td>
                                            </tr>                                        
                                        </table>

                                            Merci de rester en contact.

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
        $texte .= "Content-Type: text/html; charset=\"utf-8\" \n"; 
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

        mail($destinataire,$subject,$texte.$attachement,$mail_mime);

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