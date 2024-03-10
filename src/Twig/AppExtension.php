<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Question;
use App\Enum\AnswerType;
use App\Enum\DecisionType;
use App\Enum\RoleType;
use App\Helper\AccessHelper;
use App\Helper\DecisionTypeHelper;
use App\Helper\QuestionHelper;
use App\Helper\RoleTypeHelper;
use App\Repository\ApplicationRepository;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var DecisionTypeHelper
     */
    private $decisionTypeHelper;
    /**
     * @var RoleTypeHelper
     */
    private $roleTypeHelper;
    /**
     * @var AccessHelper
     */
    private $accessHelper;
    /**
     * @var QuestionHelper
     */
    private $questionHelper;
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;
    /**
     * @var TwigRendererEngine
     */
    private $environment;

    public function __construct(
        DecisionTypeHelper $decisionTypeHelper,
        RoleTypeHelper $roleTypeHelper,
        AccessHelper $accessHelper,
        QuestionHelper $questionHelper,
        ApplicationRepository $applicationRepository,
        Environment $environment
    )
    {
        $this->decisionTypeHelper = $decisionTypeHelper;
        $this->roleTypeHelper = $roleTypeHelper;
        $this->accessHelper = $accessHelper;
        $this->questionHelper = $questionHelper;
        $this->applicationRepository = $applicationRepository;
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('mapDecision', [$this, 'mapDecision']),
            new TwigFunction('getDecisionTypeName', [$this, 'getDecisionTypeName']),
            new TwigFunction('mapRole', [$this, 'mapRole']),
            new TwigFunction('mapAccess', [$this, 'mapAccess']),
            new TwigFunction('mapAccess', [$this, 'mapAccess']),
            new TwigFunction('getQuestionText', [$this, 'getQuestionText']),
            new TwigFunction('getAnswerText', [$this, 'getAnswerText']),
            new TwigFunction('getApplicationDetailsHtml', [$this, 'getApplicationDetailsHtml']),
        ];
    }

    public function mapDecision(int $decisionType)
    {
        return $this->decisionTypeHelper->map($decisionType);
    }

    public function getDecisionTypeName(int $decisionTypeId)
    {
        return DecisionType::GetName($decisionTypeId);
    }

    public function mapRole(int $roleTypeId)
    {
        return $this->roleTypeHelper->map($roleTypeId);
    }

    public function mapAccess(array $accessList)
    {
        return $this->accessHelper->map($accessList);
    }

    public function getQuestionText(int $questionId, int $type): ?string
    {
        $question = $this->questionHelper->getQuestion($questionId);

        if (null === $question) {
            return null;
        }

        $questionText = null;

        if ($type === AnswerType::TEXT) {
            $questionText = $question->getTextQuestion()->getText();
        } else if ($type === AnswerType::CHOICE) {
            $questionText = $question->getChoiceQuestion()->getText();
        }
        return $questionText;
    }

    public function getAnswerText(int $questionId, string $answer, int $type): ?string
    {
        $question = $this->questionHelper->getQuestion($questionId);

        if (null === $question) {
            return null;
        }

        $answerText = null;

        if ($type === AnswerType::TEXT) {
            $answerText = $answer;
        } else if ($type === AnswerType::CHOICE) {
            foreach ($question->getChoiceQuestion()->getOptions() as $option) {
                if ((int) $answer === $option->getId()) {
                    $answerText = $option->getText();
                }
            }
        }
        return $answerText;
    }

    public function getApplicationDetailsHtml(int $applicationId): ?string
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null === $application) {
            return null;
        }

        return $this->environment->render('admin/application/_partial/widget.application.detail.html.twig', ['application' => $application]);
    }
}