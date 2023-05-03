<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\StepsRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class StepsRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', FileType::class,  [
                    'label' => 'Dossier (jpg ou PDF) : ',
                    'mapped' => false,
                    'required' => false,
                    'multiple' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                                'application/jpg',
                                'application/x-jpg'
                            ],
                            'mimeTypesMessage' => 'Please upload a valid document',
                        ])
                    ]
                ])
            ->add('presta_price', IntegerType::class, ['label' => 'Prix de prestation'])
            ->add('category', EntityType::class, ['label' => 'Catégorie', 'class' => Category::class, 'choice_label' => 'name'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StepsRequest::class,
        ]);
    }
}
