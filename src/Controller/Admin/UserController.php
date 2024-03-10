<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @Route("/admin/user")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="user_list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function list(): Response
    {
        $users = $this->userRepository->findBy(['isDeleted' => false]);

        return $this->render('admin/user/list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/deleted-users", name="user_deleted_list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deletedUserList(): Response
    {
        $users = $this->userRepository->findBy(['isDeleted' => true]);

        return $this->render('admin/user/deleted.list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/new", name="user_new")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $user */
            $user = $form->getData();

            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $user->getPassword())
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{userId}", name="user_edit", requirements={"userId":"\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(int $userId, Request $request): Response
    {
        $user = $this->userRepository->find($userId);

        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        if ($user->getIsDeleted()) {
            throw new AccessDeniedException('This user is deleted!');
        }

        $currentPassword = $user->getPassword();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $user */
            $user = $form->getData();

            if (true === empty($user->getPassword())) {
                $user->setPassword($currentPassword);
            } else {
                $user->setPassword(
                    $this->passwordEncoder->encodePassword($user, $user->getPassword())
                );
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{userId}", name="user_delete", requirements={"userId":"\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @param int $userId
     * @return RedirectResponse
     */
    public function delete(int $userId): RedirectResponse
    {
        $user = $this->userRepository->find($userId);

        if (null === $user) {
            throw new NotFoundHttpException('not found user');
        }

        $user->setIsDeleted(true);
        $user->setDeletedAt(new \DateTimeImmutable());
        $user->setActingUser($this->getUser());

        $this->entityManager->flush();

        return $this->redirectToRoute('user_list');
    }

    /**
     * @Route("/enable/{userId}", name="user_enable", requirements={"userId":"\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @param int $userId
     * @return RedirectResponse
     */
    public function enable(int $userId): RedirectResponse
    {
        $user = $this->userRepository->find($userId);

        if (null === $user) {
            throw new NotFoundHttpException('not found user');
        }

        $user->setIsDeleted(false);
        $user->setDeletedAt(null);
        $user->setActingUser(null);

        $this->entityManager->flush();

        return $this->redirectToRoute('user_list');
    }
}
