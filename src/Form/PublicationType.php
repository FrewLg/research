<?php

namespace App\Form;

use App\Entity\Publication;
use DateTime;
 use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('doi' , TextType::class,[ 'label'=>"DOI:",
            'attr'=>['label'=>"DOI"]])
            ->add('journal_name')
            ->add('impact_factor')
            ->add('citation_score')
            ->add('member_role')

            ->add('published_at'
            , DateType::class, array(
                'placeholder' => [
        'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
        'label' => 'published at',
             
        'widget' => 'single_text',
              'format' => 'yyyy-MM-dd',
                 'attr' => array(
          
           'required' => true, 
        'class'=>'form-control',
        )              
          ))
          
            ->add('article_document' , FileType::class, [
                'label' => 'Upload article document ',
                'mapped' => false,
                'required' => false,
                "attr"=>[
                    "accept"=>"image/*",
                    "class"=>"form-control",

                ]
            ])
            // ->add('author')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
