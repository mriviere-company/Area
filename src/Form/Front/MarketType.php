<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use function Couchbase\defaultEncoder;
use Doctrine\ORM\EntityRepository;
use PMA\libraries\config\Form;
use App\Entity\Planet;

class MarketType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'planet',
                EntityType::class,
                [
                    'class' => Planet::class,
                    'label' => 'form.planet',
                    'query_builder' => function (EntityRepository $er) use($options) {
                        return $er->createQueryBuilder('p')
                            ->join('p.character', 'c')
                            ->where('c.id = :character')
                            ->setParameter('character', $options['character'])
                            ->orderBy('p.name', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr'  => [
                        'placeholder' => 'form.planet',
                        'class' => 'game-input',
                    ],
                    'required' => false,
                    'mapped' => false,
                ]
            )
            ->add(
                'bitcoin',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'worker',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'soldier',
                null,
                [
                    'label' => 'form.nbr',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.nbr',
                        'class' => 'game-input text-right',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.buyMarket', 'attr' => ['class' => 'confirm-button float-right']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['character']);
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_market',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}