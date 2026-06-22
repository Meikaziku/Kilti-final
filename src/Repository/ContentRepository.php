<?php

namespace App\Repository;

use App\Entity\Content;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Content>
 */
class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    /**
     * Retourne jusqu'à $limit reportages validés appartenant à la même catégorie
     * que $content, en excluant $content lui-même. Trié par vues décroissantes.
     */
    public function findSimilar(Content $content, int $limit = 6): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isValidated = true')
            ->andWhere('c.category = :category')
            ->andWhere('c.id != :id')
            ->setParameter('category', $content->getCategory())
            ->setParameter('id', $content->getId())
            ->orderBy('c.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avec filtres pour la page Explorer.
     *
     * @param int[]       $categoryIds Identifiants de catégories à inclure (vide = toutes)
     * @param string|null $duration    'gt1h' (>60min), 'lt1h' (<60min), 'asc' (tri croissant), 'desc' (tri décroissant)
     * @param int|null    $minRating   Note minimale (1 à 5)
     *
     * @return Content[]
     */
    public function findFilteredValidated(
        array $categoryIds = [],
        ?string $duration = null,
        ?int $minRating = null,
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.isValidated = true');

        if (!empty($categoryIds)) {
            $qb->andWhere('c.category IN (:cats)')
                ->setParameter('cats', $categoryIds);
        }

        // Filtres de durée : gt1h / lt1h sont des filtres ; asc / desc sont des tris
        if ($duration === 'gt1h') {
            $qb->andWhere('c.duration >= 60');
        } elseif ($duration === 'lt1h') {
            $qb->andWhere('c.duration < 60');
        }

        if ($minRating !== null && $minRating > 0) {
            $qb->andWhere('c.rating >= :minRating')
                ->setParameter('minRating', (float) $minRating);
        }

        if ($duration === 'asc') {
            $qb->orderBy('c.duration', 'ASC');
        } elseif ($duration === 'desc') {
            $qb->orderBy('c.duration', 'DESC');
        } else {
            $qb->orderBy('c.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les reportages les plus populaires (par vues).
     *
     * @return Content[]
     */
    public function findPopular(int $limit = 8): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isValidated = true')
            ->orderBy('c.views', 'DESC')
            ->addOrderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Recherche les reportages validés dont le titre contient $query
    public function searchByTitle(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isValidated = true')
            ->andWhere('c.title LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
