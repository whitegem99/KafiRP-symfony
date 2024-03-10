<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 * @package App\Controller\Admin
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{

    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        SettingsRepository $settingsRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->settingsRepository = $settingsRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="settings")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function settings(RequestStack $requestStack): Response
    {
        $settings = $this->settingsRepository->find(1);

        if (null === $settings) {
            throw new NotFoundHttpException('Ayarlar bulunamadı');
        }

        $request = $requestStack->getCurrentRequest();

        if ($request !== null && $request->isMethod('POST')) {
            $openToApplication = $request->request->getBoolean('openToApplication');
            $settings->setOpenToApplication($openToApplication);

            $archiveHistory = $request->request->get('archiveHistory');
            if (true !== empty($archiveHistory)) {
                $settings->setArchiveHistory(new \DateTimeImmutable($archiveHistory));
            } else {
                $settings->setArchiveHistory(null);
            }

            if ($request->request->getBoolean('forEveryBody')) {
                $settings->setAllowDiscordRoles([]);
            } else {
                $allowedDiscordRoles = $request->request->get('allowedDiscordRoleIds');
                $settings->setAllowDiscordRoles(explode(',', $allowedDiscordRoles));
            }

            $this->entityManager->flush();
        }

        return $this->render('admin/settings/index.html.twig', [
            'settings' => $settings,
        ]);
    }
}