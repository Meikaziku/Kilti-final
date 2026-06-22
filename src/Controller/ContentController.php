<?php

namespace App\Controller;

use App\Entity\Content;
use App\Repository\ContentRepository;
use App\Repository\RatingRepository;
use App\Repository\WatchlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class ContentController extends AbstractController
{
    // Page de détail d'un reportage
    #[Route('/content/{id}', name: 'app_content_show', requirements: ['id' => '\d+'])]
    public function show(
        Content $content,
        Request $request,
        ContentRepository $contentRepo,
        WatchlistRepository $watchlistRepo,
        RatingRepository $ratingRepo,
        EntityManagerInterface $em,
    ): Response {
        // On n'affiche que les reportages validés, sauf pour les modérateurs/admins
        // qui peuvent prévisualiser ceux en attente
        if (!$content->isValidated() && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createNotFoundException('Reportage introuvable.');
        }

        // On compte une vue seulement pour les visiteurs publics (pas les modérateurs)
        $viewed = $request->getSession()->get('viewed_contents', []);

        if (
            $content->isValidated()
            && !$this->isGranted('ROLE_MODERATOR')
            && !in_array($content->getId(), $viewed, true)
        ) {

            $content->incrementViews();
            $em->flush();

            $viewed[] = $content->getId();
            $request->getSession()->set('viewed_contents', $viewed);
        }

        // Reportages de la même catégorie pour la section "Recommandations"
        $similar = $contentRepo->findSimilar($content, 6);

        // Est-ce que l'utilisateur connecté a ce reportage dans sa watchlist ?
        $isInWatchlist = false;
        if ($this->getUser()) {
            $existing = $watchlistRepo->findOneBy([
                'user' => $this->getUser(),
                'content' => $content,
            ]);
            $isInWatchlist = $existing !== null;
        }

        // Note déjà donnée par l'utilisateur connecté (0 s'il n'a pas encore noté)
        $userRating = 0;
        $userComment = '';
        if ($this->getUser() && $this->getUser() !== $content->getUser()) {
            $rating = $ratingRepo->findOneByUserAndContent($this->getUser(), $content);
            if ($rating !== null) {
                $userRating = $rating->getValue();
                $userComment = $rating->getComment() ?? '';
            }
        }

        // On n'affiche que les 5 avis les plus récents ; on garde le total à part
        $avis = $ratingRepo->findRecentCommentedByContent($content, 5);
        $avisTotal = $ratingRepo->countCommentedByContent($content);

        return $this->render('content/show.html.twig', [
            'content' => $content,
            'similar' => $similar,
            'isInWatchlist' => $isInWatchlist,
            'userRating' => $userRating,
            'userComment' => $userComment,
            'avis' => $avis,
            'avisTotal' => $avisTotal,
        ]);
    }
}
