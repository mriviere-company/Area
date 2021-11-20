<?php

namespace App\Form\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AllyImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'imageFile',
                'Vich\UploaderBundle\Form\Type\VichImageType',
                [
                    'label' => 'form.changeImageFile',
                    'required' => false,
                    'attr'  => [
                        'class'       => '',
                        'accept'      => '.jpg,.jpeg,.bmp,.webp',
                        'autocomplete' => 'off',
                        'data-max-size' => '1'
                    ]
                ]
            )
            ->add('sendForm', SubmitType::class, ['label' => 'form.image']);
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