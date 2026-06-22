<?php

namespace App\Controller\Admin;

use App\Entity\ContentCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContentCategory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            ImageField::new('image', 'Image')
                ->setBasePath('/uploads/categories')
                ->setUploadDir('public/uploads/categories/'),
        ];
    }
}