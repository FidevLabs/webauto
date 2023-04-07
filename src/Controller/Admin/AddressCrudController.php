<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\{TextField, AssociationField, BooleanField};
use Doctrine\ORM\QueryBuilder;

class AddressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('email', 'Adresse email')->setColumns(6),

            AssociationField::new('agency', 'Nom de l\'agence')->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                $queryBuilder->where('entity.id ='.$this->getUser()->getAgency()->getId());
            })->setColumns(6), 
            
            BooleanField::new('isactived', 'Activer')->setColumns(6),
        ];
    }
    

    /*public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
       if (!$entityInstance instanceof Address) return;

       $entityInstance->getEmail();

       parent::persistEntity($em, $entityInstance);

    }*/
}
