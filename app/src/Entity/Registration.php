<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\RegistrationRepository;
use App\State\RegistrationProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_participant_tournament',
    columns: ['participant_id', 'tournament_id'],
)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ORGANIZER')",
        ),
        new Get(
            security: "is_granted('ROLE_ORGANIZER') or object.getParticipant() == user",
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: RegistrationProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['registration:read']],
    denormalizationContext: ['groups' => ['registration:write']],
)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['registration:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['registration:read', 'registration:write'])]
    private ?Tournament $tournament = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $participant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    #[Groups(['registration:read'])]
    public function getParticipantEmail(): ?string
    {
        return $this->participant?->getUserIdentifier();
    }
}
