<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pseudo', TextType::class, [
            "attr" => ['class' => "form-control"],
        ])
        ->add('email', EmailType::class,[
            "attr" => ['class' => "form-control"],
        ])
        ->add('img', FileType::class ,[
            'label' => 'Image profile',
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
                    'mimeTypesMessage' => 'svp upload une image valide !!!',
                    ]),
                ]
            ])
        ->add("recaptcha", ReCaptchaType::class)
        ->add('submit',SubmitType::class, [
            "attr" => ['class' => "form-control bg-primary"]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
