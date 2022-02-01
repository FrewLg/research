<?php

namespace App\Entity;

use App\Repository\SubmissionFinalReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubmissionFinalReportRepository::class)
 */
class SubmissionFinalReport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

   

    /**
     * @ORM\Column(type="text")
     */
    private $fullReport;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $manuscript;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;

    /**
     * @ORM\OneToOne(targetEntity=Submission::class, inversedBy="submissionFinalReport", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $submission;

    public function getId(): ?int
    {
        return $this->id;
    }

   

    public function getFullReport(): ?string
    {
        return $this->fullReport;
    }

    public function setFullReport(string $fullReport): self
    {
        $this->fullReport = $fullReport;

        return $this;
    }

    public function getManuscript(): ?string
    {
        return $this->manuscript;
    }

    public function setManuscript(?string $manuscript): self
    {
        $this->manuscript = $manuscript;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    public function setSubmission(Submission $submission): self
    {
        $this->submission = $submission;

        return $this;
    }
}
