<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\QuestionType;
use App\Form\Type\QuestionTypeType;
use App\Repository\QuestionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 * @package App\Controller\Admin
 * @Route("/question-type")
 */
class QuestionTypeController extends AbstractController
{
    /**
     * @var QuestionTypeRepository
     */
    private $questionTypeRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuestionTypeRepository $questionTypeRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->questionTypeRepository = $questionTypeRepository;
    }

    /**
     * @Route("/", name="question_type_list")
     */
    public function list(): Response
    {
        $questionTypes = $this->questionTypeRepository->findBy(['isDeleted' => false]);

        return $this->render('admin/question/type.list.html.twig', [
            'questionTypes' => $questionTypes,
        ]);
    }

    /**
     * @Route("/new", name="question_type_new")
     */
    public function new(Request $request): Response
    {
        $question = new QuestionType();

        $form = $this->createForm(QuestionTypeType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Question $question */
            $question = $form->getData();

            $this->entityManager->persist($question);
            $this->entityManager->flush();

            return $this->redirectToRoute('question_type_list');
        }

        return $this->render('admin/question/new.type.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{questionTypeId}", name="question_type_edit", requirements={"questionTypeId":"\d+"})
     */
    public function edit(int $questionTypeId, Request $request): Response
    {
        $question = $this->questionTypeRepository->find($questionTypeId);

        $form = $this->createForm(QuestionTypeType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Question $question */
            $question = $form->getData();

            $this->entityManager->flush();

            return $this->redirectToRoute('question_type_list');
        }

        return $this->render('admin/question/new.type.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{questionTypeId}", name="question_type_delete", methods={"DELETE"}, requirements={"questionTypeId":"\d+"})
     * @param int $questionTypeId
     * @return RedirectResponse
     */
    public function delete(int $questionTypeId): RedirectResponse
    {
        $questionType = $this->questionTypeRepository->find($questionTypeId);

        if (null === $questionType) {
            throw new NotFoundHttpException('not found question type');
        }

        $questionType->setIsDeleted(true);
        $questionType->setDeletedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->redirectToRoute('question_type_list');
    }
}