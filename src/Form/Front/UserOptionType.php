<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserOptionType extends AbstractType
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
                        'value' => $options['username'],
                        'maxlength' => '15',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'oldPassword',
                null,
                [
                    'label' => 'form.oldPassword',
                    'attr'  => [
                        'placeholder' => 'form.oldPassword',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'password',
                null,
                [
                    'label' => 'form.password',
                    'attr'  => [
                        'placeholder' => 'form.password',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'confirmPassword',
                null,
                [
                    'label' => 'form.confirmPassword',
                    'attr'  => [
                        'placeholder' => 'form.confirmPassword',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'newletter',
                CheckboxType::class,
                [
                    'label' => 'form.newletter',
                    'attr'  => [
                        'placeholder' => 'form.newletter',
                        'class' => '',
                        'checked' => $options['newletter']
                    ],
                    'required' => false
                ]
            )
            ->add(
                'connect_last',
                CheckboxType::class,
                [
                    'label' => 'form.connectLast',
                    'attr'  => [
                        'placeholder' => 'form.connectLast',
                        'class' => '',
                        'checked' => $options['connectLast'],
                    ],
                    'required' => false
                ]
            )
            ->add(
                'wallet_address',
                null,
                [
                    'label' => 'form.wallet_address',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.wallet_address',
                        'class' => 'game-input',
                        'value' => $options['wallet_address'],
                        'maxlength' => '30',
                        'minlength' => '10',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.newOptions', 'attr' => ['class' => 'confirm-button float-right']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['username', 'newletter', 'connectLast', 'wallet_address']);
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_options',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}