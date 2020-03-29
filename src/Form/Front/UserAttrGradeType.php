<?php

namespace App\Form\Front;

use App\Entity\Grade;
use function Couchbase\defaultEncoder;
use Doctrine\ORM\EntityRepository;
use PMA\libraries\config\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserAttrGradeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'grade',
                EntityType::class,
                [
                    'class' => Grade::class,
                    'label' => 'form.grade',
                    'query_builder' => function (EntityRepository $er) use($options) {
                        return $er->createQueryBuilder('g')
                            ->join('g.ally', 'a')
                            ->where('a.id = :id')
                            ->setParameter('id', $options['allyId'])
                            ->orderBy('g.placement', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr'  => [
                        'placeholder' => 'form.grade',
                        'class' => 'game-input',
                    ],
                    'required' => true,
                    'mapped' => false,
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.send', 'attr' => ['class' => 'confirm-button']]);
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
                'translation_domain' => 'front_grade',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => 'task_item'
            ]
        );
    }
}