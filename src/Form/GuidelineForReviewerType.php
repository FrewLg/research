<?php

namespace App\Form;

use App\Entity\GuidelineForReviewer;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuidelineForReviewerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('the_guidelline' ,   CKEditorType::class,[
                'attr'=>['placeholder'=>'Executive Summary',
                'class' => 'form-control col col-md-12 col-sm-12 col-lg-9  ',
                             'required' => false,
            
            ],]) 
            
            ->add('attachment', FileType::class, [
                'label' => 'Guideline attachment  file',
 
                'mapped' => false, 
                'required' => false,
               
            ])

            ->add('evaluationfrom', FileType::class, [
                'label' => 'Evaluation from  file',
 
                'mapped' => false, 
                'required' => false,
               
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GuidelineForReviewer::class,
        ]);
    }
}
