<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserCreatedFromDiscordOAuthEvent;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserCreatedFromDiscordOAuthSubscriber implements EventSubscriberInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var string
     */
    private $discordWebhook;

    public function __construct(
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        UserRepository $userRepository,
        string $discordWebhook
    )
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->userRepository = $userRepository;
        $this->discordWebhook = $discordWebhook;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedFromDiscordOAuthEvent::class => 'onUserCreatedFromDiscordOAuth'
        ];
    }

    public function onUserCreatedFromDiscordOAuth(UserCreatedFromDiscordOAuthEvent $event): void
    {
        $this->logger->info('User created from Discord OAuth', ['email' => $event->getEmail()]);

        $user = $this->userRepository->findOneBy(['email' => $event->getEmail()]);
        if (null !== $user) {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => null,
                    'embeds' => [
                        [
                            'title' => 'Yeni Kullanıcı Kaydı',
                            'description' => $event->getEmail() . ' e-postası ile yeni bir kullanıcı kaydı oluşturuldu.',
                            'color' => 5814783,
                        ]
                    ],
                    'username' => 'KafiRP Kayıt'
                ]
            ];
            $this->httpClient->request('POST', $this->discordWebhook, $options);
        }

    }
}