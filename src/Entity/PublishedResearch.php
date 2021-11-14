<?php

namespace App\Entity;

use App\Repository\PublishedResearchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Form;

/**
 * @ORM\Entity(repositoryClass=PublishedResearchRepository::class)
 */
class  PublishedResearch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
 
//    /**
//      * @ORM\Column(type="string", length=255, nullable=true)
//      */
//     private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $allotted_budget;
 
//   /**
//      * @ORM\Column(type="datetime", nullable=true)
//      */
//     private $year;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $final_report;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $successfully_completed;

    /**
     * @ORM\OneToOne(targetEntity=Submission::class, inversedBy="publishedResearch", cascade={"persist", "remove"})
     */
    private $submission; 

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $irb_clearance;

    /**
     * @ORM\ManyToOne(targetEntity=UserInfo::class, inversedBy="researches"  , cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * 
     */
    private $userInfo;

    /**
     * @ORM\ManyToOne(targetEntity=PublishedTopic::class, inversedBy="title")
     * @ORM\JoinColumn(nullable=false)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=AcademicYear::class, inversedBy="publishedResearch")
     */
    private $year;

    // /**
    //  * @ORM\ManyToOne(targetEntity=PublishedTopic::class, inversedBy="publishedResearch" , cascade={"persist", "remove"}  )
    //  */
    // private $title;

    // /**
    //  * @ORM\ManyToOne(targetEntity=PublishedTopic::class, cascade={"persist", "remove"} , orphanRemoval=true)
    //  * @ORM\JoinColumn(nullable=true)
    //  */
    // private $title;
 

    public function getId(): ?int
    {
        return $this->id;
    }
 

    // public function getYear(): ?\DateTimeInterface
    // {
    //     return $this->year;
    // }

    // public function setYear(?\DateTimeInterface $year): self
    // {
    //     $this->year = $year;

    //     return $this;
    // }

    public function getFinalReport(): ?string
    {
        return $this->final_report;
    }

    public function setFinalReport(?string $final_report): self
    {
        $this->final_report = $final_report;

        return $this;
    }
    public function getSuccessfullyCompleted(): ?bool
    {
        return $this->successfully_completed;
    }

    public function setSuccessfullyCompleted(?bool $successfully_completed): self
    {
        $this->successfully_completed = $successfully_completed;

        return $this;
    }

   



    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end ;
    }

    public function setDateEnd(?\DateTimeInterface $date_end ): self
    {
        $this->date_end  = $date_end ;

        return $this;
    }
 
    public function getAllottedBudget(): ?string
    {
        return $this->allotted_budget;
    }

    public function setAllottedBudget(string $allotted_budget): self
    {
        $this->allotted_budget = $allotted_budget;

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

    public function setSubmission(?Submission $submission): self
    {
        $this->submission = $submission;

        return $this;
    }
 
    public function getIrbClearance(): ?string
    {
        return $this->irb_clearance;
    }

    public function setIrbClearance(?string $irb_clearance): self
    {
        $this->irb_clearance = $irb_clearance;

        return $this;
    }
//     public function saveIrbClearance(Form $form)
// {
//     $userInfo = $this->getUser->getUserInfo();
//     $Emailpicture=$userInfo->getFirstName();
//         $prifilepicture = $form->get('image')->getData();
//         $fileName3 =  md5($Emailpicture) . '.' . $prifilepicture->guessExtension();
//         $prifilepicture->move($this->getParameter('profile_pictures'), $fileName3);
        
//         $userInfo->setIrbClearance($fileName3);
//         return $this;
    
// }

    public function getUserInfo(): ?UserInfo
    {
        return $this->userInfo;
    }

    public function setUserInfo(?UserInfo $userInfo): self
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    // public function getTitle(): ?PublishedTopic
    // {
    //     return $this->title;
    // }

    // public function setTitle(PublishedTopic $title): self
    // {
    //     $this->title = $title;

    //     return $this;
    // } 

    public function getTitle(): ?PublishedTopic
    {
        return $this->title;
    }

    public function setTitle(?PublishedTopic $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getYear(): ?AcademicYear
    {
        return $this->year;
    }

    public function setYear(?AcademicYear $year): self
    {
        $this->year = $year;

        return $this;
    }

    
}
