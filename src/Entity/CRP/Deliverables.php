<?php

namespace App\Entity\CRP;

use App\Repository\CRP\DeliverablesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliverablesRepository::class)
 */
class Deliverables
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=CollaborativeResearchProject::class, cascade={"persist", "remove"}, inversedBy="deliverables")
     * @ORM\JoinColumn(nullable=false)
     */
    private $collaborativeResearchProject;

    // /**
    //  * @ORM\ManyToOne(targetEntity=CollaborativeResearchProject::class,   cascade={"persist", "remove"}, inversedBy="deliverables")
    //  * @ORM\JoinColumn(nullable=false)
    //  */
    // private $project;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return  $this->name;
    }
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
 
    public function getCollaborativeResearchProject(): ?CollaborativeResearchProject
    {
        return $this->collaborativeResearchProject;
    }

    public function setCollaborativeResearchProject(?CollaborativeResearchProject $collaborativeResearchProject): self
    {
        $this->collaborativeResearchProject = $collaborativeResearchProject;

        return $this;
    }
}
