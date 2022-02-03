<?php

namespace App\Controller\IRB;

use App\Entity\IRB\IRBReviewAssignment;
use App\Form\IRB\IRBReviewAssignmentType;
use App\Repository\IRB\IRBReviewAssignmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\IRB\IRBReview;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use App\Form\ExternalReviewAssignmentType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\Submission;
use App\Repository\IRB\ApplicationRepository;
use App\Repository\InstitutionalReviewersBoardRepository;
use App\Repository\IRB\IRBReviewRepository;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Entity\InstitutionalReviewersBoard;
use App\Entity\IRB\Application;
use App\Entity\UserInfo;
use App\Form\IRB\IRBReviewType;
use App\Helper\ReviewHelper;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/irb-reviewer-assignment")
 */
class IRBReviewAssignmentController extends AbstractController
{
    use CsrfCheckerTrait;

    /**
     * @Route("/{id}/assign", name="irb_review_assignment_new", methods={"GET","POST"})
     */
    public function assign(
        Request $request,
        Application $submission,
        ReviewHelper $reviewHelper,

        MailerInterface $mailer,
        IRBReviewAssignmentRepository $reviewAssignmentRepository
    ): Response {

        // $this->denyAccessUnlessGranted('assn_clg_cntr');


        $entityManager = $this->getDoctrine()->getManager();

        if ($submission->getSubmittedBy() == $this->getUser()) {
            $this->addFlash('danger','Sorry! You can not assign by yourself a reviewer to the submission you made!' );
            return $this->redirectToRoute('submission_index');
        }
        ///// check if the submission is completed or not 
        $reviewAssignment = new IRBReviewAssignment();
        $reviewAssignment->setStatus(1);
        $reviewAssignment->setApplication($submission);
// $reviewAssignmentRepository->findBy(["application"=>$submission]);
       
        // $messages = $entityManager->getRepository('App:InstitutionalReviewersBoard')->findByCollege();
        $messages = $entityManager->getRepository('App:InstitutionalReviewersBoard')->findByCollege($this->getUser()->getUserInfo()->getCollege());
        // dd($messages );
        $form = $this->createForm(IRBReviewAssignmentType::class, $reviewAssignment,["application"=>$submission]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $file3 = $form->get('file_tobe_reviewed')->getData();

            if ($file3 == '') {

                $this->addFlash(
                    'danger',
                    'Review file is not uploaded !'
                );
            } else {
                $file3 = $form->get('file_tobe_reviewed')->getData();
                $fileName3 = md5(uniqid()) . '.' . $file3->guessExtension();
                $file3->move($this->getParameter('review_files'), $fileName3);
                $reviewAssignment->setFileTobeReviewed($fileName3);
            }

            $reviewAssignment->setApplication($submission);
            $duedate = $reviewAssignment->getDuedate();
            $reviewAssignment->setInvitationSentAt(new \DateTime());
            $this->addFlash(
                'success',
                'Research reviewer assigned successfully!'
            );
            // dd($submission->getId());

            $reviewAssignment->setApplication($submission);

            $entityManager->persist($reviewAssignment);
            $entityManager->flush();
            $suffix = $reviewAssignment->getIRBReviewer();
            // dd( $form);
            $messages = $entityManager->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'REVIEW_INVITATION']);
            $subject = $messages->getSubject();
            $body = $messages->getBody();
            $title = $submission->getTitle();
            $theFirstName = $reviewAssignment->getIRBReviewer()->getUserInfo()->getFirstName();
            $invitation_url = "irb-review/" . $reviewAssignment->getId() . "/accept/";
            $theEmail = $reviewAssignment->getIRBReviewer()->getEmail();
            $email = (new TemplatedEmail())
                ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
                ->to(new Address($reviewAssignment->getIRBReviewer()->getEmail(), $reviewAssignment->getIRBReviewer()->getUserInfo()))
                // ->cc(new Address($alternative_email[$i], $theFirstNames[$i]))
                ->subject($subject)
                ->htmlTemplate('emails/reviewerinvitation.html.twig')
                ->context([
                    'subject' => $subject,
                    'suffix' => $suffix,
                    'body' => $body,
                    'title' => $title,
                    'college' => $reviewAssignment->getIRBReviewer()->getUserInfo()->getCollege(),
                    'reviewerinvitation_URL' => $invitation_url,
                    'name' => $theFirstName,
                    'Authoremail' => $theEmail,
                ]);
            //    $mailer->send($email);

            return $this->redirectToRoute('irb_review_assignment_new', array('id' => $submission->getId()));
        }


        $reviewAssignments = $entityManager->getRepository('App\Entity\IRB\IRBReviewAssignment')->findBy(['application' => $submission]);

        ////////////////External reviewer
        return $this->render('irb_review_assignment/new.html.twig', [
            'irb_review_assignment' => $reviewAssignments,
            'form' => $form->createView(),

        ]);
    }


    /**
     * @Route("/myassigned", name="irb_myassigned", methods={"GET"})
     */
    public function myassigned(Request $request, PaginatorInterface $paginator): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        $this_is_me = $this->getUser();
        $myassigned = $entityManager->getRepository(IRBReviewAssignment::class)->findBy(['irbreviewer' => $this_is_me, 'closed' => NULL], ["id" => "DESC"]);
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

        $entityManager = $this->getDoctrine()->getManager();
        #######################

        #######################
        $query3 = $entityManager->createQuery(
            'SELECT    b.id , ass.invitation_sent_at as InvitationSentAt,     ass.Declined as Declined,  b.title , s.createdAt  , ass.duedate  as dueDate
        FROM App\Entity\IRB\IRBReview s 
        JOIN s.application b     
        JOIN s.iRBReviewAssignment ass      
        WHERE   s.reviewed_by=:reviewer AND ass.inactive_assignment is NULL AND ass.closed=:closed 
'
        )
            ->setParameter('closed', 1)
            ->setParameter('reviewer', $this_is_me);

        $closeds = $query3->getResult();


        #################################################

        return $this->render('application/myassigned.html.twig', [
            'closeds' => $closeds,
            'myreviews' => $myassigneds,
        ]);
    }


    /**
     * @Route("/{id}/revise", name="review_application", methods={"GET","POST"})
     */
    public function revise(Request $request, IRBReviewAssignment $reviewAssignment): Response
    {
        ////Ultimate reviewers page

        $entityManager = $this->getDoctrine()->getManager();
        $submissionOfreviewer = $entityManager->getRepository(IRBReviewAssignment::class)->find($reviewAssignment);
        $submissions = $submissionOfreviewer->getApplication();
        #######################
        if ($reviewAssignment->getClosed() == 1) {
            return $this->redirectToRoute('irb_myassigned');
        }
        #######################

        $measareviewer = $this->getUser();
        $author = $submissions->getSubmittedBy();

        if ($measareviewer == $author) {
            ////if you are the author then you can't review it///////
            $this->addFlash(
                'warining',
                'You can not see the Application  you made in this page!'
            );
            return $this->redirectToRoute('irb_myassigned');
        }

        $review = new IRBReview();
        $review->setIRBReviewAssignment($reviewAssignment);
        $review->setApplication($reviewAssignment->getApplication());
        $review->setReviewedBy($measareviewer);
        $form = $this->createForm(IRBReviewType::class, $review);
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
                $reviewfile->move($this->getParameter('irb_uploads'), $Areviewfile);
                $review->setAttachment($Areviewfile);
            }
            ##########
            // $reviewfile2 = $form->get('evaluation_attachment')->getData();
            // // if ($reviewfile2 == "") {
            //     $this->addFlash(
            //         'danger',
            //         'Evaluation  file  not uploaded!'
            //     );
            // } else {
            //     $reviewfile2 = $form->get('evaluation_attachment')->getData();
            //     $Areviewfile2 = md5(uniqid()) . '.' . $reviewfile2->guessExtension();
            //     $reviewfile2->move($this->getParameter('irb_uploads'), $Areviewfile2);
            //     $review->setEvaluationAttachment($Areviewfile2);
            // }
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
            return $this->redirectToRoute('irb_myassigned', array('id' => $reviewAssignment->getId()));
        }

        $reviews = $entityManager->getRepository(IRBReview::class)->findBy(['application' => $reviewAssignment->getApplication(), 'reviewed_by' => $measareviewer]);

        return $this->render('application/irb-revise.html.twig', [
            'review_assignment' => $reviewAssignment,
            'review_assignments' => $reviews,
            'submission' => $submissions,
            'form' => $form->createView(),
        ]);
    }
      /**
     * @Route("/{id}/delete", name="review_assignment_delete")
     */
    public function delete(IRBReviewAssignment $reviewAssignment): Response
    {
        $this->denyAccessUnlessGranted('assn_clg_cntr');

        $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->remove($reviewAssignment);
        $reviewAssignment->setInactiveAssignment(1);

        $submission=$reviewAssignment->getApplication();
        $entityManager->remove($reviewAssignment);
        $entityManager->flush();


        $this->addFlash("info", "Reviewer deleted successfully ! Thank you!");

        return $this->redirectToRoute('irb_review_assignment_new', array('id' => $submission->getId()));
    }
}
