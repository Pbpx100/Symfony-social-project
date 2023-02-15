<?php

namespace App\Form;

use App\Entity\PrivateMessages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PrivateMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['empty_data'];
        $builder
            // ->add('readed')
            //->add('created')
            //->add('sender')
            ->add('receiver', EntityType::class, [
                'class' => User::class,
                'label' => 'To',
                'query_builder' => function (EntityRepository $er) use ($data) {
                    return $data;
                },
                'choice_label' => 'nick',
            ])
            ->add(
                'message',
                TextareaType::class,
                array(
                    'label' => 'Message',


                )
            )
            ->add(
                'file',
                FileType::class,
                array(
                    'label' => 'file',
                    'required' => false,
                    'data_class' => null,
                    'attr' => array('style' => 'display: none;')
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
                'Send',
                SubmitType::class,
                array('attr' => array('class' => 'btn-dark mt-2'))
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrivateMessages::class,
        ]);
    }
}
