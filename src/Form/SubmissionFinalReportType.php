<?php

namespace App\Form;

use App\Entity\SubmissionFinalReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionFinalReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullReport',FileType::class,[
                "label"=>"Full Report",
                "help"=>"Upload Full Report",
                "mapped"=>false,
                "attr"=>[
                    "accept"=>"application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint,
                    text/plain, application/pdf",
                    "class"=>"form-control",
                ]
                
            ])
            ->add('manuscript',FileType::class,[
                "label"=>"Financial clearance",
                "help"=>"Upload Financial clearance",
                "mapped"=>false,
                "attr"=>[
                    "accept"=>"application/pdf",
                    "class"=>"form-control",
                ]
                
            ])->add("remark")
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubmissionFinalReport::class,
        ]);
    }
}
