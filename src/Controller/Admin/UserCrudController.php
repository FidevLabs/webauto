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
                    ArrayField::new('roles')->setColumns(6)
                ];
        
        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => '(Repeat)'],
                'mapped' => false,
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
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

       $msg = '<div>Votre compte a été crée: Login '. $entityInstance->getEmail().'<br/> Mot de passe :'.$pass.'</div>';

       //mail($entityInstance->getEmail(),"WebAutoDemarche - Accès de connexion",$msg);

       function sendEmail(MailerInterface $mailer, $entityInstance, $pass)  {

            $email = (new TemplatedEmail())
                ->from('mail@mail.exemple')
                ->to($entityInstance->getEmail())
                ->subject('WebAutoDemarche - Accès de connexion')
                ->html('<div>Votre compte a été crée: Login '. $entityInstance->getEmail().'<br/> Mot de passe :'.$pass.'</div>');

            $mailer->send($email);

            return $email;
        }


       parent::persistEntity($em, $entityInstance);

    }

    

    
}
