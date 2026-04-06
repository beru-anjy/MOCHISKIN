<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    // Injection du chemin du fichier lock et du moteur Twig
    public function __construct(
        private string $lockFilePath,
        private Environment $twig
    ) {}

    // Abonnement à l'événement REQUEST — priorité 10 pour s'exécuter tôt
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // On ignore les sous-requêtes (ex: fragments Twig)
        if (!$event->isMainRequest()) {
            return;
        }

        // Si le fichier lock existe → site en maintenance
        // On retourne une page 503 sans exécuter le reste de l'application
        if (file_exists($this->lockFilePath)) {
            $html = $this->twig->render('maintenance/index.html.twig');
            $event->setResponse(new Response($html, Response::HTTP_SERVICE_UNAVAILABLE));
        }
    }
}