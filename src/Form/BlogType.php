<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contents', CKEditorType::class, [
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
            ->add('altImage',TextType::class ,[
                "attr" => [ 'class' => "form-control"],
                ])
            ->add('urlVideo', FileType::class ,[
                'label' => 'Url de la vidéo a importer',
                "attr" => [ 'class' => "form-control"],
                'mapped' => false,
                'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '10G',
                            'mimeTypes' => [
                                'video/mp4',
                                'video/avi',
                                'video/mkv',
                            ],
                            'mimeTypesMessage' => 'Svp upload une vidéo valide',
                        ]),
                    ]
                ])
            ->add('title', TextType::class, [
                "attr" => ['class' => "form-control"]
                ])
                
            ->add('submit',SubmitType::class, [
                "attr" => ['class' => "form-control bg-primary"]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}