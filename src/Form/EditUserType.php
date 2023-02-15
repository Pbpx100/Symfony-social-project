<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('email', EmailType::class)

            ->add(
                'nick',
                TextType::class
            )
            ->add(
                'bio',
                TextareaType::class,
                array(
                    'label' => 'Biography',
                    'required' => false,
                    'attr' => array('class' => 'form-bio')
                )
            )

            ->add(
                'image',
                FileType::class,
                array(
                    'label' => 'Photo',
                    'required' => false,
                    'data_class' => null,
                    'attr' => array(
                        'class' => 'form-bio',

                    )
                )
            )
            ->add(
                'save',
                SubmitType::class,
                array('attr' => array('class' => 'btn-dark mt-2'))
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
