<?php

namespace App\Form;

use App\Entity\EventParticipant;
use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AttendeeEventParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var EventParticipant $data */
        $data = $options['data'] ?? null;

        $builder
            ->add('person', PersonType::class)
            ->add('line1',null,[
                'label' => 'Address',
                'attr' => [
                    'placeholder' => 'Address',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => true,
                'constraints' => [
                    new NotNull(
                        message: 'Whats your address?'
                    ),
                ],
            ])
            ->add('line2',null,[
                'label' => 'Address 2',
                'attr' => [
                    'placeholder' => 'Address 2',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('city',null,[
                'label' => 'City',
                'attr' => [
                    'placeholder' => 'City',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => true,
                'constraints' => [
                    new NotNull(
                        message: 'What city are you from?'
                    ),
                ],
            ])
            ->add('state', StateType::class)
            ->add('zipcode',null,[
                'label' => 'Zip',
                'attr' => [
                    'placeholder' => 'Zip',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => true,
                'constraints' => [
                    new NotNull(
                        message: 'Whats your Zip Code?'
                    ),
                ],
            ])
            ->add('country',HiddenType::class,[
                'data' => Countries::exists('US') ? 'US' : '',
            ])

            ->add('attendeeContactPerson', ContactPersonType::class)

            ->add('church',null,[
                'label' => 'What church do you attend (if any)?',
                'attr' => [
                    'placeholder' => 'What church do you attend (if any)?',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('invitedBy',null,[
                'label' => 'Who invited you?',
                'attr' => [
                    'placeholder' => 'Who invited you?',
                ],
                'help' => 'Please provide full name of person.',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('type', HiddenType::class,[
                'data' => EventParticipant::TYPE_ATTENDEE,
            ])

            ->add('questionsOrComments', TextareaType::class,[
                'label' => 'Questions or Comments?',
                'attr' => [
                    'placeholder' => 'Questions or Comments?',
                    'style' => "height: 100px",
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => false,
            ])
            ->add('healthConcerns',TextareaType::class,[
                'label' => 'Concerns?',
                'attr' => [
                    'placeholder' => 'Concerns?',
                    'style' => "height: 100px",
                ],
                'help' => 'Let us know if you have any dietary concerns, physical limitations or health concerns',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => false,
            ]);
        $launchPointOptions = [
            'placeholder' => 'Please choose a Launch Point',
            'help' => 'The Launch Point is the area location we will meet up before attending Encounter.',
            'class' => Location::class,
            'label' => 'Launch Point',
            'required' => true,
            'attr' => [
                'placeholder' => 'Launch Point',
            ],
            'row_attr' => [
                'class' => 'form-floating',
            ],
        ];
        if ($data) {
            $launchPointOptions['choices'] = $data->getEvent()->getLaunchPoints()->toArray();
        }
        $builder
            ->add('launchPoint',null,$launchPointOptions)
            ->add('paymentMethod',ChoiceType::class,[
                'label' => 'Payment Method',
                'required' => true,
                'choices' => array_combine(['Pay at the door', 'Apply for Scholarship'],EventParticipant::PAYMENT_METHODS),
                'placeholder' => 'Select Payment Method',
                'attr' => [
                    'placeholder' => 'Payment Method',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventParticipant::class,
        ]);
    }
}
