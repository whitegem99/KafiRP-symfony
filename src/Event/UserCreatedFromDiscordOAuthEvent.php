<?php declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UserCreatedFromDiscordOAuthEvent extends Event
{

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $randomPassword;

    public function __construct(string $email, string $randomPassword)
    {
        $this->email = $email;
        $this->randomPassword = $randomPassword;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getRandomPassword(): string
    {
        return $this->randomPassword;
    }

    /**
     * @param string $randomPassword
     */
    public function setRandomPassword(string $randomPassword): void
    {
        $this->randomPassword = $randomPassword;
    }

}