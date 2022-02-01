<?php

namespace App\Form\IRB;

use App\Entity\InstitutionalReviewersBoard;
use App\Entity\IRB\IRBReviewAssignment;
use App\Entity\User;
use App\Repository\InstitutionalReviewersBoardRepository;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

 use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
class IRBReviewAssignmentType extends AbstractType
{
    public function __construct(InstitutionalReviewersBoardRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reviewAssignment=$options['data'];
        if (!$reviewAssignment  instanceof IRBReviewAssignment ) {
           return;
        }
        
        $builder
        ->add('irbreviewer', 
          EntityType::class, [
            'class' => User::class,
            // 'mapped'=>false,
           
        ])

        // ->add('irbreviewer', EntityType::class, [
        //     'class' => \App\Entity\User::class,
        //     'choice_label' => function(\App\Entity\User $user) {
        //         return sprintf('(%d) %s', $user->getId(), $user->getUserInfo());
        //     },
        //     'placeholder' => 'Choose an author',
        //     'choices' => $this->userRepository->findAll(),
        // ])
      

         ->add('file_tobe_reviewed', FileType::class, [
            'label' => 'Upload proposal attachment',
            'mapped' => false,  'attr'=>[
                'class' => 'form-control  m-0   ',
                         'required' => true,
        
        ],
            'required' => true,
            ])
  
      ->add('invitationDueDate', DateType::class, array(
        'placeholder' => [
'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
'label' => 'Invitation response duedate',
     
'widget' => 'single_text',
      'format' => 'yyyy-MM-dd',
         'attr' => array(
            'min'=>(new DateTime('now'))->format('Y-m-d'),
 
   'required' => true,
'class'=>'form-control',
)              
  ))
  ->add('duedate', DateType::class, array(
    'placeholder' => [
'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
    'label' => 'Review duedate',
    'widget' => 'single_text',
  'format' => 'yyyy-MM-dd',
     'attr' => array(
'min'=>(new DateTime('now'))->format('Y-m-d'), 
 'required' => true,
'class'=>'form-control',
)              
))


   
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IRBReviewAssignment::class,
        ]);
    }
}


class ExternalIRBReviewAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reviewAssignment=$options['data'];
        if (!$reviewAssignment  instanceof IRBReviewAssignment ) {
           return;
        }
        $builder
     
->add('external_reviewer_name')
->add('middle_name')
->add('last_name')



->add('external_reviewer_email' ,
TextType::class, [
    'attr' => ['class' => 'form-control col col-md-12 col-sm-12 col-lg-9 '],
])



->add('file_tobe_reviewed', FileType::class, [
    'label' => 'Upload proposal attachment',
    'mapped' => false,
    'attr'=>[
        'class' => 'form-control   col-md-12 col-sm-12 col-lg-9  ',
                 'required' => true,
    
    ],
    'required' => true,
    ])
    
->add('invitationDueDate', DateType::class, array(
    'placeholder' => [
'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
'label' => 'Invitation response duedate',
 
'widget' => 'single_text',
  'format' => 'yyyy-MM-dd',
     'attr' => array(
        'min'=>(new DateTime('now'))->format('Y-m-d'),
'required' => true,
'class'=>'form-control',
)              
))

->add('duedate', DateType::class, array(
    'placeholder' => [
'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
    'label' => 'Review duedate',
    'widget' => 'single_text',
  'format' => 'yyyy-MM-dd',
     'attr' => array(
'min'=>(new DateTime('now'))->format('Y-m-d'), 
'max'=>(new DateTime('now'))->format('Y-m-d'),
'required' => true,
'class'=>'form-control',
)              
)) 

;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IRBReviewAssignment::class,
        ]);
    }
}