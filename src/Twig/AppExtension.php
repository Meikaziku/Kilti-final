<?php

namespace App\Twig;

use App\Repository\ContentCategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private ContentCategoryRepository $categoryRepository;

    public function __construct(ContentCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('kilti_categories', [$this, 'getCategories']),
        ];
    }

    // Renvoie toutes les catégories triées par nom.
    // Utilisé par le filter_drawer (inclus partout via base.html.twig) pour avoir
    // les catégories sur toutes les pages sans avoir à les passer depuis chaque contrôleur.
    public function getCategories(): array
    {
        return $this->categoryRepository->findBy([], ['name' => 'ASC']);
    }
}
