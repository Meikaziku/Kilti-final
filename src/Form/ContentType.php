<?php

namespace App\Form;

use App\Entity\Content;
use App\Entity\ContentCategory;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('url')
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => ContentCategory::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une catégorie',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
