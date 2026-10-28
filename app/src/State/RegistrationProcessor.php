<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class RegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Registration {
        if (!$data instanceof Registration) {
            throw new \LogicException('Inscription attendue.');
        }

        $user = $this->security->getUser();
        $tournament = $data->getTournament();

        if (!$user instanceof User || $tournament === null) {
            throw new BadRequestHttpException(
                'Compte ou tournoi manquant.',
            );
        }

        return $this->em->wrapInTransaction(
            function (EntityManagerInterface $em) use (
                $data,
                $user,
                $tournament,
            ): Registration {
                $em->lock($tournament, LockMode::PESSIMISTIC_WRITE);

                $registrations = $em->getRepository(
                    Registration::class,
                );

                if ($registrations->findOneBy([
                        'participant' => $user,
                        'tournament' => $tournament,
                    ]) !== null) {
                    throw new ConflictHttpException(
                        'Déjà inscrit à ce tournoi.',
                    );
                }

                if (
                    $registrations->count([
                        'tournament' => $tournament,
                    ]) >= $tournament->getSeats()
                ) {
                    throw new ConflictHttpException(
                        'Tournoi complet.',
                    );
                }

                $data->setParticipant($user);
                $em->persist($data);

                return $data;
            },
        );
    }
}
