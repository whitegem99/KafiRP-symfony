<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingsRepository::class)
 */
class Settings
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $openToApplication;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $allowDiscordRoles = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $archiveHistory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenToApplication(): ?bool
    {
        return $this->openToApplication;
    }

    public function setOpenToApplication(bool $openToApplication): self
    {
        $this->openToApplication = $openToApplication;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getAllowDiscordRoles(): array
    {
        $roles = $this->allowDiscordRoles;
        return array_unique($roles);
    }

    public function setAllowDiscordRoles(array $allowDiscordRoles): self
    {
        $this->allowDiscordRoles = $allowDiscordRoles;
        return $this;
    }

    public function getArchiveHistory(): ?\DateTimeInterface
    {
        return $this->archiveHistory;
    }

    public function setArchiveHistory(?\DateTimeInterface $archiveHistory): self
    {
        $this->archiveHistory = $archiveHistory;

        return $this;
    }
}
