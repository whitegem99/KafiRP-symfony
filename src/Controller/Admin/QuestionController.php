<?php

namespace App\Controller\Admin;

use App\Entity\ChoiceQuestion;
use App\Entity\Option;
use App\Entity\Question;
use App\Entity\TextQuestion;
use App\Form\Type\ChoiceQuestionType;
use App\Form\Type\QuestionType;
use App\Form\Type\TextQuestionType;
use App\Repository\ChoiceQuestionRepository;
use App\Repository\QuestionRepository;
use App\Repository\TextQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 * @package App\Controller\Admin
 * @Route("/admin/question")
 */
class QuestionController extends AbstractController
{

    /**
     * @var QuestionRepository
     */
    private $questionRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TextQuestionRepository
     */
    private $textQuestionRepository;
    /**
     * @var ChoiceQuestionRepository
     */
    private $choiceQuestionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuestionRepository $questionRepository,
        TextQuestionRepository $textQuestionRepository,
        ChoiceQuestionRepository $choiceQuestionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->questionRepository = $questionRepository;
        $this->textQuestionRepository = $textQuestionRepository;
        $this->choiceQuestionRepository = $choiceQuestionRepository;
    }

    /**
     * @Route("/", name="question_list")
     */
    public function list(): Response
    {
        $questions = $this->questionRepository->findBy([], ['sort' => 'ASC']);

        return $this->render('admin/question/list.html.twig', [
            'questions' => $questions,
        ]);
    }

    /**
     * @Route("/text-questions", name="question_text_list")
     */
    public function textQuestionList(): Response
    {
        $questions = $this->textQuestionRepository->findAll();

        return $this->render('admin/question/question.list.html.twig', [
            'questions' => $questions,
            'delete_route' => 'question_text_delete',
            'title' => 'Metin Soruları'
        ]);
    }

    /**
     * @Route("/choices-questions", name="question_choices_list")
     */
    public function choicesQuestionList(): Response
    {
        $questions = $this->choiceQuestionRepository->findAll();

        return $this->render('admin/question/question.list.html.twig', [
            'questions' => $questions,
            'delete_route' => 'question_choice_delete',
            'title' => 'Çoktan Seçmeli Sorular'
        ]);
    }

    /**
     * @Route("/new-text", name="question_new_text")
     */
    public function newTextQuestion(Request $request): Response
    {
        $textQuestion = new TextQuestion();

        $form = $this->createForm(TextQuestionType::class, $textQuestion);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Question $textQuestion */
            $textQuestion = $form->getData();

            $this->entityManager->persist($textQuestion);
            $this->entityManager->flush();

            return $this->redirectToRoute('question_text_list');
        }

        return $this->render('admin/question/new.text.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new-choice", name="question_new_choice")
     */
    public function newChoiceQuestion(Request $request): Response
    {
        $choiceQuestion = new ChoiceQuestion();

        $option = new Option();
        $option
            ->setText('Seçenek 1');

        $option2 = new Option();
        $option2->setText('Seçenek 2');

        $choiceQuestion->addOption($option);
        $choiceQuestion->addOption($option2);

        $form = $this->createForm(ChoiceQuestionType::class, $choiceQuestion);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Question $choiceQuestion */
            $choiceQuestion = $form->getData();

            $this->entityManager->persist($choiceQuestion);
            $this->entityManager->flush();

            return $this->redirectToRoute('question_choices_list');
        }

        return $this->render('admin/question/new.choice.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="question_new")
     */
    public function new(Request $request): Response
    {
        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Question $question */
            $question = $form->getData();

            $this->entityManager->persist($question);
            $this->entityManager->flush();

            return $this->redirectToRoute('question_list');
        }

        return $this->render('admin/question/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/active/{id}", name="question_active", requirements={"id":"\d+"})
     * @param int $id
     * @return RedirectResponse
     */
    public function setActive(int $id): RedirectResponse
    {
        $question = $this->questionRepository->find($id);

        if (null === $question) {
            throw new NotFoundHttpException('Not found question');
        }

        $question->setStatus(true);
        $this->entityManager->flush();

        return $this->redirectToRoute('question_list');
    }

    /**
     * @Route("/deactive/{id}", name="question_deactive", requirements={"id":"\d+"})
     * @param int $id
     * @return RedirectResponse
     */
    public function setDeactive(int $id): RedirectResponse
    {
        $question = $this->questionRepository->find($id);

        if (null === $question) {
            throw new NotFoundHttpException('Not found question');
        }

        $question->setStatus(false);
        $this->entityManager->flush();

        return $this->redirectToRoute('question_list');
    }

    /**
     * @Route("/delete/{id}", name="question_delete", requirements={"id":"\d+"})
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $question = $this->questionRepository->find($id);

        if (null === $question) {
            throw new NotFoundHttpException('Not found question');
        }

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        return $this->redirectToRoute('question_list');
    }

    /**
     * @Route("/text-delete/{id}", name="question_text_delete", requirements={"id":"\d+"})
     */
    public function deleteTextQuestion(int $id): RedirectResponse
    {
        $question = $this->textQuestionRepository->find($id);

        if (null === $question) {
            throw new NotFoundHttpException('Not found question');
        }

        $appQuestions = $this->questionRepository->findBy(['textQuestion' => $question]);
        if (count($appQuestions) > 0) {
            foreach($appQuestions as $appQuestion) {
                $this->entityManager->remove($appQuestion);
            }
        }

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        return $this->redirectToRoute('question_text_list');
    }

    /**
     * @Route("/choice-delete/{id}", name="question_choice_delete", requirements={"id":"\d+"})
     */
    public function deleteChoiceQuestion(int $id): RedirectResponse
    {
        $question = $this->choiceQuestionRepository->find($id);

        if (null === $question) {
            throw new NotFoundHttpException('Not found question');
        }

        $appQuestions = $this->questionRepository->findBy(['choiceQuestion' => $question]);
        if (count($appQuestions) > 0) {
            foreach($appQuestions as $appQuestion) {
                $this->entityManager->remove($appQuestion);
            }
        }

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        return $this->redirectToRoute('question_choices_list');
    }

    /**
     * @Route("/update-random", name="question_update_random", methods={"PUT"})
     * @param RequestStack $requestStack
     * @return JsonResponse
     */
    public function ajaxUpdateRandomQuestionAction(RequestStack $requestStack): JsonResponse
    {
        $data = json_decode($requestStack->getCurrentRequest()->getContent(), true);
        $requestStack->getCurrentRequest()->request->replace(is_array($data) ? $data : []);

        $questionId = $data['questionId'];
        $isRandom = $data['random'];

        /** @var Question $question */
        $question = $this->questionRepository->find($questionId);

        if (null === $question) {
            return $this->json(['success' => false, 'errorMessage' => 'Soru ID hatalı olduğu için işlem yapılamadı.']);
        }

        $question->setRandom($isRandom);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
