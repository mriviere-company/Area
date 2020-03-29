<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Planet;
use Symfony\Component\Translation\Translator;

class FleetSendType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'galaxy',
                null,
                [
                    'label' => 'form.num',
                    'attr'  => [
                        'placeholder' => 'form.num',
                        'class' => 'game-input coord text-right',
                        'min' => '1',
                        'max' => '20',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'sector',
                null,
                [
                    'label' => 'form.num',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.num',
                        'class' => 'game-input coord text-right',
                        'min' => '1',
                        'max' => '100',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'planete',
                null,
                [
                    'label' => 'form.num',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.num',
                        'class' => 'game-input coord text-right',
                        'min' => '1',
                        'max' => '25',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'planet',
                EntityType::class,
                [
                    'class' => Planet::class,
                    'label' => 'form.planet',
                    'query_builder' => function (EntityRepository $er) use($options) {
                        return $er->createQueryBuilder('p')
                            ->join('p.user', 'u')
                            ->where('u.id = :user')
                            ->setParameter('user', $options['user'])
                            ->orderBy('p.name', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr'  => [
                        'placeholder' => 'form.planet',
                        'class' => 'game-input coord',
                        'style' => 'width:150px;',
                    ],
                    'required' => false,
                    'mapped' => false,
                ]
            )
            ->add(
                'flightType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getFlightType(),
                    'label' => 'form.flightType',
                    'attr'  => [
                        'placeholder' => 'form.flightType',
                        'class' => 'game-input coord select2',
                        'style' => 'width:150px;',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.sendFleet', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getFlightType()
    {
        $translator = new Translator('front_fleet');
        return [
            $translator->trans('form.normal') => '1',
            $translator->trans('form.discharge') => '2',
            $translator->trans('form.col') => '3',
            $translator->trans('form.loot') => '4',
            $translator->trans('form.invade') => '5'
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['user']);
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_fleet',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}