<?php

namespace App\Form;

use App\Entity\EventParticipant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerTrainingCheckInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('PayAtEncounter', SubmitType::class, [
                'row_attr' => ['class' => 'w-1/2'],
                'attr' => [
                    'class' => 'w-full px-6 py-3.5 text-base font-medium text-white inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800',
                ],
            ])
            ->add('PaidAlready', SubmitType::class, [
                'row_attr' => ['class' => 'w-1/2'],
                'attr' => [
                    'value' => 'ap',
                    'class' => 'w-full px-6 py-3.5 text-base font-medium text-white inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //            'data_class' => EventParticipant::class,
        ]);
    }
}
