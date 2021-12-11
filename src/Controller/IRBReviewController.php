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
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
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

$all = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $this_is_me]));
// $closedones = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $this_is_me, 'closed' => 1 , 'inactive_assignment' => NULL ]));
// ////// if no throw exception
// $closeds = $paginator->paginate(
//     // Doctrine Query, not results
//     $closedones,
//     // Define the page parameter
//     $request->query->getInt('page', 1),
//     // Items per page
//     10
// );


#################################################

$entityManager = $this->getDoctrine()->getManager();  
#######################

        #######################
        $query3 = $entityManager->createQuery(
        'SELECT    b.id , ass.invitation_sent_at as InvitationSentAt,     ass.Declined as Declined,  b.title , s.createdAt  , ass.duedate  as dueDate
        FROM App:Review s 
        JOIN s.submission b     
        JOIN s.reviewAssignment ass      
        WHERE   s.reviewed_by=:reviewer AND ass.inactive_assignment is NULL AND ass.closed=:closed
        -- HAVING     s.remark=:remarktwo

        ')  
        ->setParameter('closed', 1 )  
        ->setParameter('reviewer', $this_is_me   ) 
        ;

        $closeds = $query3->getResult();


#################################################

        return $this->render('submission/myassigned.html.twig', [
            'closeds' => $closeds,
            'all'=>$all,
            'myreviews' => $myassigneds,
        ]);
    }

    /**
     * @Route("/filter/{filter}/", name="submission_filter", methods={"GET"})
     */
    public function byfilter(Request $request, $filter, PaginatorInterface $paginator, FilterBuilderUpdaterInterface $query_builder_updater): Response {

        // $this->denyAccessUnlessGranted('assn_clg_cntr');
        $info = 'All';
        $em = $this->getDoctrine()->getManager();
        switch ($filter) {

        case 'al':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
            break;
        case 'cp':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['complete' => '1']));
            $info = 'Complete submission';
            break;
        case 'gr':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['submission_type' => 'grant']));
            $info = 'Grant';
            break;
        case 'cs':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['submission_type' => 'Community service']));
            $info = 'Community service';
            break;
        case 'mg':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['submission_type' => 'Mega Research']));
            $info = 'Technology transfer';
            break;
        case 'tt':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['submission_type' => 'Technology transfer']));
            $info = 'Technology transfer';
            break;
        case 'ps':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['published' => '1']));
            $info = 'Published';
            break;

        case 'rv':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['submission_type' => 'grant']));
            $info = 'Review assigned';
            break;
        case 'ic':
            $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['complete' => '0']));
            $info = 'Incomplete ';
            break;
        default:
            return $this->redirectToRoute('submission_index');
#     $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
        }

        // Paginate the results of the query
        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submissionRepository,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
        return $this->render('submission/index.html.twig', [
            'info' => $info,
            'submissions' => $Allsubmissions,
        ]);
    }


    /**
     * @Route("/{id}/assigned", name="his_assignment", methods={"GET"})
     */
    public function allassigned(Request $request, User $user, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
         
        $myassigned = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $user  ]));
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

        return $this->render('review_assignment/assigned.html.twig', [
            'user' => $user,
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

        if ($reviewAssignment->getReassigned()==1 ) {
            ////if you are the author then you can't review it///////
            $this->addFlash(
                'warining',
                'You have been re-assigned!'
            );
            return $this->redirectToRoute('rereviewsubmission', array('id' => $reviewAssignment->getId()));
        }



        $review = new Review();
        $review->setReviewAssignment($reviewAssignment);
        $review->setSubmission($reviewAssignment->getSubmission());
        $review->setReviewedBy($measareviewer);

        // $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

 
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reviewfile = $form->get('attachment')->getData();
            if ($reviewfile == "") {
                $this->addFlash(
                    'danger',
                    'Review file  not uploaded!'
                );
            } else {
                $reviewfile = $form->get('attachment')->getData();
                $Areviewfile = md5(uniqid()) . '.' . $reviewfile->guessExtension();
                $reviewfile->move($this->getParameter('review_files'), $Areviewfile);
                $review->setAttachment($Areviewfile);
            }
            ##########
            $reviewfile2 = $form->get('evaluation_attachment')->getData();
            if ($reviewfile2 == "") {
                $this->addFlash(
                    'danger',
                    'Evaluation  file  not uploaded!'
                );
            } else {
                $reviewfile2 = $form->get('evaluation_attachment')->getData();
                $Areviewfile2 = md5(uniqid()) . '.' . $reviewfile2->guessExtension();
                $reviewfile2->move($this->getParameter('review_files'), $Areviewfile2);
                $review->setEvaluationAttachment($Areviewfile2);
            }
            ###############
            $review->setCreatedAt(new \DateTime());
            $review->setReviewedBy($this->getUser());
            $reviewAssignment->setClosed(1);

            $entityManager->persist($review);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'You have  completed a revision successfully!'
            );
            return $this->redirectToRoute('reviewsubmission', array('id' => $reviewAssignment->getId()));
        }

        $editorialDecision = new EditorialDecision();
        $editorialDecisionform = $this->createFormBuilder($editorialDecision)
      
            ->add('feedback', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Feedback  for the author',
                    'required' => true,
                    'class' => 'form-control',
                )))
            ->getForm();
        $editorialDecisionform->handleRequest($request);

 
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
     * @Route("/{id}/rerevise", name="rereviewsubmission", methods={"GET","POST"})
     */
    public function rerevise(Request $request, ReviewAssignment $reviewAssignment, EvaluationFormRepository $evaluationFormRepository): Response {
        ////Ultimate reviewers page
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        // $id=  $review->getReviewAssignment()->getId();
        
 
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
        // $review = new Review();
         
        $reviewid = $entityManager->getRepository(Review::class)->findOneBy(['reviewAssignment'=>$reviewAssignment->getId(), 'reviewed_by'=> $this->getUser()]);
        $review = $entityManager->getRepository(Review::class)->find($reviewid);

        $review->setReviewAssignment($reviewAssignment);
        $review->setSubmission($reviewAssignment->getSubmission());
        $review->setReviewedBy($measareviewer);

        // $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

 
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reviewfile = $form->get('attachment')->getData();
            if ($reviewfile == "") {
                $this->addFlash(
                    'danger',
                    'Review  file  not uploaded!'
                );
            } else {
                $reviewfile = $form->get('attachment')->getData();
                $Areviewfile = md5(uniqid()) . '.' . $reviewfile->guessExtension();
                $reviewfile->move($this->getParameter('review_files'), $Areviewfile);
                $review->setAttachment($Areviewfile);
            }

              ##########
              $reviewfile2 = $form->get('evaluation_attachment')->getData();
              if ($reviewfile2 == "") {
                  $this->addFlash(
                      'danger',
                      'Evaluation  file  not uploaded!'
                  );
              } else {
                  $reviewfile2 = $form->get('evaluation_attachment')->getData();
                  $Areviewfile2 = md5(uniqid()) . '.' . $reviewfile2->guessExtension();
                  $reviewfile2->move($this->getParameter('review_files'), $Areviewfile2);
                  $review->setEvaluationAttachment($Areviewfile2);
              }
              ###############

            $review->setCreatedAt(new \DateTime());
            $review->setReviewedBy($this->getUser());
            $reviewAssignment->setClosed(1);

            $entityManager->persist($review);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'You have  completed a revision successfully!'
            );
            return $this->redirectToRoute('reviewsubmission', array('id' => $reviewAssignment->getId()));
        }

        $editorialDecision = new EditorialDecision();
        $editorialDecisionform = $this->createFormBuilder($editorialDecision)
      
            ->add('feedback', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Feedback  for the author',
                    'required' => true,
                    'class' => 'form-control',
                )))
            ->getForm();
        $editorialDecisionform->handleRequest($request);


       
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
        $this->denyAccessUnlessGranted('ROLE_USER');

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
        $this->denyAccessUnlessGranted('assn_clg_cntr');

        if ($this->isCsrfTokenValid('delete'.$reviewAssignment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->remove($reviewAssignment);
            $reviewAssignment->setInactiveAssignment(1);
              $this->addFlash(
            'success',
            'Reviewer unassigned!'
        ); 
            $entityManager->flush();
        }
            return $this->redirectToRoute('review_assignment_new', array('id'=>$reviewAssignment->getSubmission()->getId()));
 
    }


    /**
     * @Route("/{id}", name="reassign", methods={"DELETE", "GET","POST"})
     */
    public function reassign( ReviewAssignment $reviewAssignment  ): Response
    {
        $this->denyAccessUnlessGranted('assn_clg_cntr');

             $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->remove($reviewAssignment);
            $reviewAssignment->setInactiveAssignment(NULL);
            $reviewAssignment->setClosed(NULL);
            $reviewAssignment->setReassigned(1);
            
              $this->addFlash(
            'success',
            'Reviewer allowed to edit the review  successfully!'
        ); 
            $entityManager->flush();
        
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

if ($reviewAssignment->getIsRejected()){
    // echo"'dsada'";
    // dd();
    return $this->redirectToRoute('rereviewsubmission' );
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
 
 
