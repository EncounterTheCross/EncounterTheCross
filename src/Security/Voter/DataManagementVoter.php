<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\Location;
use App\Entity\Testimonial;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DataManagementVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_EVENT_LEADER') || $this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                if ($subject instanceof UserInterface) {
                    return $this->leaderPermissions($user, $subject);
                }

                if ($subject instanceof Testimonial) {
                    return $this->adminPermissions();
                }

                if ($subject instanceof Location && Location::TYPE_LAUNCH_POINT === $subject->getType()) {
                    return $this->adminPermissions(); // TODO: Leader of the launch point should be able to edit.
                }

                if ($subject instanceof Location && Location::TYPE_EVENT === $subject->getType()) {
                    return $this->adminPermissions();
                }

                if ($subject instanceof Event) {
                    return $this->adminPermissions() || $this->security->isGranted('ROLE_DATA_EDITOR_OVERWRITE');
                }

                if ($subject instanceof EventParticipant) {
                    return $this->adminPermissions() || $this->security->isGranted('ROLE_DATA_EDITOR_OVERWRITE');
                }

                break;
            case self::VIEW:
                return true;
        }

        return false;
    }

    private function adminPermissions(): bool
    {
        return $this->security->isGranted('ROLE_SUPER_ADMIN') || $this->security->isGranted('ROLE_EVENT_LEADER');
    }

    private function leaderPermissions($user, $subject): bool
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $user === $subject;
    }
}
