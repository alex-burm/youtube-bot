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

#[AsCommand(name: 'search-captions')]
class SearchCaptionsCommand extends Command
{
    public function __construct(
        protected GptClient $gptClient,
        protected PineconeClient $pineconeClient,
        protected VideoRepository $videoRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('query', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $normalizedQuery = $this->gptClient->normalize($input->getArgument('query'));

        $embedding = $this->gptClient->embed($normalizedQuery);
        $response = $this->pineconeClient->query($embedding);

        dump($response['matches']);
        return Command::SUCCESS;
    }
}
