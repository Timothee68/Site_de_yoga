<?php

namespace App\Form;

use App\Entity\Command;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('name', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('firstName', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('civility', ChoiceType::class, [
                'choices' =>[
                    "Monsieur" => "Monsieur",
                    "Madame"=> "Madame",
                    "Autres"=>"Autres",
                ],
                "attr" => ['class' => "form-control"]
            ])
            ->add('email', EmailType::class,[
                "attr" => ['class' => "form-control"],
            ])
            ->add('telephone', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('city', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('cp', TextType::class, [
                "attr" => ['class' => "form-control"],
            ])
            ->add('adress', TextType::class, [
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
            'data_class' => Command::class,
        ]);
    }
}
