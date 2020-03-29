<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;

class MessageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'anonymous',
                CheckboxType::class,
                [
                    'label' => 'form.name',
                    'attr'  => [
                        'placeholder' => 'form.name',
                        'class' => '',
                    ],
                    'mapped' => false,
                    'required' => false,
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'class' => User::class,
                    'label' => 'form.user',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.rank is not null')
                            ->orderBy('u.username', 'ASC');
                    },
                    'choice_label' => 'username',
                    'attr'  => [
                        'placeholder' => 'form.user',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add(
                'title',
                null,
                [
                    'label' => 'form.title',
                    'attr'  => [
                        'placeholder' => 'form.title',
                        'class' => 'game-input',
                        'maxlength' => '20',
                        'minlength' => '1',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add(
                'content',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                [
                    'label' => 'form.content',
                    'attr'  => [
                        'placeholder' => 'form.content',
                        'class' => 'game-input',
                        'style' => 'height:100px;',
                        'rows' => 10,
                        'cols' => 75,
                        'maxlength' => '500',
                        'minlength' => '1',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add(
                'bitcoin',
                null,
                [
                    'label' => 'form.num',
                    'data' => 0,
                    'attr'  => [
                        'placeholder' => 'form.num',
                        'class' => 'game-input',
                        'min' => '0',
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'mapped' => true,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.sendMessage', 'attr' => ['class' => 'confirm-button float-right mt-3']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'App\Entity\Message',
                'translation_domain' => 'front_message',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}