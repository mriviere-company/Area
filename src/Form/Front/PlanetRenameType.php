<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\CallbackTransformer;

class PlanetRenameType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'id',
                HiddenType::class,
                [
                    'label' => 'form.id',
                    'attr'  => [
                        'placeholder' => 'form.id',
                        'class' => 'form-control',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'name',
                null,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => 'game-input',
                        'maxlength' => '15',
                        'minlength' => '2',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.renamePlanet', 'attr' => ['class' => 'btn-sm confirm-button float-right', 'style' => 'padding:0.1rem 1rem;']]);

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
                'data_class'         =>  null,
                'translation_domain' => 'front_overview',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}