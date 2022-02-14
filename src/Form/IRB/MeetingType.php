<?php

namespace App\Form\IRB;

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
                ]
            ])
            ->add('heldAt', DateTimeType::class, [
                "html5" => true,
                "widget" => "single_text",
              
            ])
            ->add('attendee', null, [
                "attr" => [

                    "class" => "select2"
                ],

              
                "attr" => [
                    "class" => "select2 col-3"
                ],
               
            ])
            ->add('applications', null, [
                "attr" => [

                    "class" => "select2"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meeting::class,
        ]);
    }
}
