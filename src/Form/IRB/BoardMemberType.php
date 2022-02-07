<?php

namespace App\Form\IRB;

use App\Entity\IRB\BoardMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('user', null, [
                "placeholder" => "Select User",
                "attr" => [
                    "class" => "select2"
                ]
            ])
            ->add('college', null, [
                "placeholder" => "Select User",
                "attr" => [
                    "class" => "select2"
                ]
            ])
            ->add('role', ChoiceType::class, [
                "placeholder" => "Select Role",
              
                "required" => true,
                "choices" => [
                    "Member" => BoardMember::ROLE_MEMBER,
                    "Chair" => BoardMember::ROLE_CHAIR,
                    "Secretary" => BoardMember::ROLE_SECRETARY,
                ],
                "attr" => [
                    "class" => "select2"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BoardMember::class,
        ]);
    }
}
