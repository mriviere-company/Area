<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class ExchangeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'exchangeType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getTrade(),
                    'label' => 'form.pactType',
                    'attr'  => [
                        'placeholder' => 'form.exchangeType',
                        'class' => 'game-input select2',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'amount',
                null,
                [
                    'label' => 'form.num',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.num',
                        'class' => 'game-input text-right',
                        'min' => 1,
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add(
                'valueType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getValue(),
                    'label' => 'form.valueType',
                    'attr'  => [
                        'placeholder' => 'form.valueType',
                        'class' => 'game-input select2',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'content',
                null,
                [
                    'label' => 'form.content',
                    'attr'  => [
                        'placeholder' => 'form.content',
                        'class' => 'game-input',
                        'maxlength' => '200',
                        'minlength' => '1',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.transfert', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getTrade()
    {
        $translator = new Translator('front_ally');
        return [
            $translator->trans('form.give') => '1',
            $translator->trans('form.take') => '2'
        ];
    }

    protected function getValue()
    {
        $translator = new Translator('front_ally');
        return [
            $translator->trans('form.bitcoin') => '1',
            $translator->trans('form.pdg') => '2'
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