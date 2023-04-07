<?php

namespace App\Controller\Admin;

use App\Entity\Agency;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, BooleanField};

class AgencyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Agency::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnDetail(),
            TextField::new('name'),
            BooleanField::new('active', 'Disponible'),

        ];
    }
    
}
