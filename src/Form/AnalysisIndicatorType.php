<?php
namespace App\Form;

use App\Entity\AnalysisIndicator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnalysisIndicatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Indicator Name'
            ])
            ->add('calculationFormula', TextareaType::class, [
                'label' => 'Calculation Formula',
                'help' => 'Use available metrics like: total_orders, total_appointments, average_order_value. Example: (total_orders / total_appointments) * 100'
            ])
            ->add('thresholds', CollectionType::class, [
                'entry_type' => ThresholdType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => 'Threshold Levels'
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Active'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnalysisIndicator::class,
        ]);
    }
}