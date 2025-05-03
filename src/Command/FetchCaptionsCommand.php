<?php

namespace App\Command;

use App\Repository\VideoRepository;
use App\Service\GoogleClient;
use Google\Service\YouTube;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fetch-captions')]
class FetchCaptionsCommand extends Command
{
    protected YouTube $youtube;

    public function __construct(
        protected GoogleClient    $googleClient,
        protected VideoRepository $videoRepository,
    ) {
        parent::__construct();
        $this->youtube = $this->googleClient->getYoutube();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = $this->videoRepository->getListWithoutCaptions();
        foreach ($list as $item) {
            $output->write('Captions saving for ' . $item['id'] . '... ');
            $bytes = $this->saveTranscript($item['id']);
            $output->write($bytes . ' bytes written.');

            $this->videoRepository->setCaptions($item['id'], true);
            $output->writeln('...saved');
        }

        return Command::SUCCESS;
    }

    protected function saveTranscript(string $videoId): int|false
    {
        $captionsPath = __DIR__ . '/../../storage/captions';
        \file_exists($captionsPath) || \mkdir($captionsPath, 0777, true);

        $captionFile = $captionsPath . '/' . $videoId . '.srt';
        if (\file_exists($captionFile)) {
            return 0;
        }

        $captions = $this->youtube->captions->listCaptions('id,snippet', $videoId);
        $caption = \current($captions->getItems());
        $content = $this->youtube->captions->download($caption['id']);

        $bodyStream = $content->getBody();
        $bodyStream->rewind();
        return \file_put_contents($captionFile, $bodyStream->getContents());
    }
}
