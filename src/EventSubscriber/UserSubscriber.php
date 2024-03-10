<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\NonSubscriberUserDetectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var string
     */
    private $discordWebhook;

    public function __construct(HttpClientInterface $httpClient, string $discordWebhook)
    {
        $this->httpClient = $httpClient;
        $this->discordWebhook = $discordWebhook;
    }

    public static function getSubscribedEvents()
    {
        return [
            NonSubscriberUserDetectEvent::class => 'onNonSubscriberUserDetect'
        ];
    }

    public function onNonSubscriberUserDetect(NonSubscriberUserDetectEvent $event)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'content' => null,
                'embeds' => [
                    [
                        'title' => 'Abone Olmayan Kullanıcı Tespit Edildi',
                        'description' => $event->getDiscordUsername() . ' (#' . $event->getDiscordId() . ')',
                        'color' => 5814783,
                    ]
                ],
                'username' => 'KafiRP Kayıt'
            ]
        ];
        $this->httpClient->request('POST', $this->discordWebhook, $options);
    }
}