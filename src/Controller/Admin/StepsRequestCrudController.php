<?php

namespace App\Controller\Admin;

use App\Entity\StepsRequest;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{NumberField, ImageField, AssociationField, TextField, TextEditorField, BooleanField, DateTimeField, MoneyField, IntegerField, IdField, TelephoneField, EmailField, };
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;

class StepsRequestCrudController extends AbstractCrudController
{
    public const ACTION_DUPLICATE = 'duplicate';
    public const DOCS_BASE_PATH = 'uploads/images/docs';
    PUBLIC const DOCS_UPLOAD_DIR = 'public/uploads/images/docs';

    

    public function __construct(private Security $security)
    {
        $this->security = $security;
    }

    

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
{      
        if ($this->getUser()->getRoles() == array('ROLE_USER') || $this->getUser()->getRoles() == array('ROLE_ADMIN')) {
            return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
                ->andWhere('entity.agency = :agency')
                ->setParameter('agency', $this->getUser()->getAgency());
        } else {
            return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        }
    }


    public static function getEntityFqcn(): string
    {

            return StepsRequest::class;

    }

    
    public function configureFields(string $pageName): iterable
    {

        $user = $this->security->getUser();
            
        return [
                //IdField::new('id')->hideOnForm(),
                TextField::new('name', 'Nom et Prénoms/Société')->setColumns(6),

                TelephoneField::new('phone', 'Téléphone')->setColumns(6)->hideOnIndex(),

                EmailField::new('email', 'Adresse email')->setColumns(6),               

                AssociationField::new('category', 'Type')->setQueryBuilder(
                                    function (QueryBuilder $queryBuilder) {
                                    $queryBuilder->where('entity.active = true');
                                })->setColumns(6),
                
                AssociationField::new('agency', 'Agence')->setQueryBuilder(
                                    function (QueryBuilder $queryBuilder) {
                                    $queryBuilder->where('entity.active = true');
                                })->onlyOnIndex()->setColumns(6),

                ImageField::new('file', 'Dossier en (pdf)')
                            ->setBasePath(self::DOCS_BASE_PATH)
                            ->setUploadDir(self::DOCS_UPLOAD_DIR)
                            ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                            ->setFormTypeOption('multiple', true)
                            ->setRequired(false)
                            ->setSortable(false)->hideOnIndex()->setColumns(6),

                DateTimeField::new('createdAt', 'Date d\'entrée')->hideOnForm(),

                NumberField::new('price', 'Facturation')->hideWhenCreating(),
                            //->setPermission('ROLE_ADMIN'),

                AssociationField::new('state', 'Etat du dossier')->setQueryBuilder(
                    function (QueryBuilder $queryBuilder) {
                    $queryBuilder->where('entity.active = true');
                })->onlyWhenUpdating()->setColumns(6),

                //TextEditorField::new('comment', 'Ajouter un commentaire')->hideOnForm()->setColumns(6),
        ];


}

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void  {

        if (!$entityInstance instanceof StepsRequest) return;

        $user = $this->security->getUser();

        $entityInstance->setCreatedAt(new \DateTimeImmutable());

        $entityInstance->setAgency($user->getAgency());

        parent::persistEntity($em, $entityInstance);

    }
    
}
