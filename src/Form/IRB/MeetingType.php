<?php

namespace App\Form\IRB;

use App\Entity\IRB\Application;
use App\Entity\IRB\BoardMember;
use App\Entity\IRB\Meeting;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MeetingType extends AbstractType
{
    private $user;
    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', null, [
                "data" => "JU-" . rand(1000, 100000),
                "attr" => [

                    "readonly" => true
                ],
            
            ])
            ->add('heldAt', DateTimeType::class, [
                "html5" => true,
                "widget" => "single_text",
                // "min" => new \DateTime(),
            ])
            ->add('attendee', null, [
                "expanded"=>true,
               
               
              
            ])
            ->add('applications', null, [
                "expanded"=>true,
               "label"=>"",
                'choice_label' => function (Application $application) {
                    return "".$application . "==>" . $application->getSubmittedBy() . "";
                },
  
               
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meeting::class,
        ]);
    }
}
