<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThresholdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Warning' => 'warning',
                    'Critical' => 'critical',
                    'Success' => 'success'
                ]
            ])
            ->add('value', NumberType::class, [
                'help' => 'Threshold value'
            ])
            ->add('operator', ChoiceType::class, [
                'choices' => [
                    '>' => '>',
                    '<' => '<',
                    '>=' => '>=',
                    '<=' => '<=',
                    '==' => '=='
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }
}