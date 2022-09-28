<?php

namespace App\Entity\CRP;

use App\Entity\User;
use App\Repository\CRP\CoInvestigatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CoInvestigatorRepository::class)
 */
class CoInvestigator
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
 

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="coInvestigator", cascade={"persist", "remove"})
     */
    private $memberName;

    /**
     * @ORM\ManyToMany(targetEntity=CollaborativeResearchProject::class, mappedBy="CoInvestigators")
     */
    private $collaborativeResearchProjects;

    public function __construct()
    {
        $this->collaborativeResearchProjects = new ArrayCollection();
    }

  

    public function getId(): ?int
    {
        return $this->id;
    }
     public function getMemberName(): ?User
    {
        return $this->memberName;
    }

    public function setMemberName(?User $memberName): self
    {
        $this->memberName = $memberName;

        return $this;
    }

    public function __toString(): string
    {
        return  $this->memberName;
    }
    
    /**
     * @return Collection<int, CollaborativeResearchProject>
     */
    public function getCollaborativeResearchProjects(): Collection
    {
        return $this->collaborativeResearchProjects;
    }

    public function addCollaborativeResearchProject(CollaborativeResearchProject $collaborativeResearchProject): self
    {
        if (!$this->collaborativeResearchProjects->contains($collaborativeResearchProject)) {
            $this->collaborativeResearchProjects[] = $collaborativeResearchProject;
            $collaborativeResearchProject->addCoInvestigator($this);
        }

        return $this;
    }

    public function removeCollaborativeResearchProject(CollaborativeResearchProject $collaborativeResearchProject): self
    {
        if ($this->collaborativeResearchProjects->removeElement($collaborativeResearchProject)) {
            $collaborativeResearchProject->removeCoInvestigator($this);
        }

        return $this;
    }
}
