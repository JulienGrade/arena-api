<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class TournamentCoverUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/tournaments')]
        private string $directory,
        private EntityManagerInterface $em,
    ) {
    }

    public function upload(
        Tournament $tournament,
        UploadedFile $file,
    ): string {
        if (
            !is_dir($this->directory)
            && !mkdir($this->directory, 0775, true)
            && !is_dir($this->directory)
        ) {
            throw new \RuntimeException(
                'Dossier des images inaccessible.',
            );
        }

        $extension = $file->guessExtension() ?? 'bin';
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $previous = $tournament->getCoverPath();

        $file->move($this->directory, $name);

        $path = '/uploads/tournaments/' . $name;

        try {
            $tournament->setCoverPath($path);
            $this->em->flush();
        } catch (\Throwable $error) {
            @unlink($this->directory . '/' . $name);

            throw $error;
        }

        $this->delete($previous);

        return $path;
    }

    public function delete(?string $path): void
    {
        $prefix = '/uploads/tournaments/';

        if ($path === null || !str_starts_with($path, $prefix)) {
            return;
        }

        $name = substr($path, strlen($prefix));

        if ($name === '' || basename($name) !== $name) {
            return;
        }

        $file = $this->directory . '/' . $name;

        if (is_file($file)) {
            unlink($file);
        }
    }
}
