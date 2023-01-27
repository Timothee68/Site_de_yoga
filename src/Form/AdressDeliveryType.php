<?php

namespace App\Form;

use App\Entity\AdressDelivery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AdressDeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('civility', ChoiceType::class, [
                'choices' =>[
                    "Monsieur" => "Monsieur",
                    "Madame"=> "Madame",
                    "Autres"=>"Autres",
                ],
                "attr" => ['class' => "form-control"]
            ])
            ->add('name', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('firstName', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('city', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('adress', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('cp', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('submit',SubmitType::class, [
                "attr" => ['class' => "form-control bg-primary"]
            ])
            ->add("recaptcha", ReCaptchaType::class)
            ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdressDelivery::class,
        ]);
    }
}
