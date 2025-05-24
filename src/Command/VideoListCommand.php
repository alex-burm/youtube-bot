<?php

namespace App\Command;

use App\Repository\VideoRepository;
use App\Service\CohereClient;
use App\Service\GptClient;
use App\Service\PineconeClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'video-list')]
class VideoListCommand extends Command
{
    public function __construct(
        protected VideoRepository $videoRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = $this->videoRepository->getList();
        foreach ($list as $id => $title) {
            $output->writeln(\sprintf('%s: %s', $id, $title));
        }
        return Command::SUCCESS;
    }
}
