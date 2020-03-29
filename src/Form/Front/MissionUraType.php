<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class MissionUraType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'soldier',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'zbForm nbrSoldier game-input text-right',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'tank',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'zbForm nbrTank game-input text-right',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'time',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getChoices(),
                    'label' => 'form.time',
                    'attr'  => [
                        'placeholder' => 'form.time',
                        'class' => 'zbForm nbrTime planetOrder select2',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.startMission', 'attr' => ['class' => 'confirm-button float-right']]);
    }

    protected function getChoices()
    {
        $translator = new Translator('front_zombie');
        return [
            '' => null,
            $translator->trans('form.one') => 1,
            $translator->trans('form.three') => 3,
            $translator->trans('form.six') => 6,
            $translator->trans('form.ten') => 10
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
                'translation_domain' => 'front_zombie',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}