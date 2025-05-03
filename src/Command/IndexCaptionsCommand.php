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

#[AsCommand(name: 'index-captions')]
class IndexCaptionsCommand extends Command
{
    public function __construct(
        protected CohereClient   $cohereClient,
        protected PineconeClient $pineconeClient,
        protected VideoRepository $videoRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('clear', null, InputOption::VALUE_OPTIONAL, 'Clear all indexed captions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('clear')) {
            $this->pineconeClient->clear();
            $output->writeln('All indexed captions cleared.');
        }

        $list = $this->videoRepository->getListWithoutIndex();
        foreach ($list as $item) {
            $file = __DIR__ . '/../../storage/captions/' . $item['id'] . '.srt';
            if (false === \file_exists($file)) {
                $output->writeln('File not found: ' . $file);
                continue;
            }
            $output->writeln('Processing file: ' . \basename($file));

            $captions = $this->parseCrtFileWithTime($file);
            $groups = $this->groupCaptionsWithOverlap($captions);

            $this->index($item['title'], $item['id']);

            $groupsCount = \count($groups);
            $output->write('Number of groups: ' . $groupsCount . ' ');
            foreach ($groups as $i => $group) {
                $text = \implode(' ', \array_column($group, 'text'));

                $this->index($text, $item['id']);
                $output->write(($i + 1) . ', ');

            }
            $output->writeln('');
            $this->videoRepository->setIndexed($item['id'], true);
        }

        $output->writeln('All files processed.');
        return Command::SUCCESS;
    }

    protected function index($text, $videoId)
    {
        $embedding = $this->cohereClient->embed($text);
        $this->pineconeClient->upsert([
            [
                'id' => \uniqid('group_'),
                'values' => $embedding,
                'metadata' => [
                    'text' => $text,
                    'videoId' => $videoId,
                ],
            ],
        ]);
        \sleep(\rand(1, 3));
    }
    protected function parseCrtFileWithTime(string $filePath): array
    {
        $lines = \file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $captions = [];
        $currentCaption = [];

        foreach ($lines as $line) {
            if (\preg_match('/(\d{1,2}:\d{1,2}:\d{1,2}\.\d{3}),(\d{1,2}:\d{1,2}:\d{1,2}\.\d{3})/', $line, $matches)) {
                $currentCaption['start'] = $this->timeToSeconds($matches[1]);
                $currentCaption['end'] = $this->timeToSeconds($matches[2]);
            } elseif (false === empty($line) && isset($currentCaption['start'])) {
                $currentCaption['text'] = $line;
                $captions[] = $currentCaption;
                $currentCaption = [];
            }
        }

        return $captions;
    }

    function timeToSeconds(string $time): int
    {
        list($h, $m, $s) = \explode(':', \str_replace(',', '.', $time));
        return (int)$h * 3600 + (int)$m * 60 + (int)\floor($s);
    }

    protected function groupCaptionsWithOverlap(array $captions, int $step = 60, int $overlap = 15): array
    {
        $groups = [];
        $start = 0;

        $maxTime = \end($captions)['end'] ?? 0;

        while ($start <= $maxTime) {
            $windowStart = \max(0, $start - $overlap);
            $windowEnd = $start + $step + $overlap;

            $currentGroup = \array_filter($captions, static function ($caption) use ($windowStart, $windowEnd) {
                return $caption['start'] < $windowEnd && $caption['end'] > $windowStart;
            });

            if (\count($currentGroup) > 0) {
//            if (\count($currentGroup) === 0) {
                $groups[] = $currentGroup;
            }

            $start += $step;
        }

        return $groups;
    }
}
