<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class AlliancePactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'allyName',
                null,
                [
                    'label' => 'form.allyName',
                    'attr'  => [
                        'placeholder' => 'form.allyName',
                        'class' => 'game-input',
                        'minlength' => '1',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'pactType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getPactType(),
                    'label' => 'form.pactType',
                    'attr'  => [
                        'placeholder' => 'form.pactType',
                        'class' => 'select2 game-input',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.add', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getPactType()
    {
        $translator = new Translator('front_ally');
        return [
            $translator->trans('form.defensive') => '1',
            $translator->trans('form.pna')=> '2',
            $translator->trans('form.war') => '3',
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
                'translation_domain' => 'front_ally',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}