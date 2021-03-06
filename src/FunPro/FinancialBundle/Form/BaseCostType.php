<?php

namespace FunPro\FinancialBundle\Form;

use FunPro\FinancialBundle\Entity\BaseCost;
use FunPro\GeoBundle\Form\Type\PointType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseCostType
 *
 * @package FunPro\FinancialBundle\Form
 */
class BaseCostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entranceFee', Form\NumberType::class)
            ->add('costPerMeter', Form\NumberType::class)
            ->add('discountPercent', Form\NumberType::class)
            ->add('paymentCashReward', Form\NumberType::class)
            ->add('paymentCreditReward', Form\NumberType::class)
            ->add('location', PointType::class)
            ->add('locationRadius', Form\NumberType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BaseCost::class,
            'validation_groups' => array('Create')
        ));
    }
}
