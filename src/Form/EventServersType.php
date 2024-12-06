<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\EventParticipant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventServersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
        //
        //
        //            if ($eventEntity instanceof Event) {
        //                $event->getForm()->add('eventParticipants', CollectionType::class, [
        //                    'entry_type' => ServerPrayerTeamAssignmentEmbeddedFormType::class,
        // //                    'by_reference' => false,
        // //                    'allow_add' => true,
        // //                    'allow_delete' => true,
        //                    'data' => $eventEntity->getEventParticipants()->filter(
        //                        function(EventParticipant $participant) use ($options) {
        //                            return $participant->getLaunchPoint()->getId() === $options['launchpoint_id'];
        //                        }
        //                    )
        //                ]);
        //            }
        //        });
        /** @var Event $eventEntity */
        $eventEntity = $builder->getData();

        foreach ($eventEntity->getEventParticipants() as $eventParticipant) {
            if ((!$eventParticipant->getLaunchPoint()->getId() === $options['launchpoint_id'] && $eventParticipant->isServer()) || $eventParticipant->isAttendee()) {
                continue;
            }

            //            $builder->add
        }
        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);

        $resolver->setDefined(['launchpoint_id']);
        $resolver->setAllowedTypes('launchpoint_id', ['int', 'string']);
    }
}
