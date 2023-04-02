<?php

namespace App\Controller\Admin;

use App\Entity\State;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class StateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return State::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            ColorField::new('color', 'Couleur'),
            BooleanField::new('active', 'Activer')
        ];
    }
    
}
