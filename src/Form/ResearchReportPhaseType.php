<?php

namespace App\Form;

use App\Entity\ResearchReportPhase;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResearchReportPhaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numberOfPhases',ChoiceType::class,[

                "choices"=>array_combine(range(1,10),range(1,10)),
                "help"=>"Total number of phases",
                "attr"=>[
                    "min"=>1,
                ]
            ])
            ->add('maximumDuration',IntegerType::class,[
                "help"=>"maximum duration of each phase  in days",
                "label"=>"Maximum duration(days)",
                "attr"=>[
                    "min"=>1,
                ]
            ])
            ->add('startDate',DateTimeType::class,[
                 "date_label"=>"Starts on",
                "widget"=>"single_text",
                "input_format"=>"Y-m-d H:i",
                "placeholder"=>
                    "Select Start date"
                    ,
               "attr"=>[
                    "min"=>(new \DateTime())->format("Y-m-d H:i:s"),
                ]
            ])
            ->add('endDate',DateTimeType::class,[
                "date_label"=>"Starts on",
               "widget"=>"single_text",
               "input_format"=>"Y-m-d H:i",
               "placeholder"=>
                   "Select Start date"
                   ,
               "attr"=>[
                   "min"=>(new \DateTime())->format("Y-m-d H:i:s"),
               ]
           ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResearchReportPhase::class,
        ]);
    }
}
