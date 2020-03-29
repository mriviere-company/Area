<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaserneRecruitType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'soldier',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrProduct game-input text-right',
                        'max' => '50000',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'tank',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrProduct game-input text-right',
                        'max' => '500',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'nuclear',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrProduct game-input text-right',
                        'max' => '5',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'scientist',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'nbrProduct game-input text-right',
                        'max' => '5000',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.recruitCaserne', 'attr' => ['class' => 'confirm-button float-right']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         =>  null,
                'translation_domain' => 'front_soldier',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}