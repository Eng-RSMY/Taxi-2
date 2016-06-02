<?php

namespace FunPro\DriverBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class CarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', Type\TextType::class)
            ->add('plaque', Type\TextType::class)
            ->add('color', Type\TextType::class)
            ->add('born', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text'
            ))
            ->add('rate', Type\NumberType::class, array(
                'scale' => 2,
            ))
            ->add('description', Type\TextareaType::class, array('required' => false))
            ->add('current', Type\CheckboxType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\DriverBundle\Entity\Car'
        ));
    }
}
