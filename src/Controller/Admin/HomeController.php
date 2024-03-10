<?php

namespace App\Controller\Admin;

use App\Enum\DecisionType;
use App\Repository\ApplicationRepository;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 * @Route("/admin")
 */
class HomeController extends AbstractController
{
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;

    public function __construct(
        ApplicationRepository $applicationRepository,
        UserRepository $userRepository,
        SettingsRepository $settingsRepository
    )
    {
        $this->applicationRepository = $applicationRepository;
        $this->userRepository = $userRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @Route("/", name="admin_home")
     */
    public function index(): Response
    {
        $settings = $this->settingsRepository->find(1);

        if (null !== $settings && null !== $settings->getArchiveHistory()) {
            $date = $settings->getArchiveHistory()->format('Y-m-d H:i:s');
            $notDecidedApplications = $this->applicationRepository->findByAfterArchiveHistory($date, ['decisionType' => DecisionType::NOT_DECIDED]);
            $notReviewApplications = $this->applicationRepository->findByAfterArchiveHistory($date, ['status' => false]);
            $approvedApplications = $this->applicationRepository->findByAfterArchiveHistory($date, ['decisionType' => DecisionType::APPROVED]);
            $rejectedApplications = $this->applicationRepository->findByAfterArchiveHistory($date, ['decisionType' => DecisionType::REJECTED]);
            $maybeApprovedApplications = $this->applicationRepository->findByAfterArchiveHistory($date, ['decisionType' => DecisionType::MAYBE_APPROVED]);
            $applications = $this->applicationRepository->findByAfterArchiveHistory($date);
        } else {
            $notDecidedApplications = $this->applicationRepository->findBy(['decisionType' => DecisionType::NOT_DECIDED, 'isDeleted' => false]);
            $notReviewApplications = $this->applicationRepository->findBy(['status' => false, 'isDeleted' => false]);
            $approvedApplications = $this->applicationRepository->findBy(['decisionType' => DecisionType::APPROVED, 'isDeleted' => false]);
            $rejectedApplications = $this->applicationRepository->findBy(['decisionType' => DecisionType::REJECTED, 'isDeleted' => false]);
            $maybeApprovedApplications = $this->applicationRepository->findBy(['decisionType' => DecisionType::MAYBE_APPROVED, 'isDeleted' => false]);
            $applications = $this->applicationRepository->findBy(['isDeleted' => false]);
        }

        $totalMembers = $this->userRepository->findAll();

        $applicationTempData = [];
        foreach ($applications as $application) {
            $date = $application->getCreatedAt()->format('Y-m-d');
            if (false === array_key_exists($date, $applicationTempData)) {
                $applicationTempData[$date] = 0;
            }
            $applicationTempData[$date]++;
        }

        $applicationData = [];
        foreach ($applicationTempData as $date => $count) {
            $obj = new \stdClass();
            $obj->date = $date;
            $obj->count = $count;
            $applicationData[] = $obj;
        }

        return $this->render('admin/home.html.twig', [
            'notDecidedApplicationsCount' => count($notDecidedApplications),
            'notReviewApplicationsCount' => count($notReviewApplications),
            'approvedApplicationsCount' => count($approvedApplications),
            'rejectedApplicationsCount' => count($rejectedApplications),
            'maybeApprovedApplicationsCount' => count($maybeApprovedApplications),
            'totalMembersCount' => count($totalMembers),
            'applicationData' => $applicationData
        ]);
    }

    /**
     * @Route("/error", name="static_error")
     * @return Response
     */
    public function error(): Response
    {
        return $this->render('admin/error.html.twig');
    }
}
