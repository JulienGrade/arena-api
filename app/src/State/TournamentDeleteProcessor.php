<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Tournament;
use App\Service\TournamentCoverUploader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class TournamentDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private TournamentCoverUploader $uploader,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): mixed {
        if (!$data instanceof Tournament) {
            throw new \LogicException('Tournoi attendu.');
        }

        $coverPath = $data->getCoverPath();

        $result = $this->removeProcessor->process(
            $data,
            $operation,
            $uriVariables,
            $context,
        );

        $this->uploader->delete($coverPath);

        return $result;
    }
}
