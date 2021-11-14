<?php

namespace App\Form;

use App\Entity\Submission;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Count;

class SubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           
            ->add('title',TextType::class,['attr'=>[]])
            ->add('step',HiddenType::class)
            ->add('sub_title')
            // ->add('abstract',
            // CKEditorType::class
            // )
            ->add('abstract' ) 
            ->add('background_and_rationale' ) 
            ->add('methodology'  ) 
            ->add('research_outcome' ) 

            ->add('reference' ,   CKEditorType::class,[
                'attr'=>['placeholder'=>'References',
                'class' => 'form-control col col-md-12 col-sm-12 col-lg-9  ',
                             'required' => false,
            
            ],]) 
              
->add('budget_and_time_schedule' ,   CKEditorType::class,[
    'attr'=>['placeholder'=>'Budget and time schedule',
    'class' => 'form-control col col-md-12 col-sm-12 col-lg-9  ',
                 'required' => false,

],]) 
->add('GeneralObjective')
          
->add('specificObjectives', CollectionType::class, [
    'entry_type' => SpecificObjectiveType::class,
    'entry_options' => ['label' => false],
    'allow_add' => true,
    'by_reference' => false,
    
    'allow_delete' => true,
])


->add('researchTimeTables',CollectionType::class,[
    'entry_type' => ResearchTimeTableType::class,
    'entry_options' => ['label' => false],
    'allow_add' => true,
    'by_reference' => false,
    'allow_delete' => true,
    'required' => false,

    // 'constraints' => [
    //     new Count([
    //       'min' => 1,
    //       'minMessage' => 'Research Time Tables Must have at least one value',
    //       // also has max and maxMessage just like the Length constraint
    //     ]),
    //   ],
])

            ->add('thematic_area')
            ->add('keywords',null,["attr"=>["data-role"=>"tagsinput"]])
            ->add('agree_to_the_terms',
            ChoiceType::class, [
                "label"=>"I have read guidelines and agree",
                "choices" =>  ["I have read guidelines and agree"=>"1"],
             'mapped' => false, "multiple" => true, 'expanded'=>true,
               ])


               
            
            // null,["label"=>"I agree with the Terms and Conditions."])
            ->add('submissionBudgets',CollectionType::class,[
                'entry_type' => SubmissionBudgetType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'required' => false,
                'allow_delete' => true,
                // 'constraints' => [
                //     new Count([
                //       'min' => 0,
                //       'minMessage' => 'You have to add some  value to budget field',
                //       // also has max and maxMessage just like the Length constraint
                //     ]),
                //   ],
            ])
            ->add('submissionAttachements',CollectionType::class,[
                'entry_type' => SubmissionAttachementType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'error_bubbling'=>false,
                'allow_delete' => true,
                'required'=>false
            ])
            ->add('coAuthors', CollectionType::class, [
            'entry_type' => CoAuthorType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'by_reference' => false,
            'allow_delete' => true,
        ])
        
      #  ->add('save', SubmitType::class,[
       # 'attr'=>['class'=>'btn btn-success']
       # ])
        ;
        
          
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Submission::class,
        ]);
    }
}
