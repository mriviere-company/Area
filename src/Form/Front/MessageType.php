<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Character;

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
                    'required' => false
                ]
            )
            ->add(
                'character',
                EntityType::class,
                [
                    'class' => Character::class,
                    'label' => 'form.character',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.rank is not null')
                            ->orderBy('c.username', 'ASC');
                    },
                    'choice_label' => 'username',
                    'attr'  => [
                        'placeholder' => 'form.character',
                        'class' => 'game-input',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
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
                    'required' => true
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
                    'required' => true
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
                    'required' => false
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.sendMessage', 'attr' => ['class' => 'confirm-button mt-3']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'front_message',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}