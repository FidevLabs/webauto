<?php

namespace App\Controller\Admin;

use App\Entity\ClientMessage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{TextEditorField, TextField, BooleanField, AssociationField};
use Doctrine\ORM\QueryBuilder;

class ClientMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientMessage::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('title', 'Objet')->setColumns(6),           

            AssociationField::new('agency', 'Nom de l\'agence')->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                $queryBuilder->where('entity.id ='.$this->getUser()->getAgency()->getId());
            })->setColumns(6), 

            TextEditorField::new('content', 'Contenu')->setColumns(6),

            BooleanField::new('active', 'Activer')->setColumns(7)
        ];
    }
    
}
