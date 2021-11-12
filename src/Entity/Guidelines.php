<?php

namespace App\Entity;

use App\Repository\GuidelinesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GuidelinesRepository::class)
 */
class Guidelines
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

 

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $guideline;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attachment;

    /**
     * @ORM\OneToOne(targetEntity=Guidelines::class, cascade={"persist", "remove"})
     */
    private $college;

  

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getGuideline(): ?string
    {
        return $this->guideline;
    }

    public function setGuideline(?string $guideline): self
    {
        $this->guideline = $guideline;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getCollege(): ?self
    {
        return $this->college;
    }

    public function setCollege(?self $college): self
    {
        $this->college = $college;

        return $this;
    }

   
   
}
