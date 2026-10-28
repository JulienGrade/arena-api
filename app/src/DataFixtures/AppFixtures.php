<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $accounts = [
            [
                'organisateur@arena.test',
                'Organisateur123!',
                ['ROLE_ORGANIZER'],
            ],
            [
                'membre@arena.test',
                'Membre123!',
                ['ROLE_USER'],
            ],
            [
                'membre2@arena.test',
                'MembreDeux123!',
                ['ROLE_USER'],
            ],
        ];

        foreach ($accounts as [$email, $password, $roles]) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPassword(
                $this->hasher->hashPassword($user, $password),
            );

            $manager->persist($user);
        }

        $tournaments = [
            ['Coupe de rentrée', 'Rocket League', 16],
            ['Finale solo', 'Street Fighter 6', 1],
        ];

        foreach ($tournaments as [$title, $game, $seats]) {
            $tournament = new Tournament();
            $tournament->setTitle($title);
            $tournament->setGame($game);
            $tournament->setSeats($seats);

            $manager->persist($tournament);
        }

        $manager->flush();
    }
}
