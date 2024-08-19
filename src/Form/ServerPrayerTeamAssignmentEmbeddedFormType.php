<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Entity\PrayerTeam;
use App\Form\DataTransformer\EventParticapentToEventPrayerTeamServerTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerPrayerTeamAssignmentEmbeddedFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            // ... add a choice list of friends of the current application user
            dd($event->getData());
        });

        $builder
            ->addModelTransformer(new EventParticapentToEventPrayerTeamServerTransformer())
            ->add('PrayerTeam', EntityType::class, [
                'class' => PrayerTeam::class,
                'placeholder' => 'Choose a Prayer Team',
            ])
            ->add('Event', EntityType::class, [
                'class' => Event::class,
                'disabled' => true,
            ])
            ->add('EventParticipant', EntityType::class, [
                'class' => EventParticipant::class,
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventPrayerTeamServer::class,
        ]);
    }
}
