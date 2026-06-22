<?php

namespace App\Controller;

use App\Entity\Content;
use App\Service\RatingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RatingController extends AbstractController
{
    #[Route('/content/{id}/rate', name: 'app_content_rate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function rate(Content $content, Request $request, RatingService $ratingService): Response
    {
        // Protection CSRF
        if (!$this->isCsrfTokenValid('rate' . $content->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('app_content_show', ['id' => $content->getId()]);
        }

        // Le reportage doit être validé pour pouvoir être noté
        if (!$content->isValidated()) {
            $this->addFlash('error', 'Ce reportage n’est pas encore disponible à la notation.');
            return $this->redirectToRoute('app_content_show', ['id' => $content->getId()]);
        }

        // On ne note pas son propre reportage
        if ($content->getUser() === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas noter votre propre reportage.');
            return $this->redirectToRoute('app_content_show', ['id' => $content->getId()]);
        }

        $value = (int) $request->request->get('value');

        $comment = trim((string) $request->request->get('comment', ''));
        $comment = $comment === '' ? null : $comment; // vide => on stocke null

        try {
            $ratingService->rate($this->getUser(), $content, $value, $comment);
            $this->addFlash('success', 'Merci pour votre avis !');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_content_show', ['id' => $content->getId()]);
    }
}
