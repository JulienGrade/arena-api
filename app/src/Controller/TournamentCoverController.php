<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Tournament;
use App\Service\TournamentCoverUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

final class TournamentCoverController extends AbstractController
{
    #[Route(
        '/api/tournaments/{id}/cover',
        name: 'tournament_cover_upload',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_ORGANIZER')]
    public function __invoke(
        Tournament $tournament,
        #[MapUploadedFile(
            constraints: new Assert\Image(
                maxSize: '3M',
                mimeTypes: [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],

            ),
            name: 'file',
        )]
        UploadedFile $file,
        TournamentCoverUploader $uploader,
    ): JsonResponse {
        return $this->json([
            'coverPath' => $uploader->upload($tournament, $file),
        ]);
    }
}

