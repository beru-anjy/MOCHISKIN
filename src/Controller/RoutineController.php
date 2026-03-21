<?php

namespace App\Controller;

use App\Repository\RoutineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RoutineController extends AbstractController
{
    #[Route('/routine', name: 'app_routine')]
    public function index(RoutineRepository $routineRepository): Response
    {
        $morningRoutine = $routineRepository->findOneBy(['type' => 'morning']);
        $eveningRoutine = $routineRepository->findOneBy(['type' => 'evening']);

        return $this->render('routine/index.html.twig', [
            'morningRoutine' => $morningRoutine,
            'eveningRoutine' => $eveningRoutine,
        ]);
    }
}