<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\ContentCategory;
use App\Entity\Watchlist;
use App\Repository\WatchlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WatchlistController extends AbstractController
{
    /**
     * Page d'affichage de la watchlist de l'utilisateur connecté.
     * Accepte un paramètre optionnel ?category=<id> pour filtrer par catégorie.
     */
    #[Route('/watchlist', name: 'app_watchlist')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $categoryId = $request->query->get('category');

        // Requête principale : items de la watchlist (avec jointure sur Content + Category)
        $qb = $em->getRepository(Watchlist::class)
            ->createQueryBuilder('w')
            ->join('w.content', 'c')
            ->addSelect('c')
            ->join('c.category', 'cat')
            ->addSelect('cat')
            ->where('w.user = :user')
            ->orderBy('w.createdAt', 'DESC');

        $qb->setParameter('user', $user);

        if ($categoryId) {
            $qb->andWhere('c.category = :cat');
            $qb->setParameter('cat', $categoryId);
        }

        $watchlist = $qb->getQuery()->getResult();

        // Catégories distinctes présentes dans la watchlist (pour les boutons de filtre)
        // On part de ContentCategory comme racine pour pouvoir le sélectionner directement.
        $availableCategories = $em->createQueryBuilder()
            ->select('DISTINCT cat')
            ->from(ContentCategory::class, 'cat')
            ->join(Content::class, 'c', 'WITH', 'c.category = cat')
            ->join(Watchlist::class, 'w', 'WITH', 'w.content = c')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('cat.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('watchlist/index.html.twig', [
            'watchlist' => $watchlist,
            'watchlistCount' => count($watchlist),
            'availableCategories' => $availableCategories,
            'currentCategory' => $categoryId,
        ]);
    }

    /**
     * Toggle (ajout/retrait) d'un reportage dans la watchlist de l'utilisateur connecté.
     *
     * Méthode POST uniquement (mutation d'état → protection CSRF via token Symfony).
     * Redirige vers la page d'où vient l'utilisateur (referer) ou vers /watchlist par défaut.
     */
    #[Route('/watchlist/toggle/{id}', name: 'app_watchlist_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(
        Content $content,
        Request $request,
        WatchlistRepository $watchlistRepo,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Vérification du jeton CSRF (transmis par le formulaire dans le composant carte)
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('watchlist-toggle-' . $content->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $existing = $watchlistRepo->findOneBy([
            'user' => $user,
            'content' => $content,
        ]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
            $this->addFlash('success', sprintf('« %s » a été retiré de votre watchlist.', $content->getTitle()));
        } else {
            $watchlist = new Watchlist();
            $watchlist->setUser($user);
            $watchlist->setContent($content);
            $watchlist->setCreatedAt(new \DateTimeImmutable());
            $em->persist($watchlist);
            $em->flush();
            $this->addFlash('success', sprintf('« %s » a été ajouté à votre watchlist.', $content->getTitle()));
        }

        // Redirection vers la page d'origine
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_watchlist');
    }
}
