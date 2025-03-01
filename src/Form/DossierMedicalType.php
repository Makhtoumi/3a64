<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('allergies', TextareaType::class, ['required' => false])
            ->add('medications', TextareaType::class, ['required' => false])
            ->add('familyMedicalHistory', TextareaType::class, ['required' => false])
            ->add('diagnoses', TextareaType::class, ['required' => false])
            ->add('labResults', TextareaType::class, ['required' => false])
            ->add('vaccinations', TextareaType::class, ['required' => false])
            ->add('patient', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Display the patient's email
                'placeholder' => 'Select a patient',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}
