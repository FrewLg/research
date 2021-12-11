<?php

namespace App\Form;

use App\Entity\Submission;
use App\Entity\ThematicArea;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abstract')


            ->add('title')
            ->add('sent_at', DateTimeType::class, [
                "required" => false
            ])
            ->add('uidentifier')
            ->add('complete')
            ->add('submission_type')
            ->add('funding_organization')
            ->add('project_start_at')
            ->add('project_end_at')
            ->add('progress')
            ->add('published', ChoiceType::class, [
                "choices" => [
                    "Published" => 1,
                    "not Published" => 0,
                ]
            ])
            ->add('status')
            ->add('keywords')
            ->add('author')
            ->add('thematic_area', EntityType::class, [
                "class" => ThematicArea::class,
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => Submission::class,
        ]);
    }
}
