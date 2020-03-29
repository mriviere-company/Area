<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShipPersoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ship',
                null,
                [
                    'label' => 'form.name',
                    'required' => false,
                ]
            )
            ->add(
                'armor',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'shield',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'accurate',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'missile',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'laser',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'plasma',
                IntegerType::class,
                [
                    'label' => 'form.nbr',
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrPoint game-input text-left',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.change', 'attr' => ['class' => 'confirm-button']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_ship_perso',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}