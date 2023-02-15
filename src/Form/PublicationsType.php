<?php

namespace App\Form;

use App\Entity\Publications;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class PublicationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'text',
                TextareaType::class,
                array(
                    'label' => 'Message',


                )
            )
            ->add(
                'image',
                FileType::class,
                array(
                    'label' => 'Image',
                    'required' => false,
                    'data_class' => null,
                    'attr' => array('style' => 'display: none;')
                )

            )

            ->add(
                'document',
                FileType::class,
                array(
                    'label' => 'Document',
                    'required' => false,
                    'data_class' => null,
                    'attr' => array('style' => 'display: none;')
                )
            )
            ->add(
                'Send',
                SubmitType::class,
                array('attr' => array('class' => 'btn-dark mt-2'))
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publications::class,
        ]);
    }
}
