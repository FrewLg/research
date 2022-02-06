<?php

namespace App\Entity\IRB;

use App\Repository\IRB\IrbReviewAtachementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IrbReviewAtachementRepository::class)
 */
class IrbReviewAtachement
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
    private $attachement;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cteatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=App\Entity\College::class, inversedBy="irbReviewAtachements")
     */
    private $college;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttachement(): ?string
    {
        return $this->attachement;
    }

    public function setAttachement(?string $attachement): self
    {
        $this->attachement = $attachement;

        return $this;
    }

    public function getCteatedAt(): ?\DateTimeInterface
    {
        return $this->cteatedAt;
    }

    public function setCteatedAt(?\DateTimeInterface $cteatedAt): self
    {
        $this->cteatedAt = $cteatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
}
