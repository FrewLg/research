<?php

namespace App\Form\IRB;

use App\Entity\IRB\ApplicationFeedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationFeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('application')
            ->add('description')
            // ->add('createdAt')
            // ->add('feedbackFrom')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationFeedback::class,
        ]);
    }
}
