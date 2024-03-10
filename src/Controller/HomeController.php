<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Application;
use App\Entity\User;
use App\Enum\AnswerType;
use App\Enum\RoleType;
use App\Event\ApplicationCompletedEvent;
use App\Repository\ApplicationRepository;
use App\Repository\QuestionRepository;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;
    /**
     * @var QuestionRepository
     */
    private $questionRepository;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        ApplicationRepository $applicationRepository,
        QuestionRepository $questionRepository,
        SettingsRepository $settingsRepository,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->applicationRepository = $applicationRepository;
        $this->questionRepository = $questionRepository;
        $this->settingsRepository = $settingsRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request): Response
    {

        $settings = $this->settingsRepository->find(1);
        if (null !== $settings && $settings->getOpenToApplication() === false) {
            return $this->render('home/index.html.twig', [
                'success' => false,
                'answerTypes' => [
                    'text' => AnswerType::TEXT,
                    'choice' => AnswerType::CHOICE,
                ],
                'errorMessage' => 'Başvuru alımı şu anda kapalıdır.',
            ]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $archiveDate = $settings->getArchiveHistory();
        if (null !== $archiveDate) {
            $existsApplication = $this->applicationRepository->findByAfterArchiveHistory($settings->getArchiveHistory()->format('Y-m-d H:i:s'), ['user' => $user]);
        } else {
            $existsApplication = $this->applicationRepository->findOneBy(['user' => $user]);
        }


        if (count($existsApplication)) {
            return $this->render('home/index.html.twig', [
                'success' => false,
                'answerTypes' => [
                    'text' => AnswerType::TEXT,
                    'choice' => AnswerType::CHOICE,
                ],
                'errorMessage' => 'Bu hesap ile zaten önceden bir başvuru yapılmış, tekrar başvuru yapamazsınız.',
            ]);
        }

        $questions = $this->questionRepository->findBy(['status' => true, 'random' => false], ['sort' => 'ASC']);
        $randomQuestions = $this->questionRepository->getRandom(1);

        $questionList = array_merge($questions, $randomQuestions);

        $form = $this->createFormBuilder();

        $form
            ->add('textQuestion', CollectionType::class, [
                'allow_extra_fields' => true
            ])
            ->add('choiceQuestion', CollectionType::class, [
                'allow_extra_fields' => true
            ]);

        $this->handleQuestions($form, $questionList, $request);

        $form->add('save', SubmitType::class, ['label' => 'Başvur', 'attr' => ['class' => 'btn btn-success']]);
        $f = $form->getForm();

        $f->handleRequest($request);
        if ($f->isSubmitted()) {

            if ($f->isValid()) {

                $data = $request->request->all();
                $textAnswersData = $data['form']['textQuestion'];
                $choiceAnswersData = $data['form']['choiceQuestion'];
                $error = false;

                if (
                    count($textAnswersData)
                    && count($choiceAnswersData)
                ) {
                    $application = new Application();
                    $application
                        ->setUser($user);

                    $this->entityManager->persist($application);

                    foreach ($textAnswersData as $questionId => $answerValue) {

                        $answerValue = trim($answerValue);
                        $this->logger->error('DDD', ['error' => $error, 'v' => $answerValue]);
                        if (empty($answerValue)) {
                            $error = true;
                            break;
                        }

                        $answer = new Answer();
                        $answer
                            ->setApplication($application)
                            ->setAnswer($answerValue)
                            ->setQuestionId((int) $questionId)
                            ->setAnswerType(AnswerType::TEXT);
                        $this->entityManager->persist($answer);
                    }

                    if ($error === false) {
                        foreach ($choiceAnswersData as $questionId => $answerValue) {
                            $answer = new Answer();
                            $answer
                                ->setApplication($application)
                                ->setAnswer($answerValue)
                                ->setQuestionId((int) $questionId)
                                ->setAnswerType(AnswerType::CHOICE);
                            $this->entityManager->persist($answer);
                        }

                        $this->entityManager->flush();
                    }
                }

                if (false === $error) {
                    $this->dispatcher->dispatch(new ApplicationCompletedEvent($application, $user));

                    return $this->render('home/index.html.twig', [
                        'success' => true,
                        'answerTypes' => [
                            'text' => AnswerType::TEXT,
                            'choice' => AnswerType::CHOICE,
                        ]
                    ]);
                }

            }
        }

        return $this->render('home/index.html.twig', [
            'form' => $f->createView(),
            'answerTypes' => [
                'text' => AnswerType::TEXT,
                'choice' => AnswerType::CHOICE,
            ]
        ]);
    }

    /**
     * @param FormBuilderInterface $form
     * @param array $questions
     */
    private function handleQuestions(FormBuilderInterface $form, array $questions, Request $request): void
    {
        foreach ($questions as $index => $question) {
            if (null !== $question->getChoiceQuestion()) {

                $options = [];
                $attr = [];

                $formData = $request->request->get('form') ?? null;
                $choiceQuestion = $formData['choiceQuestion'] ?? null;
                $data = $choiceQuestion !== null && array_key_exists($question->getId(), $choiceQuestion) ? trim($choiceQuestion[$question->getId()]) : null;

                foreach($question->getChoiceQuestion()->getOptions() as $option) {
                    $options[$option->getText()] = $option->getId();
                    $attr[$option->getText()] = [
                        'data-question-id' => $question->getId(),
                        'data-question-type-id' => AnswerType::CHOICE,
                    ];

                    if ((int) $option->getId() === (int) $data) {
                        $attr[$option->getText()]['checked'] = true;
                    }
                }

                $form->add('c_' . $index, ChoiceType::class, [
                    'choice_attr' => $attr,
                    'label' => $question->getChoiceQuestion()->getText(),
                    'help' => $question->getChoiceQuestion()->getHelpText(),
                    'expanded' => true,
                    'multiple' => $question->getChoiceQuestion()->getIsCheckbox(),
                    'choices' => $options,
                ]);

            } else if (null !== $question->getTextQuestion()) {

                $formData = $request->request->get('form') ?? null;
                $textQuestion = $formData['textQuestion'] ?? null;
                $data = $textQuestion !== null && array_key_exists($question->getId(), $textQuestion) ? trim($textQuestion[$question->getId()]) : null;

                $form->add('t_' . $index, TextType::class, [
                    'attr' => [
                        'data-question-id' => $question->getId(),
                        'data-question-type-id' => AnswerType::TEXT,
                        'value' => $data,
                    ],
                    'label' => $question->getTextQuestion()->getText(),
                    'help' => $question->getTextQuestion()->getHelpText(),
                    'data' => $data,
                ]);
            }
        }
    }
}
