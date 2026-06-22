<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\ContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/publish', name: 'app_profile_publish')]
    public function publish(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $content = new Content();
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content->setUser($this->getUser());
            $content->setStatus('pending');
            $content->setIsValidated(false);
            $content->setCreatedAt(new \DateTimeImmutable());

            // Gestion de l'image uploadée
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Nom unique pour éviter d'écraser un fichier existant
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );

                $content->setImage($newFilename);
            }

            $entityManager->persist($content);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande a été prise en compte. Elle sera visible après validation par un modérateur.');

            return $this->redirectToRoute('app_profile_publish');
        }

        return $this->render('profile/publish.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
