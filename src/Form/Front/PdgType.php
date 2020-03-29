<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class PdgType extends AbstractType
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
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.transfert', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getTrade()
    {
        $translator = new Translator('front_ally');
        return [
            $translator->trans('form.give') => '1',
            $translator->trans('form.take') => '2',
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