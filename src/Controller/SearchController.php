<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    // Page de recherche de reportages par titre
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, ContentRepository $contentRepo): Response
    {
        $query = $request->query->get('q');
        $results = [];

        if ($query) {
            $results = $contentRepo->searchByTitle($query);
        }

        return $this->render('search/index.html.twig', [
            'query' => $query ?? '',
            'results' => $results,
        ]);
    }
}
