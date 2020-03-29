<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SpatialEditFleetType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'moreNiobium',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessNiobium',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWater',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWater',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreUranium',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessUranium',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreSoldier',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessSoldier',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreTank',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessTank',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWorker',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWorker',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreScientist',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessScientist',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.manageFleet', 'attr' => ['class' => 'confirm-button']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_fleet',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}