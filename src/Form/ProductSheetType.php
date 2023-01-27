<?php

namespace App\Form;

use App\Entity\ProductSheet;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProductSheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('price', MoneyType::class,[
                "attr" => ['class' => "form-control"],
            ])
            ->add('stock', IntegerType::class,[
                "attr" => ['class' => "form-control"],
            ])
            ->add('shortDescription', CKEditorType::class, [
                'config' => array('toolbar' => 'full'),
                "attr" => ['class' => "form-control"]
                ])
            ->add('image', FileType::class ,[
                'label' => 'Image réliée à la prestation',
                "attr" => [ 'class' => "form-control"],
                'mapped' => false,
                'required' => false,
                    'constraints' => [
                        new File([
                        'maxSize' => '10254k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload une image valide',
                        ]),
                    ]
                ])
            ->add('alt',TextType::class ,[
                "attr" => [ 'class' => "form-control"],
                ])
            ->add('submit',SubmitType::class, [
                "attr" => ['class' => "form-control bg-primary"]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductSheet::class,
        ]);
    }
}
