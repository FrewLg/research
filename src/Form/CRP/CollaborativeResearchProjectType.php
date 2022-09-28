<?php

namespace App\Form\CRP;

use App\Entity\CRP\CollaborativeResearchProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollaborativeResearchProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('shortDescription')
            ->add('Deliverables')
            ->add('YearOfCemmencement' ,null,['widget' => 'single_text',])
            ->add('EndDate' ,null,['widget' => 'single_text',])
            ->add('FundingOpportunityName')
            ->add('AmountOfGrant')
            ->add('Currency')
            ->add('ThematicArea')
            ->add('OtherInsitutes')
            ->add('ResponsiblePrimaryInstitute')
            ->add('PrincipalInvestigator')
            ->add('ProjectType')
            ->add('ProjectStatus')
            ->add('CoPrincipalInvestigator')
            ->add('fundingOrganization')
            ->add('CoInvestigators')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollaborativeResearchProject::class,
        ]);
    }
}
