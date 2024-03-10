<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
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
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity=TextQuestion::class)
     */
    private $textQuestion;

    /**
     * @ORM\ManyToOne(targetEntity=ChoiceQuestion::class)
     */
    private $choiceQuestion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $random;

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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getTextQuestion(): ?TextQuestion
    {
        return $this->textQuestion;
    }

    public function setTextQuestion(?TextQuestion $textQuestion): self
    {
        $this->textQuestion = $textQuestion;

        return $this;
    }

    public function getChoiceQuestion(): ?ChoiceQuestion
    {
        return $this->choiceQuestion;
    }

    public function setChoiceQuestion(?ChoiceQuestion $choiceQuestion): self
    {
        $this->choiceQuestion = $choiceQuestion;

        return $this;
    }

    public function getRandom(): ?bool
    {
        return $this->random;
    }

    public function setRandom(bool $random): self
    {
        $this->random = $random;

        return $this;
    }
}
