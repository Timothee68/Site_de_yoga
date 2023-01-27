<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
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
            ->add('email', EmailType::class,[
                "attr" => ['class' => "form-control"],
            ])
            ->add('messageContent', CKEditorType::class, [
                'config' => array('toolbar' => 'full'),
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
            'data_class' => Contact::class,
        ]);
    }
}