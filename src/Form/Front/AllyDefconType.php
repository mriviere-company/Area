<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class AllianceDefconType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'defcon',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getDefcon(),
                    'label' => 'form.defcon',
                    'attr'  => [
                        'placeholder' => 'form.defcon',
                        'class' => 'game-input select2',
                        'autocomplete' => 'off',
                        'style' => 'width:150px;'
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.send', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getDefcon()
    {
        $translator = new Translator('front_defcon');
        return [
            $translator->trans('peace') => '0',
            $translator->trans('preparation') => '1',
            $translator->trans('warcoming') => '2',
            $translator->trans('war') => '3'
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'App\Entity\Alliance',
                'translation_domain' => 'front_defcon',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}