<?php

namespace App\Controller;

use App\Entity\EditorialDecision;
use App\Entity\ReviewAssignment;
use App\Form\ReviewAssignmentType;
use App\Repository\ReviewAssignmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Review;
use App\Entity\GuidelineForReviewer;
use App\Form\GuidelineForReviewerType;
use App\Repository\GuidelineForReviewerRepository;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use App\Form\ReviewType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\CoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Submission; 
use App\Repository\SubmissionRepository;
use App\Repository\InstitutionalReviewersBoardRepository;
use App\Repository\ReviewRepository;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Entity\InstitutionalReviewersBoard;
use App\Helper\ReviewHelper;
use App\Repository\EvaluationFormRepository;
use DateTime;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
// use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/irb-review")
 */
class IRBReviewController extends AbstractController
{
    use CsrfCheckerTrait;
  

    /**
     * @Route("/myassigned", name="myassigned", methods={"GET"})
     */
    public function myassigned(Request $request, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        $this_is_me = $this->getUser();
        $myassigned = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $this_is_me, 'closed' => NULL , 'Declined' => NULL ]));
        ////// if no throw exception
        $myassigneds = $paginator->paginate(
            // Doctrine Query, not results
            $myassigned,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
#################################################

#################################################

        return $this->render('submission/myassigned.html.twig', [
            'submissions' => $myassigneds,
            'myreviews' => $myassigneds,
        ]);
    }

    

    /**
     * @Route("/{id}/revise", name="reviewsubmission", methods={"GET","POST"})
     */
    public function revise(Request $request, ReviewAssignment $reviewAssignment, EvaluationFormRepository $evaluationFormRepository): Response {
        ////Ultimate reviewers page
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        $submissionOfreviewer = $entityManager->getRepository(ReviewAssignment::class)->find($reviewAssignment);
        $metoo = $this->getUser();
        $me_as_a_reviewer = $submissionOfreviewer->getReviewer()->getId();
        $submissions = $submissionOfreviewer->getSubmission();
        $editorialDecisions = $entityManager->getRepository(EditorialDecision::class)->find($submissions);
        #dd($me_as_a_reviewer.$me);
        $iamareviewers = $entityManager->getRepository(ReviewAssignment::class)->findBy(['submission' => $submissions, 'reviewer' => $metoo]);

        // $myassigned =  $entityManager->getRepository(ReviewAssignment::class)->findBy($reviewAssignment);
        #######################
        if ($reviewAssignment->getClosed() == 1) {
            return $this->redirectToRoute('myassigned');

        }
        #######################
        foreach ($submissionOfreviewer as $muke) {
            $dd = $muke->getReviewer()->getId();
            echo $dd; #=  $muke->getReviewer()->getId();

            $lala = $dd . 'compare' . $me;
            /////////
            $me = $this->getUser()->getId();
            $thereviewerone = $reviewAssignment->getReviewer()->getId();
            if ($dd == $me) {

                return $this->redirectToRoute('myreviews');
                $this->addFlash(
                    'danger',
                    'Sorry you' . $dd . '//' . $me . ' never been assigned to this submision!'

                );

            }

            /////
        }
        $measareviewer = $this->getUser();
        $author = $submissions->getAuthor();
        //   $reviews=$entityManager->getRepository(Review::class)->findBy(['submission' => $submissions ] );

        if ($measareviewer == $author) {
            ////if you are the author then you can't review it///////
            $this->addFlash(
                'warining',
                'You can not see the submission you made in this page!'
            );
            return $this->redirectToRoute('myreviews');
        }
        $review = new Review();
        $review->setReviewAssignment($reviewAssignment);
        $review->setSubmission($reviewAssignment->getSubmission());
        $review->setReviewedBy($measareviewer);

//////allow reviewer if he is only assigned to this submission
        $form = $this->createFormBuilder($review)
            ->add('remark', ChoiceType::class, [
                'placeholder' => 'Select Editorial decision',
                'choices' => [
                    'Declined' => 'Declined',
                    'Accepted with major revision' => 'Accepted with major revision',
                    'Accepted with minor revision' => 'Accepted with minor revision',
                    'Accepted' => 'Accepted',

                ],
                'attr' => [
                    'class' => 'form-control',
                    'required' => true,
                ],
            ])
            ->add('comment', CKEditorType::class, [
                'attr' => ['placeholder' => 'Describe your reason why',

                    'class' => 'form-control',

                    'required' => false,

                ]])
            ->add('attachment', FileType::class, [
                'label' => 'Review document  file',
                'mapped' => false,
                'required' => false,
                'attr' => [
// 'placeholder'=>'Describe your reason why',

                    'class' => 'form-control',

                    'required' => false,

                ],
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reviewfile = $form->get('attachment')->getData();
            if ($reviewfile == "") {
                $review->setAttachment('');
            } else {
                $reviewfile = $form->get('attachment')->getData();
                $Areviewfile = md5(uniqid()) . '.' . $reviewfile->guessExtension();
                $reviewfile->move($this->getParameter('review_files'), $Areviewfile);
                $review->setAttachment($Areviewfile);
            }
            $review->setCreatedAt(new \DateTime());
            $review->setReviewedBy($this->getUser());
            $reviewAssignment->setClosed(1);

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('reviewsubmission', array('id' => $reviewAssignment->getId()));
        }

        $editorialDecision = new EditorialDecision();
        $editorialDecisionform = $this->createFormBuilder($editorialDecision)
        // ->add('decision', ChoiceType::class, [
        //     'placeholder' => 'Select remark',
        //     'choices' => [

        //         'Declined' => 'Declined',
        //         'Accepted with major revision' => 'Accepted with major revision',
        //         'Accepted with minor revision' => 'Accepted with minor revision',
        //         'Accepted' => 'Accepted',

        //     ],
        //     'attr' => [
        //         'class' => 'form-control',
        //         'required' => true,
        //     ],
        // ])
            ->add('feedback', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Feedback  for the author',
                    'required' => true,
                    'class' => 'form-control',
                )))
            ->getForm();
        $editorialDecisionform->handleRequest($request);
        if ($editorialDecisionform->isSubmitted() && $editorialDecisionform->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $editorialDecision->setSubmission($submissions);
            $editorialDecision->setRevisedAt(new \DateTime());
            // $editorialDecision->setCreatedAt(new \DateTime());

            $editorialDecision->setEditedBy($this->getUser());
            $entityManager->persist($editorialDecision);
            $entityManager->flush();

            return $this->redirectToRoute('reviewsubmission', array('id' => $reviewAssignment->getId()));
        }
        $reviews = $entityManager->getRepository(Review::class)->findBy(['submission' => $reviewAssignment->getSubmission(), 'reviewed_by' => $measareviewer]);

        return $this->render('submission/review_byreviewer.html.twig', [
            'review_assignment' => $reviewAssignment,
            'review_assignments' => $reviews,
            'submission' => $submissions,
            'editorialDecisions' => $editorialDecisions,
            'editorialDecisionform' => $editorialDecisionform->createView(),
            'form' => $form->createView(),
            'evaluationForms' => $evaluationFormRepository->findBy(['parent' => null]),
        ]);
    }


      
  /**
     * @Route("/{id}/decline/", name="decline_invitation", methods={"GET","POST"})
     */
    public function declineinvitation(Request $request, ReviewAssignment $reviewAssignment): Response
    {
	$entityManager = $this->getDoctrine()->getManager();
    $mew= $this->getUser()->getId();
	$deadline= $reviewAssignment->getDuedate();
	$today= new \DateTime();
	$message='';
 	if ($deadline<=$today){
 	$message="Overdue!";
#	echo $day;
	}
	////if he is not the one he has been assigned to this proposal then redirect the page to the list of the submission he hasd been assigned to
	$reviewAssignment->setDeclined(1);
	$this->getDoctrine()->getManager()->flush();
	$this->addFlash( 
            'danger',
            'You declined your review invitation. The process will not be undone!'
        ); 
	return $this->redirectToRoute('myreviews');
}
    /**
     * @Route("/{id}", name="unassign", methods={"DELETE", "GET","POST"})
     */
    public function unassign(Request $request, ReviewAssignment $reviewAssignment  ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reviewAssignment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->remove($reviewAssignment);
            $reviewAssignment->setInactiveAssignment(1);
              $this->addFlash(
            'info',
            'Reviewer unassigned!'
        ); 
            $entityManager->flush();
        }
            return $this->redirectToRoute('review_assignment_new', array('id'=>$reviewAssignment->getSubmission()->getId()));
 
    }

  

  /**
     * @Route("/{id}/accept/", name="accept_invitation", methods={"GET","POST"})
     */
    public function acceptinvitation(Request $request, ReviewAssignment $reviewAssignment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

    $entityManager = $this->getDoctrine()->getManager();
    if($this->getUser() != $reviewAssignment->getReviewer()){
        $flashbag = $this->get('session')->getFlashBag();
        $flashbag->add("danger", "Sorry you are not allowed for this service !" );
        return $this->redirectToRoute('myassigned');
    }
   
    if(  $reviewAssignment->getDeclined()==1){
       
        $flashbag = $this->get('session')->getFlashBag();
        $flashbag->add("danger", "Sorry invitation has declined !" );

        return $this->redirectToRoute('myassigned');
    }
    
    if ($reviewAssignment->getIsAccepted()){
        return $this->redirectToRoute('reviewsubmission', array('id' => $reviewAssignment->getId()));
}
    if ($reviewAssignment->getIsRejected()){
        // echo"'dsada'";
        // dd();
        return $this->redirectToRoute('myassigned');
}
        $submission=$reviewAssignment->getSubmission();
                 $workunit=$reviewAssignment->getSubmission();
	 $guideline_for_reviewers = $entityManager->getRepository(GuidelineForReviewer::class)->findAll()[0];
	$Allsubmission = $entityManager->getRepository(Submission::class)->findBy(['id' => $submission ] );
	$deadline= $reviewAssignment->getDuedate();
	$today= new \DateTime();
	$message='';
 	if ($deadline<=$today){
        $flashbag = $this->get('session')->getFlashBag();
        $flashbag->add("danger", "Sorry Invitation overdue !" );

        //  $this->addFlash('error',"!!");
        return $this->redirectToRoute('myassigned');
}
 
        if ($request->request->get('accept-invitation')) {
            $this->checkCsrf('accept-invitation');
            $reviewAssignment->setAcceptedAt(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('reviewsubmission', array('id' =>$reviewAssignment->getId()));
        }
 
	return $this->render('review_assignment/accept_invitation.html.twig', [
	'review_assignment' => $reviewAssignment,
	'guideline' => $guideline_for_reviewers,
        ]);
    } 

  

}
 
 
