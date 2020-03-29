<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ModifPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                    'required' => false,
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
                    'required' => false,
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
                    'required' => false,
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
                        'checked'   => 'checked'
                    ],
                    'required' => false,
                    'mapped' => true,
                ]
            )
            ->add(
                'planetOrder',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getChoices(),
                    'label' => 'form.planetOrder',
                    'attr'  => [
                        'placeholder' => 'form.planetOrder',
                        'class' => 'planetOrder select2',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.newOptions', 'attr' => ['class' => 'confirm-button float-right']]);
    }

    protected function getChoices()
    {
        $translator = new Translator('front_options');
        return [
            '' => null,
            $translator->trans('form.alpha') => 'alpha',
            $translator->trans('form.pos') => 'pos',
            $translator->trans('form.col') => 'colo'
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
                'translation_domain' => 'front_options',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}