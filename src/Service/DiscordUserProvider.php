<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Event\NonSubscriberUserDetectEvent;
use App\Event\UserCreatedFromDiscordOAuthEvent;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordUserProvider implements UserProviderInterface
{
    private const DISCORD_ACCESS_TOKEN_ENDPOINT = 'https://discord.com/api/oauth2/token';
    private const DISCORD_USER_DATA_ENDPOINT = 'https://discordapp.com/api/users/@me';
    private const DISCORD_USER_GUILDS_ENDPOINT = 'https://discordapp.com/api/users/@me/guilds';

    /**
     * @var string
     */
    private $discordClientId;
    /**
     * @var string
     */
    private $discordClientSecret;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var string
     */
    private $discordBotToken;
    /**
     * @var string
     */
    private $discordServerId;
    /**
     * @var string
     */
    private $discordRoleId;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var string
     */
    private $discordWhitelistRoleId;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpClientInterface $httpClient,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        SettingsRepository $settingsRepository,
        string $discordClientId,
        string $discordClientSecret,
        string $discordBotToken,
        string $discordServerId,
        string $discordRoleId,
        string $discordWhitelistRoleId
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->settingsRepository = $settingsRepository;
        $this->discordClientId = $discordClientId;
        $this->discordClientSecret = $discordClientSecret;
        $this->discordBotToken = $discordBotToken;
        $this->discordServerId = $discordServerId;
        $this->discordRoleId = $discordRoleId;
        $this->discordWhitelistRoleId = $discordWhitelistRoleId;
    }

    public function loadUserByDiscordOAuth(string $code): ?UserInterface
    {
        $accessToken = $this->getAccessToken($code);

        $discordUserData = $this->getUserInformations($accessToken);
        [$isAccess, $inWhiteList] = $this->checkAccess($accessToken);

        [
            'email'             => $email,
            'id'                => $discordId,
            'username'          => $discordUsername,
            'discord_avatar'    => $discordAvatar
        ] = $discordUserData;

        if (false === $isAccess) {
            $this->eventDispatcher->dispatch(
                new NonSubscriberUserDetectEvent($discordId, $discordUsername)
            );

            return null;
        }

        $user = $this->userRepository->getUserFromDiscordOAuth($discordId, $discordUsername, $email, $discordAvatar, $accessToken, $inWhiteList);

        if (null === $user) {
            $randomPassword = $this->randomPassword();
            $user = $this->userRepository->createUserFromDiscordOAuth($discordId, $discordUsername, $email, $discordAvatar, $randomPassword, $accessToken, $inWhiteList);

            $this->eventDispatcher->dispatch(
                new UserCreatedFromDiscordOAuthEvent($email, $randomPassword)
            );
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User || !$user->getDiscordId()) {
            throw new UnsupportedUserException();
        }

        /** @var string $discordId */
        $discordId = $user->getDiscordId();

        return $this->loadUserByUsername($discordId);
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }

    private function getAccessToken(string $code): string
    {
        $redirectUrl = $this->urlGenerator->generate(
            'app_login',
            ['discord-oauth-provider' => 1],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $options = [
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'client_id'     => $this->discordClientId,
                'client_secret' => $this->discordClientSecret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $redirectUrl,
                'scope'         => 'identify email guilds guilds.join',
            ]
        ];

        /**
         *
         *
         *
         *
         */

        $response = $this->httpClient->request('POST', self::DISCORD_ACCESS_TOKEN_ENDPOINT, $options);

        $data = $response->toArray();

        if (!$data['access_token']) {
            throw new ServiceUnavailableHttpException(null, 'Authentication Failed [DISCORD]');
        }

        return $data['access_token'];
    }

    public function getUserInformations(string $accessToken): array
    {
        $options = [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ];

        $response = $this->httpClient->request('GET', self::DISCORD_USER_DATA_ENDPOINT, $options);

        $data = $response->toArray();

        if (
            !$data['id']
            || !$data['email']
            || !$data['username']
        ) {
            throw new ServiceUnavailableHttpException(null, 'Authentication Failed [DISCORD USER DATA]');
        }

        if (
            !$data['verified']
        ) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Not verified. [DISCORD]');
        }

        $discordAvatarUrl = 'https://cdn.discordapp.com/avatars/{user_id}/{user_avatar}.png';
        $data['discord_avatar'] = str_replace(
            ['{user_id}', '{user_avatar}'],
            [$data['id'], $data['avatar']],
            $discordAvatarUrl
        );

        return $data;
    }

    public function checkAccess(string $accessToken): array
    {
        $member = $this->getUserInformations($accessToken);

        $options = [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ];

        $response = $this->httpClient->request('GET', self::DISCORD_USER_GUILDS_ENDPOINT, $options);

        $data = $response->toArray();

        $endpoint = 'https://discordapp.com/api/guilds/' . $this->discordServerId . '/members/' . $member['id'];

        $headers = [
            'Accept'        => 'application/json',
            'Authorization' => 'Bot ' . $this->discordBotToken,
        ];

        if (false === in_array($this->discordServerId, array_column($data, 'id'), true)) {
            $options = [
                'headers' => $headers,
                'json' => [
                    'access_token' => $accessToken,
                    'nick' => $member['username'],
                    'roles' => [],
                    'mute' => false,
                    'deaf' => false
                ]
            ];
            $responseAddToGuild = $this->httpClient->request('PUT', $endpoint, $options);
            return [false, false];
        }

        $options = [
            'headers' => $headers
        ];
        $responseGuildRoles = $this->httpClient->request('GET', $endpoint, $options);
        $dataMemberRoles = $responseGuildRoles->toArray();

        $isAccess = false;
        $inWhiteList = true === in_array($this->discordWhitelistRoleId, $dataMemberRoles['roles'], true);
        $settings = $this->settingsRepository->find(1);

        if (
            null !== $settings
            && array_key_exists('roles', $dataMemberRoles)
            && is_array($dataMemberRoles['roles'])
            && is_array($settings->getAllowDiscordRoles())
            && count($settings->getAllowDiscordRoles()) > 0
        ) {
            foreach ($dataMemberRoles['roles'] as $memberRole) {
                if (in_array($memberRole, $settings->getAllowDiscordRoles(), true)) {
                    $isAccess = true;
                    break;
                }
            }
        } else {
            $isAccess = true;
        }

        return [$isAccess, $inWhiteList];
    }

    /**
     * @param string $discordId
     * @return User
     */
    public function loadUserByUsername($discordId): User
    {
        $user = $this->userRepository->findOneBy([
            'discordId' => $discordId
        ]);

        if (null === $user) {
            throw new UsernameNotFoundException('Not found Discord ID');
        }

        return $user;
    }

    private function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 12; $i++) {
            $n = random_int(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}