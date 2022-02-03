<?php

namespace App\Entity\IRB;

use App\Repository\IRB\IRBReviewAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;  

/**
 * @ORM\Entity(repositoryClass=IRBReviewAssignmentRepository::class)
 */
class IRBReviewAssignment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Application::class, inversedBy="iRBReviewAssignments")
     */
    private $application;

    /**
     * @ORM\ManyToOne(targetEntity=App\Entity\User::class, inversedBy="iRBReviewAssignments")
     */
    private $irbreviewer; 
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $duedate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invitation_sent_at;
    // ^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@\ju.edu.et

    /**
     * @ORM\Column(type="string", length=255, nullable=true)     
     */
    private $external_irbreviewer_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $external_irbreviewer_name;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $middle_name;


     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $last_name;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Declined;




    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reassigned;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file_tobe_reviewed;



    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $acceptedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $rejectedAt;

    /**
     * @ORM\OneToMany(targetEntity=IRBReview::class, mappedBy="iRBReviewAssignment", orphanRemoval=true)
     */
    private $irbreviews;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $closed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inactive_assignment;

    public function __construct()
    {
        $this->irbreviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getIrbreviewer() 
    {
        return $this->irbreviewer;
    }

    public function setIrbreviewer(?\App\Entity\User $irbreviewer): self
    {
        $this->irbreviewer = $irbreviewer;

        return $this;
    }

    public function getDuedate(): ?\DateTimeInterface
    {
        return $this->duedate;
    }

    public function setDuedate(?\DateTimeInterface $duedate): self
    {
        $this->duedate = $duedate;

        return $this;
    }

    public function getInvitationSentAt(): ?\DateTimeInterface
    {
        return $this->invitation_sent_at;
    }

    public function setInvitationSentAt(?\DateTimeInterface $invitation_sent_at): self
    {
        $this->invitation_sent_at = $invitation_sent_at;

        return $this;
    }
    
    public function getDeclined(): ?string
    {
        return $this->Declined;
    }
    public function setDeclined(?string $Declined): self
    {
        $this->Declined = $Declined;

        return $this;
    }


    public function getReassigned(): ?string
    {
        return $this->reassigned;
    }
    public function setReassigned(?string $reassigned): self
    {
        $this->reassigned = $reassigned;

        return $this;
    }


    
    public function getFileTobeReviewedeclined(): ?string
    {
        return $this->file_tobe_reviewed;
    }
    public function setFileTobeReviewed(?string $file_tobe_reviewed): self
    {
        $this->file_tobe_reviewed = $file_tobe_reviewed;

        return $this;
    }

    

    public function getExternalirbrevieweremail(): ?string
    {
        return $this->external_irbreviewer_email;
    }
    public function setExternalirbrevieweremail(?string $external_irbreviewer_email): self
    {
        $this->external_irbreviewer_email = $external_irbreviewer_email;

        return $this;
    }

 
    
    public function getExternalirbreviewerName(): ?string
    {
        return $this->external_irbreviewer_name;
    }
    
    public function setExternalirbreviewerName(?string $external_irbreviewer_name): self
    {
        $this->external_irbreviewer_name = $external_irbreviewer_name;

        return $this;
    }

    
    
    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }
    
    public function setMiddleName(?string $middle_name): self
    {
        $this->middle_name = $middle_name;

        return $this;
    }
    


    
    public function getLastName(): ?string
    {
        return $this->last_name;
    }
    
    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }
    

   

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeInterface
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeInterface $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getRejectedAt(): ?\DateTimeInterface
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeInterface $rejectedAt): self
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }
    public function getIsAccepted()
    {
        return $this->acceptedAt != null;
    }
    public function getIsRejected()
    {
        return $this->rejectedAt != null;
    }

    /**
     * @return Collection|Review[]
     */
    public function getIrbreviews(): Collection
    {
        return $this->irbreviews;
    }

    public function addReview(\App\Entity\IRB\IRBReview $review): self
    {
        if (!$this->irbreviews->contains($review)) {
            $this->irbreviews[] = $review;
            $review->setIRBReviewAssignment($this);
        }

        return $this;
    }

    public function removeReview(\App\Entity\IRB\IRBReview $review): self
    {
        if ($this->irbreviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getIRBReviewAssignment() === $this) {
                $review->setIRBReviewAssignment(null);
            }
        }

        return $this;
    }

    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(?bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getInactiveAssignment(): ?bool
    {
        return $this->inactive_assignment;
    }

    public function setInactiveAssignment(?bool $inactive_assignment): self
    {
        $this->inactive_assignment = $inactive_assignment;

        return $this;
    }
    
}
