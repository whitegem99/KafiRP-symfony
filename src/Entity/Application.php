<?php

namespace App\Entity;

use App\Enum\DecisionType;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
 */
class Application
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
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $decisionType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var ArrayCollection
     */
    private $decisions;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="application")
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="application")
     */
    private $notes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = false;
        $this->decisionType = DecisionType::NOT_DECIDED;
        $this->isDeleted = false;
        $this->decisions = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReviewDate(): ?\DateTimeInterface
    {
        return $this->reviewDate;
    }

    public function setReviewDate(?\DateTimeInterface $reviewDate): self
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    public function getDecisionType(): ?int
    {
        return $this->decisionType;
    }

    public function setDecisionType(int $decisionType): self
    {
        $this->decisionType = $decisionType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getDecisions(): ?ArrayCollection
    {
        return $this->decisions;
    }

    /**
     * @param Decision $decision
     * @return $this
     */
    public function addDecision(Decision $decision): Application
    {
        if (!$this->decisions->contains($decision)) {
            $this->decisions->add($decision);
        }
        return $this;
    }

    /**
     * @param Decision $decision
     * @return $this
     */
    public function removeDecision(Decision $decision): Application
    {
        if ($this->decisions->contains($decision)) {
            $this->decisions->removeElement($decision);
        }
        return $this;
    }

    /**
     * @param ArrayCollection $decisions
     * @return Application
     */
    public function setDecisions(ArrayCollection $decisions): Application
    {
        $this->decisions = $decisions;
        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setApplication($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getApplication() === $this) {
                $answer->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setApplication($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getApplication() === $this) {
                $note->setApplication(null);
            }
        }

        return $this;
    }
}
