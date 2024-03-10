<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Application;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ApplicationCompletedEvent extends Event
{
    private $application;
    private $user;

    public function __construct(Application $application, User $user)
    {
        $this->application = $application;
        $this->user = $user;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

}