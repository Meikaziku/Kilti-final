<?php

namespace App\Service;

use App\Entity\Content;
use App\Entity\Rating;
use App\Entity\Users;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;

class RatingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RatingRepository $ratingRepository,
    ) {}

    /**
     * Crée ou met à jour la note d'un utilisateur sur un reportage,
     * puis recalcule la moyenne et le compteur sur le reportage.
     */
    public function rate(Users $user, Content $content, int $value, ?string $comment = null): Rating
    {
        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5.');
        }

        $rating = $this->ratingRepository->findOneByUserAndContent($user, $content);

        if ($rating === null) {
            $rating = (new Rating())
                ->setUser($user)
                ->setContent($content);
            $this->em->persist($rating);
        }

        $rating->setValue($value);
        $rating->setComment($comment);
        $this->em->flush();

        $this->recomputeAggregates($content);

        return $rating;
    }

    /**
     * Recalcule la moyenne et le nombre de notes d'un reportage.
     * À appeler après chaque ajout/modification/suppression de Rating.
     */
    public function recomputeAggregates(Content $content): void
    {
        $avg = $this->ratingRepository->averageForContent($content);
        $count = $this->ratingRepository->countForContent($content);

        $content->setRating($avg);
        $content->setRatingsCount($count);

        $this->em->flush();
    }
}
