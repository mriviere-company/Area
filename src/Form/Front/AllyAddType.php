<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AllianceAddType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'nameUser',
                null,
                [
                    'label' => 'form.nameUser',
                    'attr'  => [
                        'placeholder' => 'form.nameUser',
                        'class' => 'game-input',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.add', 'attr' => ['class' => 'confirm-button']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_ally',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}