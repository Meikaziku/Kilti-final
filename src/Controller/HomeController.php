<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ContentRepository $contentRepo): Response
    {
        // Récupère jusqu'à 5 reportages populaires pour alimenter le carrousel
        $featured = $contentRepo->findPopular(5);

        return $this->render('home/index.html.twig', [
            'featured' => $featured,
        ]);
    }
}
