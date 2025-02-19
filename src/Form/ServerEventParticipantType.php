<?php

namespace App\Form;

use App\Entity\EventParticipant;
use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ServerEventParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var EventParticipant $data */
        $data = $options['data'] ?? null;

        $builder
            ->add('person', PersonType::class)
            ->add('line1', null, [
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
            ->add('line2', null, [
                'label' => 'Address 2',
                'attr' => [
                    'placeholder' => 'Address 2',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('city', null, [
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
            ->add('zipcode', null, [
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
            ->add('country', HiddenType::class, [
                'data' => Countries::exists('US') ? 'US' : '',
            ])

            ->add('serverAttendedTimes', IntegerType::class, [
                'label' => 'Times Serving',
                'attr' => [
                    'placeholder' => 'How many times have you served at an Encounter prior to this one?',
                ],
                'help' => 'How many times have you served at an Encounter prior to this one?',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => false,
            ])
            ->add('type', HiddenType::class, [
                'data' => EventParticipant::TYPE_SERVER,
            ])
            ->add('questionsOrComments', TextareaType::class, [
                'label' => 'Questions or Comments?',
                'attr' => [
                    'placeholder' => 'Questions or Comments?',
                    'style' => 'height: 100px',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => false,
            ])
            ->add('healthConcerns', TextareaType::class, [
                'label' => 'Concerns?',
                'attr' => [
                    'placeholder' => 'Concerns?',
                    'style' => 'height: 100px',
                ],
                'help' => 'Let us know if you have any dietary concerns, physical limitations or health concerns',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => false,
            ]);
        $launchPointOptions = [
            'placeholder' => 'Please choose a Launch Point',
            'help' => 'The Launch Point is the location in the list below that is closest to you and where we will initially meet. If you are located more than 100 miles from one of these locations, please select "Venue" and we will contact you with more details.',
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
            ->add('launchPoint', null, $launchPointOptions)
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Payment Method',
                'required' => true,
                'choices' => ['Pay at the door' => EventParticipant::PAYMENT_METHOD_ATDOOR], // array_combine(['Pay at the door', 'Apply for Scholarship'],EventParticipant::PAYMENT_METHODS),
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
