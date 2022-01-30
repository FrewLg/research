<?php

namespace App\Entity\IRB;

use App\Repository\AmendmentAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AmendmentAttachmentRepository::class)
 */
class AmendmentAttachment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Amendment::class, inversedBy="amendmentAttachments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $amendment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmendment(): ?Amendment
    {
        return $this->amendment;
    }

    public function setAmendment(?Amendment $amendment): self
    {
        $this->amendment = $amendment;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
