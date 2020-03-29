<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SalonType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content',
                null,
                [
                    'label' => 'form.content',
                    'attr'  => [
                        'placeholder' => 'form.content',
                        'class' => 'game-input ',
                        'style' => 'height: 30px; font-size: 1.5rem;',
                        'maxlength' => '200',
                        'minlength' => '1',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.sendMessage', 'attr' => ['class' => 'confirm-button', 'style' => 'height: 30px; font-size: 1.5rem;']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_salon',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}