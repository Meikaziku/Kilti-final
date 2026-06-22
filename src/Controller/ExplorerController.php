<?php

namespace App\Controller;

use App\Repository\ContentCategoryRepository;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExplorerController extends AbstractController
{
    // Page d'exploration : liste filtrable des reportages validés
    #[Route('/explorer', name: 'app_explorer')]
    public function index(
        Request $request,
        ContentRepository $contentRepo,
        ContentCategoryRepository $categoryRepo,
    ): Response {
        // Récupération des filtres depuis l'URL
        $genres = $request->query->all('genres');
        $duration = $request->query->get('duration');
        $minRating = $request->query->get('min_rating');

        // On ne garde que des identifiants de catégories numériques
        $categoryIds = [];
        foreach ($genres as $genre) {
            if (is_numeric($genre)) {
                $categoryIds[] = (int) $genre;
            }
        }

        // Note minimale (null si pas de filtre)
        $minRatingValue = null;
        if ($minRating !== null) {
            $minRatingValue = (int) $minRating;
        }

        $contents = $contentRepo->findFilteredValidated($categoryIds, $duration, $minRatingValue);

        // Toutes les catégories pour le drawer de filtres
        $categories = $categoryRepo->findAll();

        // Section "Populaire chez Kilti" (ne dépend pas des filtres)
        $popular = $contentRepo->findPopular(8);

        // Reportage vedette = premier résultat
        $featured = $contents[0] ?? null;

        return $this->render('explorer/index.html.twig', [
            'contents' => $contents,
            'featured' => $featured,
            'popular' => $popular,
            'categories' => $categories,
            'currentGenres' => $categoryIds,
            'currentDuration' => $duration,
            'currentRating' => $minRating,
            'hasFilters' => !empty($categoryIds) || $duration || $minRating,
        ]);
    }
}
