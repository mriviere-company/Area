<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FleetEditShipType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'moreCargoI',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'moreCargoV',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'moreCargoX',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'lessCargoI',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'lessCargoV',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'lessCargoX',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'moreSonde',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessSonde',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreColonizer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessColonizer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreRecycleur',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessRecycleur',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreMoonMaker',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessMoonMaker',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreRadarShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessRadarShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreBrouilleurShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessBrouilleurShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreMotherShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessMotherShip',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreHunter',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessHunter',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreFregate',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessFregate',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreBarge',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessBarge',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreHunterHeavy',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreHunterWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreCorvet',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreCorvetLaser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreCorvetWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreFregatePlasma',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreCroiser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreIronClad',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreDestroyer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessHunterHeavy',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessHunterWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessCorvet',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessCorvetLaser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessCorvetWar',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessFregatePlasma',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessCroiser',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessIronClad',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessDestroyer',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input coord text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.putOnGround', 'attr' => ['class' => 'confirm-button']]);
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