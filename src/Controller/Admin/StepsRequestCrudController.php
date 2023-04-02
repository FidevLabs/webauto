<?php

namespace App\Controller\Admin;

use App\Entity\StepsRequest;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;

class StepsRequestCrudController extends AbstractCrudController
{
    public const ACTION_DUPLICATE = 'duplicate';
    public const DOCS_BASE_PATH = 'uploads/images/docs';
    PUBLIC const DOCS_UPLOAD_DIR = 'public/uploads/images/docs';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    public static function getEntityFqcn(): string
    {
        return StepsRequest::class;
    }

    
    public function configureFields(string $pageName): iterable
    {

        $user = $this->security->getUser();

        //dd();

    if ($user->getRoles() == array('ROLE_USER')) {

        
        return [

            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Intitulé/Société')->setColumns(6),

            TelephoneField::new('phone', 'Contact')->setColumns(6),

            EmailField::new('email', 'Adresse email')->setColumns(6),

            AssociationField::new('category', 'Type')->setQueryBuilder(
                                function (QueryBuilder $queryBuilder) {
                                $queryBuilder->where('entity.active = true');
                            })->setColumns(6),

            ImageField::new('file', 'Dossier')
                        ->setBasePath(self::DOCS_BASE_PATH)
                        ->setUploadDir(self::DOCS_UPLOAD_DIR)
                        ->setSortable(false)->setColumns(6),
                
            TextEditorField::new('comment', 'Ajouter un commentaire')->onlyOnDetail(),

            DateTimeField::new('createdAt','Date')->hideOnForm(),

            MoneyField::new('price', 'Coût')
                        ->setCurrency('EUR')
                        ->onlyOnIndex(),

            AssociationField::new('state')->setQueryBuilder(
                function (QueryBuilder $queryBuilder) {
                $queryBuilder->where('entity.active = true');
            })->onlyOnIndex()
            
            
        ];

    } else {
        
        return [
                IdField::new('id')->hideOnForm(),
                TextField::new('name', 'Intitulé/Société')->setColumns(6),

                TelephoneField::new('phone', 'Contact')->setColumns(6),

                EmailField::new('email', 'E-mail')->setColumns(6),               

                AssociationField::new('category', 'Type')->setQueryBuilder(
                                    function (QueryBuilder $queryBuilder) {
                                    $queryBuilder->where('entity.active = true');
                                })->setColumns(6), 

                ImageField::new('file', 'Dossier')
                            ->setBasePath(self::DOCS_BASE_PATH)
                            ->setUploadDir(self::DOCS_UPLOAD_DIR)
                            ->setSortable(false)->setColumns(6),

                DateTimeField::new('createdAt', 'Date d\'entrée')->hideOnForm(),

                MoneyField::new('price', 'Facturation')
                            ->setCurrency('EUR')
                            ->setColumns(6),
                            //->setPermission('ROLE_ADMIN'),

                AssociationField::new('state', 'Etat du dossier')->setQueryBuilder(
                    function (QueryBuilder $queryBuilder) {
                    $queryBuilder->where('entity.active = true');
                })->setColumns(6),

                //TextEditorField::new('comment', 'Ajouter un commentaire')->hideOnForm()->setColumns(6),
        ];

    }

}
    
}
