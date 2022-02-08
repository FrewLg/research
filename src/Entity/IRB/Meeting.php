<?php

namespace App\Entity\IRB;

use App\Entity\College;
use App\Entity\User;
use App\Repository\IRB\MeetingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MeetingRepository::class)
 */
class Meeting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $heldAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity=College::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $college;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="meetings")
     */
    private $attendee;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="meeting")
     */
    private $applications;

    public function __construct()
    {
        $this->attendee = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getHeldAt(): ?\DateTimeInterface
    {
        return $this->heldAt;
    }

    public function setHeldAt(\DateTimeInterface $heldAt): self
    {
        $this->heldAt = $heldAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
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

    /**
     * @return Collection|BoardMember[]
     */
    public function getAttendee(): Collection
    {
        return $this->attendee;
    }

    public function addAttendee(BoardMember $attendee): self
    {
        if (!$this->attendee->contains($attendee)) {
            $this->attendee[] = $attendee;
        }

        return $this;
    }

    public function removeAttendee(BoardMember $attendee): self
    {
        $this->attendee->removeElement($attendee);

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setMeeting($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getMeeting() === $this) {
                $application->setMeeting(null);
            }
        }

        return $this;
    }
}
