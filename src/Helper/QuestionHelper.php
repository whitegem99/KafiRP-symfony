<?php declare(strict_types=1);

namespace App\Helper;

use App\Entity\Question;
use App\Repository\QuestionRepository;

class QuestionHelper
{
    /**
     * @var QuestionRepository
     */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function getQuestion(int $questionId): ?Question
    {
        return $this->questionRepository->find($questionId);
    }
}