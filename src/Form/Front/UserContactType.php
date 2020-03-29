<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'text',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                [
                    'label' => 'form.text',
                    'attr'  => [
                        'placeholder' => 'form.text',
                        'class' => 'form-control',
                        'rows' => 10,
                        'cols' => 75,
                        'maxlength' => '300',
                        'minlength' => '15',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'email',
                null,
                [
                    'label' => 'form.email',
                    'attr'  => [
                        'placeholder' => 'form.email',
                        'class' => 'form-control',
                        'minlength' => '5',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.contact']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_contact',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}