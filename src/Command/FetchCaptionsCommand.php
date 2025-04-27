<?php

namespace App\Command;

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
        protected GoogleClient $client,
    ) {
        parent::__construct();
        $this->youtube = $this->client->getYoutube();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $params = [
            'channelId' => 'UC3y33E2JqXCAh125rKnmjzw',
            'maxResults' => 10,
            'type' => 'video',
        ];

        do {
            $response = $this->youtube->search->listSearch('snippet', $params);

            foreach ($response->getItems() as $video) {
                $videoId = $video->getId()->getVideoId();
                //$title = $video->getSnippet()->getTitle();

                $output->write('Captions saving for ' . $videoId . '... ');
                $bytes = $this->saveTranscript($videoId);
                $output->writeln($bytes . ' bytes written.');
            }

            $page = $response->getNextPageToken();
            if ($page) {
                $params['pageToken'] = $page;
            }
        } while ($page);

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
