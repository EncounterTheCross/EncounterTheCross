<?php

namespace App\Form;

use App\Entity\EventPrayerTeamServer;
use App\Entity\PrayerTeam;
use App\Form\DataTransformer\EventParticapentToEventPrayerTeamServerTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerPrayerTeamAssignmentEmbeddedFormType extends AbstractType
{
    //    public function __construct(private EntityManagerInterface $entityManager)
    //    {
    //    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
        //            // ... add a choice list of friends of the current application user
        //            dump($event->getData());
        //        });

        $builder
//            ->addModelTransformer(new EventParticapentToEventPrayerTeamServerTransformer())
            ->add('PrayerTeam', EntityType::class, [
                'class' => PrayerTeam::class,
                'placeholder' => 'Choose a Prayer Team',
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
