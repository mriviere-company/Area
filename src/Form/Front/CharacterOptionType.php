<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CharacterOptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                null,
                [
                    'label' => 'form.username',
                    'data' => null,
                    'attr'  => [
                        'placeholder' => 'form.username',
                        'class' => 'game-input',
                        'value' => $options['username'],
                        'maxlength' => '15',
                        'minlength' => '3',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
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

        $builder->get('username')
            ->addModelTransformer(new CallbackTransformer(
                  function ($tagAsFirstUpper) {
                      return ucfirst($tagAsFirstUpper);
                  },
                  function ($tagAsFirstUpper) {
                      return ucfirst($tagAsFirstUpper);
                  }
              ));
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
        $resolver->setRequired(['username']);
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