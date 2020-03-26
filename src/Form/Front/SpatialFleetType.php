<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\CallbackTransformer;

class SpatialFleetType extends AbstractType
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
            ->add('sendForm', SubmitType::class, ['label' => 'form.addFleet', 'attr' => ['class' => 'confirm-button float-right']]);

        $builder->get('name')
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
                'data_class'         => null,
                'translation_domain' => 'front_spatial',
            ]
        );
    }
}