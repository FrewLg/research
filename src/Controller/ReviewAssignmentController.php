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
use App\Entity\CoAuthor;

use App\Entity\GuidelineForReviewer;
use App\Form\GuidelineForReviewerType;
use App\Repository\GuidelineForReviewerRepository;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use App\Form\ExternalReviewAssignmentType;
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
use App\Entity\UserInfo;
use App\Helper\ReviewHelper;
use DateTime;
use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
// use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/reviewer-assignment")
 */
class ReviewAssignmentController extends AbstractController
{
    use CsrfCheckerTrait;
  
    /**
     * @Route("/{id}/assign", name="review_assignment_new", methods={"GET","POST"})
     */
    public function assign(Request $request, Submission $submission ,ReviewHelper $reviewHelper, 
    UserPasswordEncoderInterface $passwordEncoder,
    MailerInterface $mailer,  ReviewAssignmentRepository $reviewAssignmentRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if($submission->getComplete()==''){
         
            $this->addFlash(
                'danger',
                'Sorry! You incomplete submissions cannot be sent to the reviewer!'
            ); 
        return $this->redirectToRoute('submission_index');
        }

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
            // return $this->redirectToRoute('review_assignment_new',['id'=>$submission->getId()]);
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
//  $submission=$entityManager->getRepository(Submission::class)->findBy(['id'=>$submission->getId()]);
    
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
            
            $file3 = $form->get('file_tobe_reviewed')->getData();

   if ($file3==''){
   
    $this->addFlash(
        'danger',
        'Review file is not uploaded !'
  ); 

    }   else{
     $file3 = $form->get('file_tobe_reviewed')->getData();
          $fileName3 = md5(uniqid()).'.'.$file3->guessExtension();
      $file3->move($this->getParameter('review_files'), $fileName3);
           $reviewAssignment->setFileTobeReviewed($fileName3);
         }


         ##########################
     $assignedreviewer = $form->get('reviewer')->getData();
             
 $one_of_co_authors=$entityManager->getRepository(CoAuthor::class)->findOneBy(['submission'=>$submission->getId(), 'researcher'=>$assignedreviewer ]);
if($one_of_co_authors){
     $this->addFlash(
             'danger',
             'This user is one of the Co-author hence '.$assignedreviewer->getUserInfo().'  cannot be assigned.!'
       ); 
       return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));

}
 
            ##############
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
        // dd($submission->getId());


            $entityManager->persist($reviewAssignment);
            $entityManager->flush();
           $suffix= $reviewAssignment->getReviewer()->getUserInfo()->getSuffix();

           $messages = $entityManager->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'REVIEW_INVITATION']);
           $subject = $messages->getSubject();
           $body = $messages->getBody();
           $title=$submission->getTitle();
           $theFirstName= $reviewAssignment->getReviewer()->getUserInfo()->getFirstName();
          $invitation_url= "irb-review/".$reviewAssignment->getId()."/accept/" ;
           $theEmail=$reviewAssignment->getReviewer()->getEmail();
            $email = (new TemplatedEmail())
            ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
            ->to(new Address($reviewAssignment->getReviewer()->getEmail(), $reviewAssignment->getReviewer()->getUserInfo()))
            // ->cc(new Address($alternative_email[$i], $theFirstNames[$i]))
            ->subject($subject)
            ->htmlTemplate('emails/reviewerinvitation.html.twig')
            ->context([
                'subject' => $subject,
                'suffix' => $suffix,
                'body' => $body,
                'title' => $title, 
                'college'=>$submission->getCallForProposal()->getCollege(),
                'reviewerinvitation_URL' => $invitation_url,
                'name' => $theFirstName,
                'Authoremail' => $theEmail,
            ])
        ;
        $mailer->send($email);

            // return $this->redirectToRoute('review_assignment_new', array('id'=>$submission->getId()));
        }

        ////////////////External reviewer 

        $externalreviewerform = $this->createForm(ExternalReviewAssignmentType::class, $reviewAssignment);
        $externalreviewerform->handleRequest($request); 
 
            if ($externalreviewerform->isSubmitted() && $externalreviewerform->isValid()) {
         
                $reviewAssignment->setSubmission($submission);
                $duedate=$reviewAssignment->getDuedate();
                        $reviewAssignment->setInvitationSentAt(new \DateTime());
               
                    $file3external = $externalreviewerform->get('file_tobe_reviewed')->getData();

                    if ($file3external==''){
                        $this->addFlash(
                            'danger',
                            'Review file is not uploaded !'
                      ); 
                     }   else{
                      $file3external = $externalreviewerform->get('file_tobe_reviewed')->getData();
                           $fileName3ext = md5(uniqid()).'.'.$file3external->guessExtension();
                       $file3external->move($this->getParameter('review_files'), $fileName3ext);
                            $reviewAssignment->setFileTobeReviewed($fileName3ext);
                          }

                          ##########create account for ecternmal reviewer  
                            $parts=explode('@', $reviewAssignment->getExternalReviewerEmail()); 
                           $username=$parts[0];// username
                            $ext_email = $externalreviewerform->get('external_reviewer_email')->getData();
                             
                            $externaluser =new User();
                            $externaluser->setUsername($username);
                            $externaluser->setEmail($ext_email);
                            $pass_to_be_hashed="Rev".$username.$user->getId()."!";
                            $externaluser->setIsReviewer(1);
                            $externaluser->setPassword(
                                $passwordEncoder->encodePassword(
                                    $externaluser,
                                    $pass_to_be_hashed
                                )
                            );

                            $last_name = $externalreviewerform->get('last_name')->getData();
                            $middle_name = $externalreviewerform->get('middle_name')->getData();
                            $external_reviewer_name = $externalreviewerform->get('external_reviewer_name')->getData();
                            
                            // dd($pass_to_be_hashed);
                            $reviewAssignment->setReviewer($externaluser);
                
                            $entityManager->persist($externaluser);
                            $entityManager->flush();

                            $externaluserinfo =new UserInfo();
                            $externaluserinfo->setUser($externaluser);
                            
                            $externaluserinfo->setFirstName($external_reviewer_name);
                            $externaluserinfo->setMidleName($middle_name);
                            $externaluserinfo->setLastName($last_name);
                            $entityManager->persist($externaluserinfo);
                            $entityManager->flush();

                            $entityManager->persist($reviewAssignment);
                            $entityManager->flush();
                            $this->addFlash(
                                'success',
                                'External  reviewer has been assigned successfully!'
                            );  
 
                            #######################Email

                            $messages = $entityManager->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'REVIEW_INVITATION']);
                            $subject = $messages->getSubject();
                            $body = $messages->getBody();
                            $title=$submission->getTitle();
                            $invitation_url= "irb-review/".$reviewAssignment->getId()."/accept/" ;
                            $theEmail=$reviewAssignment->getReviewer()->getEmail();
                             $email = (new TemplatedEmail())
                             ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
                             ->to(new Address($ext_email, $external_reviewer_name))
                              ->subject($subject)
                             ->htmlTemplate('emails/review_invitation_external.html.twig')
                             ->context([
                                 'subject' => $subject,
                                 'suffix' => "",
                                 'body' =>  "<br>"."Please use your username and password provided below 
                                 to get  started with our platform.<br> Username:".$username."
                                 <br> Passwrod: ".$pass_to_be_hashed."<br>Please do not forget to change your password 
                                 after you logged into the system.",
                                 'title' => $title, 
                                 'college'=>$submission->getCallForProposal()->getCollege(),
                                 'reviewerinvitation_URL' => $invitation_url,
                                 'name' => $external_reviewer_name,
                                 'Authoremail' => $theEmail,
                             ])
                         ;
                        $mailer->send($email);


                        ######################
                      
                      

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
     * @Route("/{id}/edit", name="review_assignment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ReviewAssignment $reviewAssignment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

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
 
 
