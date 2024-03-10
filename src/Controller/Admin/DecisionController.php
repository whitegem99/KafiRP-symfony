<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\Decision;
use App\Enum\DecisionType;
use App\Repository\ApplicationRepository;
use App\Repository\DecisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 * @package App\Controller\Admin
 * @Route("/decision")
 */
class DecisionController extends AbstractController
{
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var DecisionRepository
     */
    private $decisionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ApplicationRepository $applicationRepository,
        DecisionRepository $decisionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $applicationRepository;
        $this->decisionRepository = $decisionRepository;
    }

    /**
     * @Route("/approve/{applicationId}", name="add_approve_decision", requirements={"applicationId","\d+"})
     */
    public function approve(int $applicationId): Response
    {
        $application = $this->applicationRepository->find($applicationId);

        if (
            null === $application
            || true === $application->getIsDeleted()
        ) {
            throw new NotFoundHttpException('Not found application');
        }

        $decision = $this->prepareDecision($application);

        $decision
            ->setActingUser($this->getUser())
            ->setApplication($application)
            ->setDecisionType(DecisionType::APPROVED);

        if ($decision->getId() === null) {
            $this->entityManager->persist($decision);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("/maybe-approve/{applicationId}", name="add_maybe_approve_decision", requirements={"applicationId","\d+"})
     */
    public function maybeApprove(int $applicationId): Response
    {
        $application = $this->applicationRepository->find($applicationId);

        if (
            null === $application
            || true === $application->getIsDeleted()
        ) {
            throw new NotFoundHttpException('Not found application');
        }

        $decision = $this->prepareDecision($application);

        $decision
            ->setActingUser($this->getUser())
            ->setApplication($application)
            ->setDecisionType(DecisionType::MAYBE_APPROVED);

        if ($decision->getId() === null) {
            $this->entityManager->persist($decision);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("/reject/{applicationId}", name="add_reject_decision", requirements={"applicationId","\d+"})
     */
    public function reject(int $applicationId): Response
    {
        $application = $this->applicationRepository->find($applicationId);

        if (
            null === $application
            || true === $application->getIsDeleted()
        ) {
            throw new NotFoundHttpException('Not found application');
        }

        $decision = $this->prepareDecision($application);
        $decision
            ->setActingUser($this->getUser())
            ->setApplication($application)
            ->setDecisionType(DecisionType::REJECTED);

        if ($decision->getId() === null) {
            $this->entityManager->persist($decision);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('application_list');
    }

    /**
     * @return Decision
     */
    private function prepareDecision(Application $application): Decision
    {
        $decision = $this->decisionRepository->findOneBy(['actingUser' => $this->getUser(), 'application' => $application]);

        if (null === $decision) {
            $decision = new Decision();
        } else {
            $decision->setUpdatedAt(new \DateTimeImmutable());
        }

        return $decision;
    }
}
