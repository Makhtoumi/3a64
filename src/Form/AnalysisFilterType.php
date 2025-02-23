<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnalysisFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_date', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('end_date', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false
            ])
         ;
    }
}