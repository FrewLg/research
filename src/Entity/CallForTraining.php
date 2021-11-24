<?php

namespace App\Entity;

use App\Repository\CallForTrainingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CallForTrainingRepository::class)
 */
class CallForTraining
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;

    /**
     * @ORM\ManyToOne(targetEntity=College::class, inversedBy="callForTrainings")
     */
    private $college;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=TrainingParticipant::class, mappedBy="training")
     */
    private $trainingParticipants;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $document_attachment;

    /**
     * @ORM\ManyToOne(targetEntity=TrainingType::class, inversedBy="callForTrainings")
     */
    private $training_type;

    public function __construct()
    {
        $this->trainingParticipants = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }
    public function __toString(): string
    {
        return  $this->name;
    }
    public function getCollege(): ?College
    {
        return $this->college;
    }

    public function setCollege(?College $college): self
    {
        $this->college = $college;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime  $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|TrainingParticipant[]
     */
    public function getTrainingParticipants(): Collection
    {
        return $this->trainingParticipants;
    }

    public function addTrainingParticipant(TrainingParticipant $trainingParticipant): self
    {
        if (!$this->trainingParticipants->contains($trainingParticipant)) {
            $this->trainingParticipants[] = $trainingParticipant;
            $trainingParticipant->setTraining($this);
        }

        return $this;
    }

    public function removeTrainingParticipant(TrainingParticipant $trainingParticipant): self
    {
        if ($this->trainingParticipants->removeElement($trainingParticipant)) {
            // set the owning side to null (unless already changed)
            if ($trainingParticipant->getTraining() === $this) {
                $trainingParticipant->setTraining(null);
            }
        }

        return $this;
    }

    public function getDocumentAttachment(): ?string
    {
        return $this->document_attachment;
    }

    public function setDocumentAttachment(?string $document_attachment): self
    {
        $this->document_attachment = $document_attachment;

        return $this;
    }

    public function getTrainingType(): ?TrainingType
    {
        return $this->training_type;
    }

    public function setTrainingType(?TrainingType $training_type): self
    {
        $this->training_type = $training_type;

        return $this;
    }
}
