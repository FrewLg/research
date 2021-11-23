<?php

namespace App\Form;

use App\Entity\CallForTraining;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class CallForTrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description' , CKEditorType::class, [
                'attr' => ['placeholder' => 'Description and  details',
                    'class' => 'form-control  col col-md-12 col-sm-12 col-lg-9  ',
                    'required' => false,

                ]])
            ->add('deadline' , DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
         ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CallForTraining::class,
        ]);
    }
}
