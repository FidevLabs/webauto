<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, ImageField};

class PaymentCrudController extends AbstractCrudController
{
    public const DOCS_BASE_PATH = 'uploads/payment/icone';
    PUBLIC const DOCS_UPLOAD_DIR = 'public/uploads/payment/icone';

    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Titre')->setColumns(6),
            ImageField::new('img', 'Icone')->setBasePath(self::DOCS_BASE_PATH)
                                            ->setUploadDir(self::DOCS_UPLOAD_DIR)
                                            ->setUploadedFileNamePattern('[day]-[month]-[year]-[contenthash].[extension]')
                                            ->setFormTypeOption('multiple', false)
                                            ->setRequired(false)
                                            ->setSortable(false)->setColumns(6),
        ];
    }
    
}
