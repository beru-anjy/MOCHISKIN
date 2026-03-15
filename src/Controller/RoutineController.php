<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RoutineController extends AbstractController
{
    #[Route('/routine', name: 'app_routine')]
    public function index(): Response
    {
        return $this->render('routine/index.html.twig', [
            'controller_name' => 'RoutineController',
        ]);
    }
}
