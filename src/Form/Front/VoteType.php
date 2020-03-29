<?php

namespace App\Form\Front;

use App\Entity\User;
use function Couchbase\defaultEncoder;
use Doctrine\ORM\EntityRepository;
use PMA\libraries\config\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class VoteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'user',
                EntityType::class,
                [
                    'class' => User::class,
                    'label' => 'form.vote',
                    'query_builder' => function (EntityRepository $er) use($options) {
                        return $er->createQueryBuilder('u')
                            ->join('u.ally', 'a')
                            ->join('u.grade', 'g')
                            ->where('a.id = :id')
                            ->setParameter('id', $options['allyId'])
                            ->orderBy('g.placement', 'ASC');
                    },
                    'choice_label' => 'username',
                    'attr'  => [
                        'placeholder' => 'form.vote',
                        'class' => 'game-input',
                        'style' => 'width:150px;',
                    ],
                    'required' => true,
                    'mapped' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.send.vote', 'attr' => ['class' => 'confirm-button']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['allyId']);
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