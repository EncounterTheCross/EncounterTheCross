<?php

namespace App\Service;

use App\Entity\Person;
use App\Repository\PersonRepository;

class PersonManager
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {
    }

    /**
     * 1. look up by email
     * 2. see if first name and last name are the same, if so return 1st found
     *      else return initial person submitted.
     *
     * Will return the Submitted person if none found matching in database.
     */
    public function exists(Person $person): Person
    {
        $found = $this->personRepository->findBy([
            'email' => $person->getEmail(),
        ]);

        if (empty($found)) {
            // No one found
            return $person;

            //            // TODO: issue here could be duplicates of a contact person place holder.
            //            $found = $this->personRepository->findBy([
            //                'phone' => $person->getPhone(),
            //            ]);
        }

        // Found someone
        // ALWAYS use first found person
        $foundPerson = $found[0];

        if ($foundPerson->getFullName() !== $person->getFullName()) {
            return $person;
        }

        return $foundPerson;
    }

    public function update(Person $person, bool $forced): Person
    {
        if (!$forced) {
            // will still get Database person if PHONE changes
            $existingPerson = $this->exists($person);
            // Make sure we have the most up-to-date phone, the other field were just checked
            if (null !== $person->getPhone() && $existingPerson->getPhone() !== $person->getPhone()) {
                // This is a person from the database
                $existingPerson->setPhone($person->getPhone());
            }

            // if we didn't change phone number we still have the Initial Person submitted
            return $existingPerson;
        }
        // Leader has determined this 100% is a different person
        $newPerson = new Person();

        $newPerson
            ->setFirstName($person->getFirstName())
            ->setLastName($person->getLastName())
            ->setEmail($person->getEmail())
            ->setPhone($person->getPhone())
        ;

        return $newPerson;
    }
}
