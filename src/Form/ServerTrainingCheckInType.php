<?php

namespace App\Form;

use App\Entity\ContactPerson;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\Location;
use App\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerTrainingCheckInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('type')
//            ->add('paid',CheckboxType::class)
            ->add('paymentAmount', MoneyType::class, [
                'mapped' => false,
                'currency' => 'USD',
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => [
                    'Cash' => 'Cash',
                    'Card' => 'Card',
                    'Check' => 'Check',
                    'Online' => 'Online',
                ],
                'label' => 'Payment Type',
                'placeholder' => 'Choose Payment Type',
                'mapped' => false,
            ])
            ->add('paymentMethod', null, [
                'label' => 'Payment Time',
                'disabled' => true,
            ])
//            ->add('status')
//            ->add('launchPoint', EntityType::class, [
//                'class' => Location::class,
//                'choice_label' => 'id',
//            ])
//            ->add('person', EntityType::class, [
//                'class' => Person::class,
//                'choice_label' => 'id',
//            ])
//            ->add('attendeeContactPerson', EntityType::class, [
//                'class' => ContactPerson::class,
//                'choice_label' => 'id',
//            ])
//            ->add('event', EntityType::class, [
//                'class' => Event::class,
//                'choice_label' => 'id',
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventParticipant::class,
        ]);
    }
}
