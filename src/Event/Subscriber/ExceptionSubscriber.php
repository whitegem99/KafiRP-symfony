<?php declare(strict_types=1);

namespace App\Event\Subscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\Error\OutOfMemoryError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $templateEngine;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $kernelEnvironment;

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function __construct(
        Environment $engineInterface,
        LoggerInterface $logger,
        string $kernelEnvironment
    )
    {
        $this->templateEngine = $engineInterface;
        $this->logger = $logger;
        $this->kernelEnvironment = $kernelEnvironment;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ('dev' === $this->kernelEnvironment) {
            return;
        }

        $response = new Response();
        $logId = microtime(true);
        $templateName = null;
        $statusCode = null;
        $message = null;
        $errorMessage = null;
        $displayReferer = false;

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }
        else
            $statusCode = $exception->getCode();

        switch($statusCode)
        {
            case 401:
                //get current url to be able to set the redirectUri
                $redirectUrl = $event->getRequest()->getRequestUri();

                if(empty($redirectUrl))
                    $url = $exception->getMessage();
                else
                    $url = rtrim($exception->getMessage(), '/') . '?returnUri=' . urlencode($redirectUrl);

                $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($url));
                break;
            case 403:
                $templateName = '/error/index.html.twig';
                $message = $exception->getMessage();
                $errorMessage = 'Bu sayfayı görüntüleme yetkiniz bulunmamaktadır.';
                break;
            case 404:
                $templateName = '/error/index.html.twig';
                $displayReferer = strpos($exception->getMessage(), 'No route found') !== false;
                break;
            default:
                $templateName = '/error/index.html.twig';
                $statusCode = 404;
                break;
        }

        if($templateName !== null)
        {
            $response->setStatusCode($statusCode);
            $response->setContent($this->templateEngine->render($templateName, array('logId' => $logId, 'message' => $message, 'errorMessage' => $errorMessage)));
            $event->setResponse($response);
        }

        $event->stopPropagation();

        $path = $event->getRequest()->getPathInfo();
        $controllerName = $event->getRequest()->attributes->get('_controller');

        //avoid logging unnecessary exceptions
        //$nonAdminAuthorizationError = $statusCode == 401 && strpos($path, 'admin') === false;
        $authorizationError = $statusCode === 401;
        $noRouteError = $statusCode === 404 && $exception instanceof NotFoundHttpException && $controllerName === null;

        if(!$authorizationError && !$noRouteError) {

            $logMessage = 'LOGID: ' . $logId .
                ' M:' . $exception->getMessage() .
                ' P:' . $path .
                ' Q:' . $event->getRequest()->getQueryString() .
                ($displayReferer ? ' R:' . $event->getRequest()->headers->get('referer') : '') .
                ' F:' . $exception->getFile() . ':' . $exception->getLine();

            $logContext = [];

            if (
                $exception instanceof OutOfMemoryError
            ) {
                $logContext = [
                    'trace' => $exception->getTrace(),
                ];
            }

            $this->logger->error($logMessage, $logContext);
        }
    }
}