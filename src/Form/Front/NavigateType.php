<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class NavigateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'galaxy',
                null,
                [
                    'label' => 'form.galaxys',
                    'data' => $options['galaxy'],
                    'attr'  => [
                        'placeholder' => 'form.galaxy',
                        'class' => 'game-input text-right',
                        'min' => '1',
                        'max' => '10',
                        'style' => 'width:45px;',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'sector',
                null,
                [
                    'label' => 'form.sectors',
                    'data' => $options['sector'],
                    'attr'  => [
                        'placeholder' => 'form.sector',
                        'class' => 'game-input text-right',
                        'min' => '1',
                        'max' => '100',
                        'autocomplete' => 'off',
                        'style' => 'width:45px;',
                    ],
                    'required' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.navigate', 'attr' => ['class' => 'confirm-button']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['galaxy']);
        $resolver->setRequired(['sector']);
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_galaxy',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}