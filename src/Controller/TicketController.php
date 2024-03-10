<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use App\Repository\TicketMessageRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

/**
 * @Route("/ticket")
 */
class TicketController extends AbstractController
{
    /**
     * @var TicketRepository
     */
    private $ticketRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TicketMessageRepository
     */
    private $ticketMessageRepository;
    /**
     * @var string
     */
    private $uploadDirectory;

    public function __construct(
        TicketRepository $ticketRepository,
        TicketMessageRepository $ticketMessageRepository,
        EntityManagerInterface $entityManager,
        string $uploadDirectory
    )
    {
        $this->ticketRepository = $ticketRepository;
        $this->ticketMessageRepository = $ticketMessageRepository;
        $this->entityManager = $entityManager;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * @Route("/list", name="ticket_list")
     */
    public function list(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (false === $user->getInWhitelist()) {
            return $this->redirectToRoute('home');
        }

        $tickets = $this->ticketRepository->findBy(['user' => $user], ['updatedAt' => 'DESC'], 20);
        return $this->render('ticket/list.html.twig', ['tickets' => $tickets]);
    }

    /**
     * @Route("/detail/{ticketId}", name="ticket_detail", requirements={"ticketId":"\d+"})
     * @param int $ticketId
     * @return Response
     */
    public function detail(int $ticketId, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (false === $user->getInWhitelist()) {
            return $this->redirectToRoute('home');
        }

        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId, 'user' => $user]);

        if (null === $ticket) {
            throw new NotFoundHttpException('Not found ticket.');
        }

        $ticketMessage = new TicketMessage();
        $ticketMessage->setUser($user);
        $ticketMessage->setTicket($ticket);

        $form = $this->createFormBuilder($ticketMessage, ['allow_extra_fields' => true])
            ->add(
                'message',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Cevap',
                    'attr' => [
                        'placeholder' => 'Konuyla ilgili cevap vermek isterseniz mesajınızı buraya yazabilirsiniz'
                    ],
                    'row_attr' => [
                        'class' => 'mt-4'
                    ]
                ]
            )
            ->add(
                'images',
                FileType::class,
                [
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Ek Dosyalar (opsiyonel)',
                    'mapped' => false,
                    'constraints' => [
                        new All([
                            new File([
                                'maxSize' => '10m',
                                'mimeTypes' => [
                                    'image/bmp',
                                    'image/gif',
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ],
                                'mimeTypesMessage' => 'Lütfen sadece görsel dosya yüklediğinizden emin olunuz!',
                            ])
                        ])
                    ],
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Gönder', 'attr' => ['class' => 'ibtn'], 'row_attr' => ['class' => 'form-button mt-2 mb-2']])
            ->getForm();

        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {

            $ticket->setStatus(false);

            /** @var UploadedFile[] $brochureFile */
            $uploadedImages = $form->get('images')->getData();

            /** @var UploadedFile $uploadedImage */
            foreach ($uploadedImages as $uploadedImage) {

                $originalFilename = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $mimeType = $uploadedImage->getClientMimeType();
                $size = $uploadedImage->getSize();

                $safeFilename = md5($originalFilename . '_' . time());
                $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$uploadedImage->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $uploadedImage->move(
                        $this->uploadDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $image = new Image();
                $image
                    ->setMimeType($mimeType)
                    ->setOriginalName($originalFilename)
                    ->setPath($newFilename)
                    ->setSize($size);

                $this->entityManager->persist($image);

                $ticketMessage->addImage($image);
            }

            $this->entityManager->persist($ticketMessage);
            $this->entityManager->flush();

            $success = true;
        }

        $ticketMessages = $this->ticketMessageRepository->findBy(['ticket' => $ticket]);

        return $this->render(
            'ticket/detail.html.twig',
            [
                'ticket' => $ticket,
                'ticketMessages' => $ticketMessages,
                'success' => $success,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/new", name="ticket_create")
     * @return Response
     */
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (false === $user->getInWhitelist()) {
            return $this->redirectToRoute('home');
        }

        $ticket = new Ticket();
        $ticket->setUser($user);

        $form = $this->createFormBuilder($ticket, ['allow_extra_fields' => true])
            ->add(
                'title',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Başlık',
                    'attr' => [
                        'placeholder' => 'Sorununuz hakkında bir başlık yazın'
                    ]
                ]
            )
            ->add(
                'content',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'İçerik',
                    'attr' => [
                        'placeholder' => 'Sorununuzun detaylarını yazınız'
                    ]
                ]
            )
            ->add(
                'images',
                FileType::class,
                [
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Ek Dosyalar (opsiyonel)',
                    'mapped' => false,
                    'constraints' => [
                        new All([
                            new File([
                                'maxSize' => '10m',
                                'mimeTypes' => [
                                    'image/bmp',
                                    'image/gif',
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ],
                                'mimeTypesMessage' => 'Lütfen sadece görsel dosya yüklediğinizden emin olunuz!',
                            ])
                        ])
                    ],
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Kaydet', 'attr' => ['class' => 'ibtn'], 'row_attr' => ['class' => 'form-button']])
            ->getForm();

        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile[] $brochureFile */
            $uploadedImages = $form->get('images')->getData();

            /** @var UploadedFile $uploadedImage */
            foreach ($uploadedImages as $uploadedImage) {

                $originalFilename = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $mimeType = $uploadedImage->getClientMimeType();
                $size = $uploadedImage->getSize();

                $safeFilename = md5($originalFilename . '_' . time());
                $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$uploadedImage->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $uploadedImage->move(
                        $this->uploadDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $image = new Image();
                $image
                    ->setMimeType($mimeType)
                    ->setOriginalName($originalFilename)
                    ->setPath($newFilename)
                    ->setSize($size);

                $this->entityManager->persist($image);

                $ticket->addImage($image);
            }

            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
            $success = true;
        }

        return $this->render('ticket/create.html.twig', ['form' => $form->createView(), 'success' => $success]);
    }

}