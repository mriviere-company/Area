<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class AllyGradeType extends AbstractType
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
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => 'game-input',
                        'maxlength' => '15',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add(
                'canRecruit',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false,
                    'mapped' => true,
                ]
            )
            ->add(
                'canKick',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'canWar',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'canPeace',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'canEdit',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'seeMembers',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'useFleets',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'placement',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getPlacement(),
                    'label' => 'form.placement',
                    'attr'  => [
                        'placeholder' => 'form.placement',
                        'class' => 'game-input select2',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.send', 'attr' => ['class' => 'confirm-button']]);

        $builder->get('name')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagAsFirstUpper) {
                    return lcfirst($tagAsFirstUpper);
                },
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                }
            ));
    }

    protected function getPlacement()
    {
        $translator = new Translator('front_grade');
        return [
            $translator->trans('leader') => '1',
            $translator->trans('coleader') => '2',
            $translator->trans('officer') => '3',
            $translator->trans('member') => '4'
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_grade',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}