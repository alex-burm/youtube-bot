<?php

namespace App\Command;

use App\Repository\VideoRepository;
use App\Service\GoogleClient;
use Google\Service\YouTube;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fetch-list')]
class FetchListCommand extends Command
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
        $playlistId = '';
        $page = null;
        do {
            $params = [
                'playlistId' => $playlistId,
                'maxResults' => 50,
            ];

            if ($page) {
                $params['pageToken'] = $page;
            }

            $response = $this->youtube->playlistItems->listPlaylistItems('snippet', $params);
            foreach ($response->getItems() as $playlistItem) {
                $snippet = $playlistItem->getSnippet();
                $videoId = $snippet->getResourceId()->getVideoId();
                $title = $snippet->getTitle();
                $thumbnail = $snippet->getThumbnails()->getMedium()->url;

                $output->write(\sprintf('Video (%s): %s', $videoId, $title));

                if ($this->videoRepository->find($videoId)) {
                    $output->writeln('...already exists');
                    continue;
                }

                $this->videoRepository->add(
                    $videoId,
                    $playlistId,
                    $title,
                    $thumbnail
                );
                $output->writeln('...saved');
            }

            $page = $response->getNextPageToken();
        } while ($page);

        return Command::SUCCESS;
    }
}
