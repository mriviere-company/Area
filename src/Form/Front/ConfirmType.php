<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\CallbackTransformer;

class ConfirmType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                null,
                [
                    'label' => 'form.username',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.username',
                        'class' => 'game-input',
                        'maxlength' => '15',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'password',
                null,
                [
                    'label' => 'form.password',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.password',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'email',
                null,
                [
                    'label' => 'form.email',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.email',
                        'class' => 'game-input',
                        'minlength' => '5',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.confirm', 'attr' => ['class' => 'confirm-button']]);

        $builder->get('username')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                },
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                }
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'App\Entity\User',
                'translation_domain' => 'front_confirm',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}