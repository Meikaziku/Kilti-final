<?php

namespace App\Controller\Admin;

use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Content::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Informations
            AssociationField::new('user', 'Auteur'),
            TextField::new('title', 'Titre'),
            TextareaField::new('description', 'Description'),
            UrlField::new('url', 'URL du reportage'),

            // Image optionnelle : si l'admin n'upload rien, EasyAdmin garde l'image existante
            ImageField::new('image', 'Image')
                ->setBasePath('/uploads')
                ->setUploadDir('public/uploads/')
                ->setRequired(false),

            IntegerField::new('duration', 'Durée (minutes)')
                ->setHelp('Durée du reportage en minutes (ex: 72 pour 1h12).'),

            AssociationField::new('category', 'Catégorie'),

            // Stats (lecture seule, gérées automatiquement)
            NumberField::new('rating', 'Note moyenne')
                ->setNumDecimals(2)
                ->setHelp('Moyenne calculée à partir des notes des utilisateurs.')
                ->setFormTypeOption('disabled', true)
                ->hideOnIndex(),

            IntegerField::new('ratingsCount', 'Nombre de notes')
                ->setFormTypeOption('disabled', true)
                ->hideOnIndex(),

            IntegerField::new('views', 'Vues')
                ->setDisabled()
                ->onlyOnDetail(),

            // Modération
            BooleanField::new('isValidated', 'Validé'),
            TextareaField::new('rejectedReason', 'Raison du refus')
                ->hideOnIndex()
                ->setHelp('À remplir uniquement en cas de refus.'),
        ];
    }

    // Quand on supprime un reportage, on supprime aussi son image du dossier uploads
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Content && $entityInstance->getImage()) {
            $path = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $entityInstance->getImage();

            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
