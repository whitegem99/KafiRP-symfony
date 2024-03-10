<?php

namespace App\Controller\Admin;

use App\Entity\Note;
use App\Entity\User;
use App\Enum\DecisionType;
use App\Event\ApplicationDecisionEvent;
use App\Repository\AnswerRepository;
use App\Repository\ApplicationRepository;
use App\Repository\DecisionRepository;
use App\Repository\NoteRepository;
use App\Repository\SettingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 * @package App\Controller\Admin
 * @Route("/application")
 */
class ApplicationController extends AbstractController
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var DecisionRepository
     */
    private $decisionRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;

    public function __construct(
        ApplicationRepository $applicationRepository,
        DecisionRepository $decisionRepository,
        SettingsRepository $settingsRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    )
    {
        $this->applicationRepository = $applicationRepository;
        $this->decisionRepository = $decisionRepository;
        $this->settingsRepository = $settingsRepository;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @Route("/list", name="application_list")
     */
    public function list(): Response
    {
        $settings = $this->settingsRepository->find(1);
        if (null !== $settings && null !== $settings->getArchiveHistory()) {
            $applications = $this->applicationRepository->findByAfterArchiveHistory($settings->getArchiveHistory()->format('Y-m-d H:i:s'));
        } else {
            $applications = $this->applicationRepository->findBy(['isDeleted' => false]);
        }

        foreach ($applications as $application) {
            $decisions = $this->decisionRepository->findBy(['application' => $application]);
            if ($application->getDecisions() === null) {
                $application->setDecisions(new ArrayCollection());
            }
            foreach ($decisions as $decision) {
                $application->addDecision($decision);
            }
        }

        return $this->render('admin/application/list.html.twig', [
            'applications' => $applications,
        ]);
    }

    /**
     * @Route("/list/archive", name="application_archive_list")
     */
    public function listArchive(): Response
    {
        $settings = $this->settingsRepository->find(1);
        if (null !== $settings && null !== $settings->getArchiveHistory()) {
            $applications = $this->applicationRepository->findByBeforeArchiveHistory($settings->getArchiveHistory()->format('Y-m-d H:i:s'));
        } else {
            $applications = [];
        }

        foreach ($applications as $application) {
            $decisions = $this->decisionRepository->findBy(['application' => $application]);
            if ($application->getDecisions() === null) {
                $application->setDecisions(new ArrayCollection());
            }
            foreach ($decisions as $decision) {
                $application->addDecision($decision);
            }
        }

        return $this->render('admin/application/list.html.twig', [
            'applications' => $applications,
        ]);
    }

    /**
     * @Route("/approved", name="approved_application_list")
     */
    public function approvedApplicationList(): Response
    {
        $applications = $this->applicationRepository->findBy(['isDeleted' => false, 'decisionType' => DecisionType::APPROVED]);

        foreach ($applications as $application) {
            $decisions = $this->decisionRepository->findBy(['application' => $application]);
            if ($application->getDecisions() === null) {
                $application->setDecisions(new ArrayCollection());
            }
            foreach ($decisions as $decision) {
                $application->addDecision($decision);
            }
        }

        return $this->render('admin/application/list.html.twig', [
            'applications' => $applications,
        ]);
    }

    /**
     * @Route("/show/{applicationId}", name="application_show", requirements={"applicationId":"\d+"})
     * @param int $applicationId
     * @return Response
     */
    public function show(int $applicationId): Response
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null === $application) {
            throw new NotFoundHttpException('Not found application');
        }

        $decisions = $this->decisionRepository->findBy(['application' => $application]);
        if ($application->getDecisions() === null) {
            $application->setDecisions(new ArrayCollection());
        }

        $isVoted = false;
        foreach ($decisions as $decision) {
            $application->addDecision($decision);
            if ($decision->getActingUser()->getId() === $this->getUser()->getId()) {
                $isVoted = true;
            }
        }

        if (null !== $application) {
            $application->setStatus(true);
            $this->entityManager->flush();
        }

        return $this->render('admin/application/show.html.twig', [
            'application' => $application,
            'isVoted' => $isVoted
        ]);
    }

    /**
     * @Route("/approve/{applicationId}", name="application_approved", requirements={"applicationId":"\d+"})
     * @param int $applicationId
     * @return RedirectResponse
     */
    public function approve(int $applicationId): RedirectResponse
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null !== $application) {
            $oldDecisionType = $application->getDecisionType();
            $application->setDecisionType(DecisionType::APPROVED);
            $application->setReviewDate(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new ApplicationDecisionEvent($application, $oldDecisionType, $this->getUser()));
        }

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("/reject/{applicationId}", name="application_reject", requirements={"applicationId":"\d+"})
     * @param int $applicationId
     * @return RedirectResponse
     */
    public function reject(int $applicationId): RedirectResponse
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null !== $application) {
            $oldDecisionType = $application->getDecisionType();
            $application->setDecisionType(DecisionType::REJECTED);
            $application->setReviewDate(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new ApplicationDecisionEvent($application, $oldDecisionType, $this->getUser()));
        }

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("/maybe-approve/{applicationId}", name="application_maybe_approve", requirements={"applicationId":"\d+"})
     * @param int $applicationId
     * @return RedirectResponse
     */
    public function maybeApprove(int $applicationId): RedirectResponse
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null !== $application) {
            $oldDecisionType = $application->getDecisionType();
            $application->setDecisionType(DecisionType::MAYBE_APPROVED);
            $application->setReviewDate(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new ApplicationDecisionEvent($application, $oldDecisionType, $this->getUser()));
        }

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("/delete/{applicationId}", name="application_delete", requirements={"applicationId":"\d+"})
     * @param int $applicationId
     * @return RedirectResponse
     */
    public function delete(int $applicationId): RedirectResponse
    {
        $application = $this->applicationRepository->find($applicationId);

        if (null !== $application) {
            $application->setIsDeleted(true);
            $application->setDeletedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('application_list');
    }

    /**
     * @Route("add-note", name="application_add_note", methods={"POST"})
     * @param RequestStack $requestStack
     * @return RedirectResponse
     */
    public function addNote(RequestStack $requestStack): RedirectResponse
    {
        $applicationId = $requestStack->getCurrentRequest()->request->getInt('applicationId');
        $noteText = $requestStack->getCurrentRequest()->request->get('note');
        /** @var User $user */
        $user = $this->getUser();
        $referrer = $requestStack->getCurrentRequest()->headers->get('referer');

        if (
            $applicationId > 0
            && null !== $noteText
            && '' !== $noteText
            && null !== $user
        ) {
            $application = $this->applicationRepository->find($applicationId);

            if (null !== $application) {
                $note = new Note();
                $note
                    ->setApplication($application)
                    ->setUser($user)
                    ->setContent($noteText)
                    ->setCreatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($note);
                $this->entityManager->flush();

                $this->addFlash('success', 'Başvuru notu başarıyla eklendi.');

                return $this->redirect($referrer);
            }
        }

        $this->addFlash('error', 'Başvuru notu eklerken bir hata meydana geldi.');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/purge/{applicationId}", name="application_purge", requirements={"application_purge": "\d+"})
     */
    public function ApplicationPurgeAction(
        int $applicationId,
        NoteRepository $noteRepository,
        AnswerRepository $answerRepository
    ): RedirectResponse
    {
        $user = $this->getUser();

        if (false === in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->logger->info('Arkadaşın canı sıkılıyor galiba', ['user' => $user]);
            throw new AccessDeniedHttpException('Yavaş kardeşim, yavaş...');
        }

        $application = $this->applicationRepository->find($applicationId);

        if (null === $application) {
            throw new NotFoundHttpException('Başvuru bulunamadı.');
        }

        // Note
        $notes = $noteRepository->findBy(['application' => $application]);
        if (count($notes) > 0) {
            foreach ($notes as $note) {
                $this->entityManager->remove($note);
            }
        }

        // Decision
        $decisions = $this->decisionRepository->findBy(['application' => $application]);
        if (count($decisions) > 0) {
            foreach ($decisions as $decision) {
                $this->entityManager->remove($decision);
            }
        }

        // Answer
        $answers = $answerRepository->findBy(['application' => $application]);
        if (count($answers) > 0) {
            foreach ($answers as $answer) {
                $this->entityManager->remove($answer);
            }
        }

        $this->entityManager->remove($application);
        $this->entityManager->flush();

        return $this->redirectToRoute('application_list');
    }
}
