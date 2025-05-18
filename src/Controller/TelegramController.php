<?php

namespace App\Controller;

use App\Repository\QueryRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Service\CohereClient;
use App\Service\GptClient;
use App\Service\PineconeClient;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use BotMan\Drivers\Telegram\TelegramDriver;

class TelegramController
{
    public function __construct(
        protected ContainerInterface $container,
        protected CohereClient $cohereClient,
        protected GptClient $gptClient,
        protected PineconeClient $pineconeClient,
        protected VideoRepository $videoRepository,
        protected UserRepository $userRepository,
        protected QueryRepository $queryRepository,
    ) {
    }

    public function hook(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $this->container->get('settings')['botman'];

        DriverManager::loadDriver(TelegramDriver::class);
        $botman = BotManFactory::create($config);

        $botman->hears('/start', function (BotMan $bot) {

            $image = new Image('https://burm.me/img/profile.jpg');
            $message = OutgoingMessage::create($this->render('welcome'));
            $message->withAttachment($image);

            $bot->reply($message, ['parse_mode' => 'HTML']);

            $user = $this->userRepository->find($bot->getUser()->getId());
            if (false === $user) {
                $this->userRepository->add(
                    id: $bot->getUser()->getId(),
                    firstName: $bot->getUser()->getFirstName() ?? '',
                    lastName: $bot->getUser()->getLastName() ?? '',
                    username: $bot->getUser()->getUsername(),
                );
            }
        });

        $botman->hears('(.*)', function (BotMan $bot, $payload) {
            if ('/start' === $payload) {
                return;
            }

            if (\strlen($payload) > 300) {
                $bot->reply($this->render('error-max-length'), ['parse_mode' => 'HTML']);
                return;
            }

            if (\strlen($payload) < 5) {
                $bot->reply($this->render('error-min-length'), ['parse_mode' => 'HTML']);
                return;
            }

            $this->queryRepository->add($bot->getUser()->getId(), $payload);

            $bot->reply($this->render('search', [
                'query' => $payload,
            ]), ['parse_mode' => 'HTML']);

            $bot->types();

            try {
                $embedding = $this->gptClient->embed($payload);
                $response = $this->pineconeClient->query($embedding);

                $videoIds = \array_unique(\array_map(static fn($match) => $match['metadata']['videoId'], $response['matches']));
                foreach ($videoIds as $videoId) {
                    $video = $this->videoRepository->find($videoId);

                    $image = new Image($video['thumbnail']);
                    $message = OutgoingMessage::create(
                        $this->render('video', [
                            'video' => $video,
                        ])
                    );
                    $message->withAttachment($image);
                    $bot->reply($message, ['parse_mode' => 'HTML']);
                }
            } catch (\Throwable $exception) {
                $bot->reply($this->render('exception'), ['parse_mode' => 'HTML']);
            }
        });

        $botman->listen();
        return $response;
    }

    protected function render(string $name, array $data = []): string
    {
        \extract($data);
        \ob_start();
        require \dirname(__DIR__) . '/Template/telegram/' . $name . '.html.php';
        return \ob_get_clean();
    }
}
