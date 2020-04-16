<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FleetSplitType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                null,
                array(
                    'label' => 'form.name',
                    'attr'  => array(
                        'placeholder' => 'form.name',
                        'class' => 'game-input',
                        'style' => 'width:150px;',
                        'maxlength' => '15',
                        'minlength' => '2',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'sonde',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'colonizer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'recycleur',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'moonMaker',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'radarShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'brouilleurShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'motherShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'cargoI',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'cargoV',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'cargoX',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'hunter',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'fregate',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'barge',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'hunterHeavy',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'hunterWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'corvet',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'corvetLaser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'corvetWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'fregatePlasma',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'croiser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'ironClad',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'destroyer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'niobium',
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
                'water',
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
                'uranium',
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
                'soldier',
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
                'tank',
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
                'worker',
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
                'scientist',
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
            ->add('sendForm', SubmitType::class, ['label' => 'form.scinder', 'attr' => ['class' => 'confirm-button']]);
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