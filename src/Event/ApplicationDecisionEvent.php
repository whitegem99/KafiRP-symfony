<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Application;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ApplicationDecisionEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var int
     */
    private $oldDecisionType;

    /**
     * @var User
     */
    private $systemUser;

    public function __construct(
        Application $application,
        int $oldDecisionType,
        User $systemUser
    )
    {
        $this->application = $application;
        $this->oldDecisionType = $oldDecisionType;
        $this->systemUser = $systemUser;
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
     * @return int
     */
    public function getOldDecisionType(): int
    {
        return $this->oldDecisionType;
    }

    /**
     * @param int $oldDecisionType
     */
    public function setOldDecisionType(int $oldDecisionType): void
    {
        $this->oldDecisionType = $oldDecisionType;
    }

    /**
     * @return User
     */
    public function getSystemUser(): User
    {
        return $this->systemUser;
    }

    /**
     * @param User $systemUser
     */
    public function setSystemUser(User $systemUser): void
    {
        $this->systemUser = $systemUser;
    }

}