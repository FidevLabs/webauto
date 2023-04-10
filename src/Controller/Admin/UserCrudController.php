<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud, KeyValueStore};
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType};
use Symfony\Component\Form\{FormBuilderInterface, FormEvent, FormEvents};
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\{TemplatedEmail, Email};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Address;

class UserCrudController extends AbstractCrudController
{

    private $passbrut;

    public function __construct( private UserPasswordHasherInterface $userPasswordHasher) { }   

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

        
    public function configureFields(string $pageName): iterable
    {
        

        $fields = [
                    IdField::new('id')->hideOnForm(),
                    TextField::new('name', 'Nom')->setColumns(6),
                    TextField::new('nickname', 'Prénoms')->setColumns(6),
                    //BooleanField::new('isVerified', 'Activer le compte'),
                    EmailField::new('email', 'Adresse email')->setColumns(6),
                    ArrayField::new('roles')->setColumns(6),
                    AssociationField::new('agency', 'Agence')->setQueryBuilder(
                        function (QueryBuilder $queryBuilder) {
                        $queryBuilder->where('entity.active = true');
                    })->setColumns(6),

                    AssociationField::new('actor', 'Type de personne')
                                    ->setColumns(6),

                ];
        
        $password = TextField::new('password' )
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Retapez'],
                'mapped' => false,
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ->setColumns(6)
            ;
        $fields[] = $password;

        return $fields;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword() {

        return function($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            $this->passbrut = $password;

            $hash = $this->userPasswordHasher->hashPassword($form->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
       if (!$entityInstance instanceof User) return;

       $pass = $this->passbrut;
       $to = $entityInstance->getEmail();

       $subject = 'Inscription Web Auto Démarche';
       $from = 'WebAutoDémarche';
       
       $headers[] = 'MIME-Version: 1.0';
       $headers[] = 'Content-type: text/html; charset=iso-8859-1';
       
	   $message = "
                   <html>
                      <head>
                          <title>$subject</title>
                      </head>
                      <body>
                       		<p>Bonjour, votre inscription a bien été prise en compte !</p>
                           <table>
                              <tr>
                                 <th>Login :</th>
                                 <td>$to</td>
                              </tr>
                              <tr>
                                 <th>Mot de passe :</th>
                                 <td>$pass</td>
                              </tr>
                           </table>
                      </body>
                   </html>
                   ";

       mail($to, $subject, $message, implode("\r\n", $headers));


       parent::persistEntity($em, $entityInstance);

    }

    

    
}
