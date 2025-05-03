<?php

namespace App\Command;

use App\Repository\VideoRepository;
use App\Service\CohereClient;
use App\Service\PineconeClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'search-captions')]
class SearchCaptionsCommand extends Command
{
    public function __construct(
        protected CohereClient   $cohereClient,
        protected PineconeClient $pineconeClient,
        protected VideoRepository $videoRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = "как тестировать exception";
        $embedding = $this->cohereClient->embed($query);
        $response = $this->pineconeClient->query($embedding, 10);

        dd($response);
        return Command::SUCCESS;
    }
}
