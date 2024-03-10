<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ApplicationCompletedEvent;
use App\Event\ApplicationDecisionEvent;
use App\Helper\DecisionTypeHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApplicationSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $discordWebhook;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var DecisionTypeHelper
     */
    private $decisionTypeHelper;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        DecisionTypeHelper $decisionTypeHelper,
        UrlGeneratorInterface $urlGenerator,
        string $discordWebhook
    )
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->decisionTypeHelper = $decisionTypeHelper;
        $this->urlGenerator = $urlGenerator;
        $this->discordWebhook = $discordWebhook;
    }

    public static function getSubscribedEvents()
    {
        return [
            ApplicationCompletedEvent::class => ['onApplicationCompleted'],
            ApplicationDecisionEvent::class => ['onApplicationDecisionUpdated'],
        ];
    }

    public function onApplicationCompleted(ApplicationCompletedEvent $event): void
    {
//        $application = $event->getApplication();
//        $user = $event->getUser();
//
//        $this->logger->info('Application created', ['application' => $application]);
//
//        if (null !== $application) {
//
//            $fields = [];
//
//            $fields[] = [
//                'name' => 'Başvuru Sahibi',
//                'value' => $user->getUsername() . ' (' . $user->getEmail() . ')',
//            ];
//
//            $fields[] = [
//                'name' => 'Discord ID',
//                'value' => $user->getDiscordId(),
//            ];
//
//            $fields[] = [
//                'name' => 'Discord Kullanıcı Adı',
//                'value' => $user->getDiscordUsername(),
//            ];
//
//            $options = [
//                'headers' => [
//                    'Content-Type' => 'application/json',
//                ],
//                'json' => [
//                    'content' => null,
//                    'embeds' => [
//                        [
//                            'title' => 'Yeni Başvuru',
//                            'color' => 13369344,
//                            'fields' => $fields
//                        ]
//                    ],
//                    'username' => 'KafiRP Başvuru'
//                ]
//            ];
//            $this->httpClient->request('POST', $this->discordWebhook, $options);
//        }
    }

    public function onApplicationDecisionUpdated(ApplicationDecisionEvent $event): void
    {
        $application = $event->getApplication();
        $systemUser = $event->getSystemUser();

        $this->logger->info('Application decision is updated', ['application' => $application, 'event' => $event]);

        if (null !== $application) {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => '#' . $application->getId() . ' numaralı başvuru üzerinde karar değişikliği yapıldı.',
                    'embeds' => [
                        [
                            'title' => 'Başvuru Durumu',
                            'url' => $this->urlGenerator->generate('application_show', ['applicationId' => $application->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                            'color' => 13369344,
                            'fields' => [
                                [
                                    'name' => 'Eski Karar',
                                    'value' => $this->decisionTypeHelper->map($event->getOldDecisionType()),
                                ],
                                [
                                    'name' => 'Yeni Karar',
                                    'value' => $this->decisionTypeHelper->map($application->getDecisionType()),
                                ],
                                [
                                    'name' => 'Yönetici',
                                    'value' => $systemUser->getUsername(),
                                ]
                            ]
                        ]
                    ],
                    'username' => 'KafiRP Başvuru Durum'
                ]
            ];
            $this->httpClient->request('POST', $this->discordWebhook, $options);
        }
    }
}