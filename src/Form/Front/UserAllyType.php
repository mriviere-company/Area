<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;

class UserAllyType extends AbstractType
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
                    'required' => true
                ]
            )
            ->add(
                'sigle',
                null,
                [
                    'label' => 'form.sigle',
                    'attr'  => [
                        'placeholder' => 'form.sigle',
                        'class' => 'game-input',
                        'maxlength' => '4',
                        'minlength' => '2',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'slogan',
                null,
                [
                    'label' => 'form.slogan',
                    'attr'  => [
                        'placeholder' => 'form.slogan',
                        'class' => 'game-input',
                        'maxlength' => '30',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'politic',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getPolitic(),
                    'label' => 'form.politic',
                    'attr'  => [
                        'placeholder' => 'form.politic',
                        'class' => 'select2 game-input',
                    ],
                    'required' => true
                ]
            )
            ->add(
                'description',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                [
                    'label' => 'form.description',
                    'attr'  => [
                        'placeholder' => 'form.description',
                        'class' => 'game-input',
                        'maxlength' => '1000',
                        'rows' => 8,
                        'autocomplete' => 'off',
                        'style' => 'height:100px;',
                    ],
                    'required' => false
                ]
            )
            ->add(
                'taxe',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getPercentTaxe(),
                    'label' => 'form.taxeAlly',
                    'attr'  => [
                        'placeholder' => 'form.taxe',
                        'class' => 'select2 game-input',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.send', 'attr' => ['class' => 'confirm-button pull-right mt-3']]);

        $builder->get('sigle')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagAsUpper) {
                    return strtolower($tagAsUpper);
                },
                function ($tagAsUpper) {
                    return strtoupper($tagAsUpper);
                }
            ));
        $builder->get('name')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                },
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                }
            ));
        $builder->get('slogan')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                },
                function ($tagAsFirstUpper) {
                    return ucfirst($tagAsFirstUpper);
                }
            ));
    }

    protected function getPolitic()
    {
        $translator = new Translator('front_ally');
        return [
            $translator->trans('democrat') => 'democrat',
            $translator->trans('fascism') => 'fascism',
            $translator->trans('communism') => 'communism'
         /*   $translator->trans('anarchism') => 'anarchism',
            $translator->trans('monarchy') => 'monarchy',
            $translator->trans('theocrat') => 'theocrat'*/
        ];
    }

    protected function getPercentTaxe()
    {
        return [
            '05%' => '5',
            '10%' => '10',
            '15%' => '15',
            '20%' => '20',
            '25%' => '25',
            '30%' => '30',
            '35%' => '35',
            '40%' => '40',
            '45%' => '45',
            '50%' => '50',
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'App\Entity\Ally',
                'translation_domain' => 'front_ally',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}