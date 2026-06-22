<?php

namespace App\Repository;

use App\Entity\Content;
use App\Entity\Rating;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function findOneByUserAndContent(Users $user, Content $content): ?Rating
    {
        return $this->findOneBy(['user' => $user, 'content' => $content]);
    }

    /**
     * Moyenne des notes sur un reportage, ou null si aucune note.
     */
    public function averageForContent(Content $content): ?float
    {
        $avg = $this->createQueryBuilder('r')
            ->select('AVG(r.value)')
            ->where('r.content = :content')
            ->setParameter('content', $content)
            ->getQuery()
            ->getSingleScalarResult();

        return $avg === null ? null : (float) $avg;
    }

    public function countForContent(Content $content): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.content = :content')
            ->setParameter('content', $content)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre d'avis (notes avec un commentaire) sur un reportage.
     */
    public function countCommentedByContent(Content $content): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.content = :content')
            ->andWhere('r.comment IS NOT NULL')
            ->setParameter('content', $content)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Les N avis les plus récents (commentaire non vide) d'un reportage.
     *
     * @return Rating[]
     */
    public function findRecentCommentedByContent(Content $content, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.content = :content')
            ->andWhere('r.comment IS NOT NULL')
            ->setParameter('content', $content)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
