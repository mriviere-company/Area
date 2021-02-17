<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
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
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreNiobiums.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreNiobiums',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreNiobium.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessNiobium',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessNiobiums.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessNiobiums',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessNiobium.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWater',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreWaters.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWaters',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreWater.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWater',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessWaters.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWaters',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessWater.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreUranium',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreUraniums.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreUraniums',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreUranium.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessUranium',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessUraniums.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessUraniums',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessUranium.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreSoldier',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreSoldiers.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreSoldiers',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreSoldier.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessSoldier',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessSoldiers.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessSoldiers',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessSoldier.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreTank',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreTanks.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreTanks',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreTank.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessTank',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessTanks.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessTanks',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessTank.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWorker',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreWorkers.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreWorkers',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreWorker.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWorker',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessWorkers.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessWorkers',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessWorker.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreScientist',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreScientists.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'moreScientists',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_moreScientist.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessScientist',
                RangeType::class,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessScientists.value = this.value'
                    ),
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'lessScientists',
                null,
                array(
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => array(
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                        'oninput' => 'this.form.spatial_edit_fleet_lessScientist.value = this.value'
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