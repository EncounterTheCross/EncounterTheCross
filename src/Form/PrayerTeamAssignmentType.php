<?php

namespace App\Form;

use App\Entity\EventPrayerTeamServer;
use App\Entity\PrayerTeam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrayerTeamAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
