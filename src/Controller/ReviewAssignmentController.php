<?php

namespace App\Controller;

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
use DateTime;
use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/assignment")
 */
class ReviewAssignmentController extends AbstractController
{
    use CsrfCheckerTrait;
  
    /**
     * @Route("/{id}/assign", name="review_assignment_new", methods={"GET","POST"})
     */
    public function assign(Request $request, Submission $submission ,ReviewHelper $reviewHelper, InstitutionalReviewersBoardRepository $institutionalReviewersBoardRepository, ReviewAssignmentRepository $reviewAssignmentRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();  
        if($request->request->get('assign-selected')){
            $this->checkCsrf('assign-selected');
            $count=0;
            $all=0;
            foreach ($submission->getReviewAssignments() as $key => $reviewAssignment) {
              if($reviewAssignment->getStatus()== 1){
                $reviewAssignment->setStatus(2);
                $reviewHelper->sendReviewInvitation($reviewAssignment);
            $count++;
              }
            }
            if($count!=0){
                $entityManager->flush();
                
                $this->addFlash('success',"$count Reviewer(s) invited successfully");
               
            }else $this->addFlash('danger',"Invalid request!");
            return $this->redirectToRoute('review_assignment_new',['id'=>$submission->getId()]);
        }
            $user= $this->getUser();
	
    $the_submission_author=$submission->getAuthor();
 	if($the_submission_author==$user){
      $this->addFlash(
            'danger',
            'Sorry! You can not assign by yourself a reviewer to the submission you made!'
        ); 
	return $this->redirectToRoute('submission_index');
 }   
 ///// check if the submission is completed or not
 $confirmed=$entityManager->getRepository(Submission::class)->find($submission);
        $is_submission_confirmed=$confirmed->getComplete();
    //      if($is_submission_confirmed=='completed'){
    // $this->addFlash(
    //         'danger',
    //         'The submission is not complete. Hence review assignment will never be performced!'
    //   ); 
    //      return $this->redirectToRoute('submission_index');
    //        }
 ///// check if the submission is completed or not 
   	$allreviewersfrom_i_r_b =  array_reverse($reviewAssignmentRepository->findBy(['submission' => $submission] ));
        $reviewAssignment = new ReviewAssignment();
        $reviewAssignment->setStatus(1);
        $reviewAssignment->setSubmission($submission);
 
        $form = $this->createForm(ReviewAssignmentType::class, $reviewAssignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submission->setStatus(2);
            $entityManager = $this->getDoctrine()->getManager();
            
            ///deny if the user is the author
         $theassigned_reviewer=$reviewAssignment->getReviewer();
 if($theassigned_reviewer==$user){
      $this->addFlash(
            'warning',
            'You can not assign yourself as a reviewer to   this submission. Assign others instead!'
        ); 
	return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));
 }
 
  if($theassigned_reviewer==$the_submission_author){
      $this->addFlash(
            'danger',
            'You can not assign the author himself as a reviewer to   the submission. Assign others instead!'
        ); 
	return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));
 }
            $reviewAssignment->setSubmission($submission);
	$duedate=$reviewAssignment->getDuedate();
            $reviewAssignment->setInvitationSentAt(new \DateTime());
      $this->addFlash(
            'success',
            'Research reviewer assigned successfully!'
        ); 
            $entityManager->persist($reviewAssignment);
            $entityManager->flush();
            return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));
        }

        ////////////////External reviewer
         $externalreviewerform = $this->createFormBuilder($reviewAssignment)
            ->add('external_reviewer_name')
            
                ->add('external_reviewer_email' ,
                TextType::class, [
                    'attr' => ['class' => 'form-control col col-md-12 col-sm-12 col-lg-9 '],
                ])
              
                ->add('invitationDueDate', DateType::class, array(
                    'placeholder' => [
          'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
          'label' => 'Invitation response duedate',
                 
          'widget' => 'single_text',
                  'format' => 'yyyy-MM-dd',
                     'attr' => array(
                        'min'=>(new DateTime('now'))->format('Y-m-d'),
               'required' => true,
        'class'=>'form-control',
           )              
              ))

                ->add('duedate', DateType::class, array(
                    'placeholder' => [
          'year' => 'Year', 'month' => 'Month', 'day' => 'Day', ],
                    'label' => 'Review duedate',
                    'widget' => 'single_text',
                  'format' => 'yyyy-MM-dd',
                     'attr' => array(
        'min'=>(new DateTime('now'))->format('Y-m-d'), 
        'max'=>$reviewAssignment->getSubmission()->getCallForProposal()->getReviewProcessEnd()->format('Y-m-d'),
               'required' => true,
        'class'=>'form-control',
           )              
              ))
             

                ->getForm();
            $externalreviewerform->handleRequest($request);
            if ($externalreviewerform->isSubmitted() && $externalreviewerform->isValid()) {
         
                $reviewAssignment->setSubmission($submission);
                $duedate=$reviewAssignment->getDuedate();
                        $reviewAssignment->setInvitationSentAt(new \DateTime());
                  $this->addFlash(
                        'success',
                        'External  reviewer assigned successfully!'
                    ); 
                        $entityManager->persist($reviewAssignment);
                        $entityManager->flush();
                        return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));
        
                        

            }
        ////////////////External reviewer
        return $this->render('review_assignment/new.html.twig', [
            'review_assignment' => $reviewAssignment,
            'submission' => $submission,
            'review_assignments'=>$allreviewersfrom_i_r_b,
            'form' => $form->createView(),
            'externalreviewerform'=>$externalreviewerform->createView(),
        ]);
    }
  /**
     * @Route("/{id}/accept/", name="accept_invitation", methods={"GET","POST"})
     */
    public function acceptinvitation(Request $request, ReviewAssignment $reviewAssignment): Response
    {
    $entityManager = $this->getDoctrine()->getManager();
    // if($this->getUser() != $reviewAssignment->getReviewer()){
    //     throw new AccessDeniedException(); 
    // }
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

    /**
     * @Route("/{id}/edit", name="review_assignment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ReviewAssignment $reviewAssignment): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
#        $subs = $entityManager->getRepository(Submission::class)->findBy(['submission' => $workunit ] );
        $form = $this->createForm(ReviewAssignmentType::class, $reviewAssignment);     
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
         #  return $this->redirectToRoute('submission_index');
         $this->addFlash(
            'success',
            'Update has been made to the submission successfully!'
        ); 

           return $this->redirectToRoute('review_assignment_new', array('id'=>$reviewAssignment->getSubmission()->getId()));
        }
         return $this->render('review_assignment/edit.html.twig', [
            'review_assignment' => $reviewAssignment,
            'form' => $form->createView(),
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
     * @Route("/{id}/delete", name="review_assignment_delete", methods={  "GET","POST"})
     */
    public function delete(ReviewAssignment $reviewAssignment  ): Response
    {
 
             $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reviewAssignment);
            $entityManager->flush() ;

         $flashbag = $this->get('session')->getFlashBag();
        $flashbag->add("info", "Reviewer deleted successfully ! Thank you!");
      
        return $this->redirectToRoute('review_assignment_new', array('id'=>$reviewAssignment->getSubmission()->getId()));
    }
}
 
 
