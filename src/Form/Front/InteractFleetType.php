<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Fleet;
use Symfony\Component\Translation\Translator;

class InteractFleetType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'list',
                EntityType::class,
                [
                    'class' => Fleet::class,
                    'label' => 'form.fleet',
                    'query_builder' => function (EntityRepository $er) use($options) {
                        return $er->createQueryBuilder('f')
                            ->join('f.commander', 'c')
                            ->where('c.id = :commander')
                            ->andWhere('f.flightAt is null')
                            ->andWhere('f.fightAt is null')
                            ->setParameters(['commander' => $options['commander']])
                            ->orderBy('f.name', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr'  => [
                        'placeholder' => 'form.fleet',
                        'class' => 'game-input',
                    ],
                    'required' => true,
                    'mapped' => false,
                ]
            )
            ->add(
                'flightType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'choices' => $this->getFlightAt(),
                    'label' => 'form.flightType',
                    'attr'  => [
                        'placeholder' => 'form.flightType',
                        'class' => 'game-input select2',
                    ],
                    'required' => true
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.sendFleet', 'attr' => ['class' => 'confirm-button']]);
    }

    protected function getFlightAt()
    {
        $translator = new Translator('front_fleet');
        return [
            $translator->trans('form.normal') => '1',
            $translator->trans('form.discharge') => '2',
            $translator->trans('form.col') => '3',
            $translator->trans('form.loot') => '4',
            $translator->trans('fleet.invade') => '5'
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['commander']);
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