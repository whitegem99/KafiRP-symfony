<?php declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NonSubscriberUserDetectEvent extends Event
{
    /**
     * @var string
     */
    private $discordId;

    /**
     * @var string
     */
    private $discordUsername;

    public function __construct(string $discordId, string $discordUsername)
    {
        $this->discordId = $discordId;
        $this->discordUsername = $discordUsername;
    }

    /**
     * @return string
     */
    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    /**
     * @param string $discordId
     * @return NonSubscriberUserDetectEvent
     */
    public function setDiscordId(string $discordId): NonSubscriberUserDetectEvent
    {
        $this->discordId = $discordId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordUsername(): string
    {
        return $this->discordUsername;
    }

    /**
     * @param string $discordUsername
     * @return NonSubscriberUserDetectEvent
     */
    public function setDiscordUsername(string $discordUsername): NonSubscriberUserDetectEvent
    {
        $this->discordUsername = $discordUsername;
        return $this;
    }
}