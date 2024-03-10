<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\TicketMessage;
use App\Repository\TicketMessageRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Constraints\File;

/**
 * @Route("/admin/ticket")
 * @Security("is_granted('ROLE_SUPPORT')")
 */
class TicketController extends AbstractController
{

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var TicketMessageRepository
     */
    private $ticketMessageRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
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
     * @Route("/list", name="admin_ticket_list")
     * @Security("is_granted('ROLE_SUPPORT')")
     */
    public function list(): Response
    {
        $tickets = $this->ticketRepository->findBy(['status' => false], ['createdAt' => 'DESC']);

        return $this->render('admin/ticket/list.html.twig', ['tickets' => $tickets]);
    }

    /**
     * @Route("/list/all", name="admin_all_ticket_list")
     * @Security("is_granted('ROLE_SUPPORT')")
     */
    public function all(): Response
    {
        $tickets = $this->ticketRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/ticket/list.html.twig', ['tickets' => $tickets]);
    }

    /**
     * @Route("/detail/{ticketId}", name="admin_ticket_detail", requirements={"ticketId":"\d+"})
     * @Security("is_granted('ROLE_SUPPORT')")
     */
    public function detail(int $ticketId, Request $request): Response
    {
        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId]);

        if (null === $ticket) {
            throw new NotFoundHttpException('Not found ticket.');
        }

        $ticketMessage = new TicketMessage();
        $ticketMessage->setUser($this->getUser());
        $ticketMessage->setTicket($ticket);

        $form = $this->createFormBuilder($ticketMessage, ['allow_extra_fields' => true])
            ->add(
                'message',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Cevap',
                    'attr' => [
                        'placeholder' => 'Konuyla ilgili cevap vermek isterseniz mesajınızı buraya yazabilirsiniz',
                        'class' => 'form-control'
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
                    'row_attr' => [
                        'class' => 'mt-3 mb-3'
                    ],
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
            ->add('save', SubmitType::class, ['label' => 'Gönder', 'attr' => ['class' => 'button button-primary'], 'row_attr' => ['class' => 'mt-2 mb-2']])
            ->getForm();

        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {

            $ticket->setStatus(true);

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
            'admin/ticket/detail.html.twig',
            [
                'ticket' => $ticket,
                'ticketMessages' => $ticketMessages,
                'success' => $success,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/update-status/{ticketId}", name="admin_update_ticket_status", requirements={"ticketId","\d+"})
     * @Security("is_granted('ROLE_SUPPORT')")
     */
    public function changeStatus(int $ticketId, Request $request): Response
    {
        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId]);

        if (null === $ticket) {
            throw new NotFoundHttpException('Ticket not found');
        }

        $status = $request->query->getBoolean('status');
        $ticket->setStatus($status);

        $this->entityManager->flush();

        return $this->redirectToRoute('admin_ticket_detail', ['ticketId' => $ticketId]);
    }
}