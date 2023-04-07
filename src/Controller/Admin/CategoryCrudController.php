<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, BooleanField, DateTimeField, ColorField};

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

        
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnDetail(),
            TextField::new('name', 'Titre'),
            ColorField::new('color', 'Couleur'),
            BooleanField::new('active', 'Disponible'),            
            DateTimeField::new('updatedAt', 'modifié le')->hideOnForm()->hideOnDetail(),
            DateTimeField::new('createdAt', 'Créer le')->hideOnForm()->hideOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
       if (!$entityInstance instanceof Category) return;

       $entityInstance->setCreatedAt(new \DateTimeImmutable());

       parent::persistEntity($em, $entityInstance);

    }

    public function deleteEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof Category) return;

        if ($entityInstance->getProducts()) {
            foreach ($entityInstance->getProducts() as $product) {

                $em->remove($product);
                
                parent::deleteEntity($em, $entityInstance);

            }
        }

        parent::deleteEntity($em, $entityInstance);

    }



    
}
