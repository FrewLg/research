<?php

namespace App\Controller;
use App\Entity\CallForProposal;
use App\Entity\CoAuthor;
use App\Entity\CollaboratingInstitution;
use App\Entity\EditorialDecision;
use App\Entity\Expense;
use App\Entity\PublishedSubmission;
use App\Entity\PublishedSubmissionAttachment;
use App\Entity\Review;
use App\Entity\ReviewAssignment;
use App\Entity\Submission;
use App\Entity\SubmissionAttachement;
use App\Entity\SubmissionBudget;
use App\Filter\Type\FilterFunctions;
use App\Filter\Type\SubmissionFilterType;
use App\Form\EditorialDecisionType;
use App\Form\ReviewType;
use App\Form\SubmissionType;
use App\Message\SendEmailMessage;
use App\Repository\CallForProposalRepository;
use App\Repository\EvaluationFormRepository;
use App\Repository\ReviewRepository;
use App\Repository\SubmissionRepository;
use App\Utils\Constants;
use Doctrine\ORM\Query\Expr;
use Dompdf\Dompdf;
use Dompdf\Options;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/submission")
 */
class SubmissionController extends AbstractController {
    /**
     * @Route("/", name="submission_index", methods={"GET","POST"})
     */
    public function index(Request $request, SubmissionRepository $submissionRepository, PaginatorInterface $paginator, FilterBuilderUpdaterInterface $query_builder_updater): Response {
        $this->denyAccessUnlessGranted('assn_clg_cntr');
        $em = $this->getDoctrine()->getManager();
        //  $submissionRepository = array_reverse($em->getRepository(Submission::class)->findAll());
        $formFilter = $this->get('form.factory')->create(SubmissionFilterType::class);
        $formFilter->handleRequest($request);
        $info = 'All';
        $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
        if ($request->query->has($formFilter->getName())) {
            $filter = new FilterFunctions();
            $lexikFormFilter = $this->get('lexik_form_filter.query_builder_updater');
            $submissionRepository = $filter->filter($request, $formFilter, $em, $lexikFormFilter, 'App:User');
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
            'formFilter' => $formFilter->createView(),
            'submissions' => $Allsubmissions,
            'info' => $info,
        ]);
    }
/**
 * @Route("/{id}/callresponses", name="callresponses", methods={"GET","POST"})
 */
    public function callresponses(Request $request, CallForProposal $call, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('assn_clg_cntr');
        $em = $this->getDoctrine()->getManager();
        //  $submissionRepository = array_reverse($em->getRepository(Submission::class)->findAll());
        $formFilter = $this->get('form.factory')->create(SubmissionFilterType::class);
        $formFilter->handleRequest($request);
        $info = $call->getSubject();
        $submissionRepository = array_reverse($em->getRepository('App:Submission')->findBy(['callForProposal' => $call]));

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
            'submissions' => $Allsubmissions,
            'info' => $info,
        ]);
    }
    /**
     * @Route("/filter/{filter}/", name="submission_filter", methods={"GET"})
     */
    public function byfilter(Request $request, $filter, PaginatorInterface $paginator, FilterBuilderUpdaterInterface $query_builder_updater): Response {

        $this->denyAccessUnlessGranted('assn_clg_cntr');
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
     * @Route("/wizard/{uidentifier}", name="submission_firststepold", methods={"GET","POST"})
     */
    public function metadata(Request $request, CallForProposal $callForProposal, UserController $test, MailerInterface $mailer): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        ##########################
        $userdetails = $this->getUser()->getUserInfo();
        if ($userdetails == '') {
            $test->checkuser();
            return $this->redirectToRoute('myprofile');

        }
        // dd($userdetails);
        if (
            $userdetails->getFirstName() == '' || $userdetails->getMidleName() == '' ||
            $userdetails->getLastName() == '' ||
            $userdetails->getCollege() == '' ||  
            $userdetails->getEducationLevel() == '' || $userdetails->getAcademicRank() == '') {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Please complete your profile first before you submit the proposal  !");

            return $this->redirectToRoute('myprofile');
        }

##########################

        $p_i_college = $this->getUser()->getUserInfo()->getCollege();

        if (!$p_i_college == $callForProposal->getCollege()) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "You are not allowed make a submission from" . $p_i_college . " !");

            return $this->redirectToRoute('researchworks');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $new = false;

        //dd($request->request);
        $submission = $entityManager->getRepository(Submission::class)->findOneBy(['author' => $this->getUser(), 'callForProposal' => $callForProposal]);
        if ($submission == null) {
            $new = true;
            $submission = new Submission();

        } else {
            if ($submission->getStep() == 10) {
                $this->addFlash('warning', "Already completed submission");
                // return $this->redirectToRoute('myreviews');
            }
        }
        $submission->setCallForProposal($callForProposal);
        $submission->setUidentifier(md5(uniqid()));

        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);
        $submission->setStatus(1);
        if ($form->isSubmitted() && $form->isValid()) {
            $submission->setAuthor($this->getUser());

            if ($new) {
                $entityManager->persist($submission);
            }

            foreach ($submission->getSubmissionAttachements() as $key => $author) {

                // $file = $form->get('file')->getData();
                $files = $author->getFile('file');

                if ($files == NULL) {

                    $this->addFlash('danger', "Please upload a file with only valid word file format! Allowed file formats are  .doc , .docx , .odp ,
                ");

                    return $this->redirectToRoute('submission_firststepold', ["uidentifier" => $callForProposal->getUidentifier()]);

                }
            }

            if ($submission->getStep() == 10) {
                $submission->setSentAt(new \DateTime());

                $submission->setComplete("completed");
                $entityManager->flush();
                $this->addFlash('success', "submission complete");

                $invitation_url = 'submission/my-membership';
                #####################################
                ///////////// Let us email subscribed users to announcements
                $messages = $entityManager->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'SUBMISSION_CO_PI_INVITATION']);
                $subject = $messages->getSubject();
                $body = $messages->getBody();
                $em = $this->getDoctrine()->getManager();
                $query = $entityManager->createQuery(
                    'SELECT u.email ,  u.username
	    FROM App:CoAuthor s
	    JOIN s.researcher u
 	    WHERE s.submission = :submission')
                    ->setParameter('submission', $submission);
                $recepients = $query->getResult();
                $em = $this->getDoctrine()->getManager();
                $qb = $em->createQueryBuilder();
                $messages = $em->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'SUBMISSION_CO_PI_INVITATION']);
                $subject = $messages->getSubject();
                $body = $messages->getBody();
                foreach ($recepients as $row) {
                    $theEmails[] = $row['email'] . ' ';
                    $theNames[] = $row['username'] . ' ';
                    $theFirstNames[] = $row['username'] . ' ';
                }
                ////////////
                $length = count($recepients);
                for ($i = 0; $i < $length; $i++) {
///////////////
                    $theFirstName = $theFirstNames[$i];
                    if ($theFirstName == '') {
                        $theFirstName = $theNames[$i];
                        dd($theFirstName);
                    }
                    $theEmail = $theEmails[$i];
                    $email = (new TemplatedEmail())
                        ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
//    ->to($theEmails)
                        ->to(new Address($theEmails[$i], $theFirstNames[$i]))
                        ->bcc(new Address($theEmails[$i], $theFirstNames[$i]))
                        ->subject($subject)
                        ->htmlTemplate('emails/co-authorship-invitation.html.twig')
                        ->context([
                            'subject' => $subject,
                            'body' => $body,
                            'title' => $submission->getTitle(),
                            'submission_url' => $invitation_url,
                            'name' => $theFirstName,
                            'Authoremail' => $theEmail,
                        ])
                    ;
                    $mailer->send($email);
                }
##########
                $applicantmessages = $em->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'EMAIL_KEY_SUBMISSION_ACKNOWLEDGEMENT']);
                $applicantsubject = $applicantmessages->getSubject();
                $applicantbody = $applicantmessages->getBody();

                $submission_url = 'submission/' . $submission->getId() . '/status';
                $applicant = $submission->getAuthor()->getEmail();
                $applicantname = $submission->getAuthor()->getUserInfo()->getFirstName();
                $emailtwo = (new TemplatedEmail())
                    ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
                    ->to($applicant)
                    ->subject($applicantsubject)
                    ->htmlTemplate('emails/application_ack.html.twig')
                    ->context([
                        'subject' => $applicantsubject,
                        'body' => $applicantbody,
                        'title' => $submission->getTitle(),
                        'submission_url' => $submission_url,
                        'name' => $applicantname,
                        'Authoremail' => $applicant])
                ;

                $mailer->send($emailtwo);

                // $sendEmail = new SendEmailMessage([$this->getUser()->getEmail()], Constants::EMAIL_KEY_SUBMISSION_ACKNOWLEDGEMENT, "emails/application_ack.html.twig", [
                // ]);
                // $this->dispatchMessage($sendEmail);
                // $emails = [];
                // foreach ($submission->getCoAuthors() as $key => $author) {
                //     $emails[] = $author->getEmail();
                // }
                // $sendEmail = new SendEmailMessage($emails, Constants::EMAIL_KEY_SUBMISSION_ACKNOWLEDGEMENT, "emails/application_ack.html.twig", [
                // ]);
                // $this->dispatchMessage($sendEmail);
                return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));

                // return $this->redirectToRoute('myreviews');
            }
            $entityManager->flush();

            ##############################

            return $this->redirectToRoute('submission_firststepold', ["uidentifier" => $callForProposal->getUidentifier()]);
        }
        return $this->render('submission/metadata.html.twig', [
            'submissionform' => $form->createView(),
            'submission' => $submission,
            'call' => $callForProposal,
        ]);
    }
    /**
     * @Route("/{uid}/research-sumary", name="research_sumary", methods={"GET"})
     */
    public function exportnow(Request $request, $uid) {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $this->getDoctrine()->getManager();

        $submission = $em->getRepository('App:Submission')->findOneBy(['uidentifier' => $uid]);

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $pdfOptions->set('tempDir', '/home/ghost/Desktop/pdf-export/tmp');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->set_option("isPhpEnabled", true);

        $html = $this->renderView('submission/summary.html.twig', [
            'user' => $this->getUser(),
            'submission' => $submission,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        $font = null;
        $dompdf->getCanvas()->page_text(72, 18, "Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0, 0, 0));

        ob_end_clean();
        $filename = $submission->getTitle();

        $dompdf->stream($filename . "file.pdf", [
            "Attachment" => false,
        ]);
    }

    // ob_end_clean();
    // $dompdf->stream();
    /**
     * @Route("/metadata/{id}/", name="submission_firststep_edit", methods={"GET","POST"})
     */
    public function metadataedit(Request $request, Submission $submission, CallForProposalRepository $callForProposalRepository): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $callForProposal = $submission->getCallForProposal();
//////// =======check whather it is confirmed or not============
        $confirmed = $entityManager->getRepository(Submission::class)->find($submission);
        $is_submission_confirmed = $confirmed->getComplete();
        if ($is_submission_confirmed == '1') {
            $this->addFlash(
                'warning',
                'Confirmed submission will never be updated!'
            );
            return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));
        }

////////////////////////////
        $therequest = $entityManager->getRepository(CallForProposal::class)->find($submission->getCallForProposal());

        ################### Are you the one? #################################
        $em = $this->getDoctrine()->getManager();
        $thisUser = $this->getUser();
        $myapplications = $em->getRepository(Submission::class)->find($submission);
        $requesteduser = $myapplications->getAuthor();
        if ($requesteduser !== $thisUser) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Sorry you are not allowed for this service ! Thank you!");
            return $this->redirectToRoute('myreviews');
        }
        ################### Are you the one? #################################

        $guidelines = $therequest->getGuidelines();
//////////////////#####################check the call  deadline########################////////////////
        $deadline = $therequest->getDeadline();
        $today = new \DateTime();
        $message = '';
        if ($deadline <= $today) {
            $message = "Overdue!";
#    echo $day;
        }
        //////

        $entityManager = $this->getDoctrine()->getManager();
        $mew = $this->getUser()->getId();
        $user = $this->getUser();
//////allow reviewer if he is only assigned to this submission
        // $form = $this->createFormBuilder($submission)

        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);
        $submission->setStatus(1);
        if ($form->isSubmitted() && $form->isValid()) {
            $submission->setAuthor($this->getUser());

            if ($submission->getStep() == 10) {
                $submission->setSentAt(new \DateTime());
                $submission->setUidentifier(md5());

                $submission->setComplete("completed");
                $entityManager->flush();
                $this->addFlash('success', "submission complete");

                $sendEmail = new SendEmailMessage([$this->getUser()->getEmail()], Constants::EMAIL_KEY_SUBMISSION_ACKNOWLEDGEMENT, "emails/application_ack.html.twig", [
                ]);
                $this->dispatchMessage($sendEmail);
                $emails = [];
                foreach ($submission->getCoAuthors() as $key => $author) {
                    $emails[] = $author->getEmail();
                }
                $sendEmail = new SendEmailMessage($emails, Constants::EMAIL_KEY_SUBMISSION_ACKNOWLEDGEMENT, "emails/application_ack.html.twig", [
                ]);
                $this->dispatchMessage($sendEmail);

                return $this->redirectToRoute('myreviews');
            }
            $entityManager->flush();
            return $this->redirectToRoute('submission_firststepold', ["uidentifier" => $callForProposal->getUidentifier()]);
        }
        return $this->render('submission/metadata.html.twig', [
            'submissionform' => $form->createView(),
            'submission' => $submission,
            'call' => $callForProposal,
        ]);
    }
//          ->add('title')
//          ->add('sub_title')

//     ->add('agree_to_the_terms')

//            ->add('abstract')
//            ->add('project_end_at', DateType::class, array(
//             'placeholder' => [
//   'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
// ],
//           'widget' => 'single_text',
//           'format' => 'yyyy-MM-dd',
//              'attr' => array(

//        'required' => true,
// 'class'=>'form-group col-md-4',
//    )
//       ))
//        ->add('project_start_at', DateType::class, array(
//             'placeholder' => [
//   'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
// ],
//           'widget' => 'single_text',
//           'format' => 'yyyy-MM-dd',
//              'attr' => array(
//        'required' => true,
// 'class'=>'form-group nowrap col-md-4',
//    )
//       ))

//          ->getForm();
//         $form->handleRequest($request);
//         if ($form->isSubmitted() && $form->isValid()) {
//             $entityManager = $this->getDoctrine()->getManager();
//           $submission->setAuthor($user);
//             $this->addFlash(
//             'info',
//             'Submission initiated successfully!'
//         );
//         $submission->setProgress(10);
//             $entityManager->persist($submission);
//                      $submission->setProgress(30);
//             $entityManager->flush();
//             return $this->redirectToRoute('submission_secondstep' , array('id' => $submission->getId()));
//         }
//                return $this->render('submission_includes/metadata_edit.html.twig', [
//      'submission' => $submission,
//             'guidelines'=> $guidelines,
//             'deadline'=> $deadline,
//             'message'=>$message,
//  // 'call'=>$callForProposalRepository,
//             'submissionform' => $form->createView(),
//         ]);
//     }

//                /**
//      * @Route("/{id}/contributors", name="submission_contributors", methods={"GET","POST"})
//      */
//     public function contributors(Request $request , Submission $submission): Response
//     {

//         $this->denyAccessUnlessGranted('ROLE_USER');
//         $entityManager = $this->getDoctrine()->getManager();
//         $confirmed=$entityManager->getRepository(Submission::class)->find($submission);
//         $is_submission_confirmed=$confirmed->getComplete();
//                 $progress=$confirmed->getProgress();
//         if($is_submission_confirmed=='1s'){
//         $this->addFlash(
//             'warning',
//             'Confirmed submission will never be updated! Wait for revision rather'
//         );
//             return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));
//            }

//            $me= $this->getUser();
//            $author=$submission->getAuthor();
//              if($me==$author){
//                    $this->addFlash(
//                    'susccess',
//                    'Preveiw before you send the submision!'
//                );
//               }
//               else{

//             return $this->redirectToRoute('myreviews');
//            }

//          $contributors=$entityManager->getRepository(CoAuthor::class)->findBy(['submission' => $submission ] );
//         $mew= $this->getUser()->getId();
//           $user= $this->getUser();
//           $coAuthor = new CoAuthor();
//        $coAuthorform = $this->createFormBuilder($coAuthor)

//            ->add('name', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'First name',
//              'required' => true,
//    'class'=>'form-control',
//          )))

//               ->add('last_name', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'Last name',
//              'required' => true,
//    'class'=>'form-control',
//          )))

//             ->add('gender', ChoiceType::class, [
//        'placeholder' => 'Select gender',
//       'choices' => [
//             'Male' => 'Male',
//             'Female' => 'Female',

//     ],
//      'attr' => [
//                 'class' => 'form-control',
//                 'required' => true,
//             ] ,
// ])

//               ->add('affiliation', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'Affiliation',
//              'required' => true,
//    'class'=>'form-control',
//          )))

//      ->add('orcid', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'ORCID',
//              'required' => false,
//    'class'=>'form-control',
//          )))
//              ->add('email', EmailType::class, array(
//    'attr' => array(
//              'placeholder' => 'Email of corresponding author',
//              'required' => true,
//    'class'=>'form-control',
//          )))
//              ->add('role', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'Role like PI, Co-PI',
//              'required' => true,
//    'class'=>'form-control',
//          )))
//     ->add('role', ChoiceType::class, [
//              'placeholder' => '--Select member role  --',
//       'choices' => [
//             'PI' => 'PI',
//             'Co-PI' => 'Co-PI',
//             'Co-PI' => 'Co-PI',

//     ],
//      'attr' => [
//                 'class' => 'form-control',
//                 'required' => true,
//             ] ,
// ])

//              ->add('country', EntityType::class, array(
// 'required'=>false,
//                       'placeholder' => '-- Select country --',
//          'class' => 'App\Entity\Country',
//          'attr' => array(
//              'empty' => 'Select country ',
//              'required' => false,
//              'class' => 'chosen-select form-control',
//          )
//      ))

//                      ->add('bio',  CKEditorType::class,[
//     'attr'=>['placeholder'=>'Describe your reason why',

//     'class' => 'form-control',

//                  'required' => false,

// ],])

//     ->add('cv', FileType::class, [
//                 'label' => 'Upload CV of research member',
//                 'mapped' => false,
//                 'required' => false,
//             ])

//          ->getForm();
//         $coAuthorform->handleRequest($request);
//         if ($coAuthorform->isSubmitted() && $coAuthorform->isValid()) {
//             $entityManager = $this->getDoctrine()->getManager();
//             $coAuthor->setSubmission($submission);

//             if($progress<=20){
//                   $submission->setProgress(20);
//          }
//           $file3 = $coAuthor->getCv();
//          ////check there is atleast one PI to ths submission
//          $contribution=$entityManager->getRepository(CoAuthor::class)->findBy(['submission' => $submission ,'role'=>'PI'] );
//          $contributionCo= $coAuthor->getRole();
//     if($contribution &&  $contributionCo=='PI'){
//     $this->addFlash(
//             'danger',
//             'Sorry! The research PI role should be given for only one member!'
//         );
//     return $this->redirectToRoute('submission_contributors', array('id' => $submission->getId()));
//         }
//      //// End check there is atleast one PI to ths submission

//    if ($file3==''){
//        echo 'File not uploaded';
//     }   else{
//      $file3 = $coAuthorform->get('cv')->getData();
//           $fileName3 = md5(uniqid()).'.'.$file3->guessExtension();
//       $file3->move($this->getParameter('collaborators_cv'), $fileName3);
//            $coAuthor->setCv($fileName3);
//          }

//             $entityManager->persist($coAuthor);
//             $entityManager->flush();
//       $this->addFlash(
//             'success',
//             'Research member added successfully!'
//         );
//             return $this->redirectToRoute('submission_contributors', array('id' => $submission->getId()));
//         }

//         return $this->render('submission/contributors.html.twig', [
//           'submission' => $submission,
//             'co_authors'=>$contributors,
//                 'form'=>$coAuthorform->createView(),

//         ]);
//     }

//                /**
//      * @Route("/{id}/expensebudget", name="submission_budget", methods={"GET","POST"})
//      */
//     public function budgetexpense(Request $request, ExpenseRepository $expenseRepository ,Submission $submission): Response
//     {
//     $this->denyAccessUnlessGranted('ROLE_USER');
//         $entityManager = $this->getDoctrine()->getManager();
//         $confirmed=$entityManager->getRepository(Submission::class)->find($submission);
//         $is_submission_confirmed=$confirmed->getComplete();
//         $is_researchType_grant=$confirmed->getSubmissionType();
//                 $progress=$confirmed->getProgress();
//         if($is_submission_confirmed=='1'){
//     $this->addFlash(
//             'warning',
//             'Confirmed submission will never be updated! Wait for revision rather'
//         );
//             return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));
//     }
//           if($is_researchType_grant=='Grant'){

//             return $this->redirectToRoute('funding_institution', array('id' => $submission->getId()));
//     }

//     /////====
//     $me= $this->getUser();
//     $author=$submission->getAuthor();
//       if($me==$author){
//             $this->addFlash(
//             'success',
//             'Preveiw before you send the submision!'
//         );
//        }
//        else{

//      return $this->redirectToRoute('myreviews');
//     }

//     ////=====
//     $Expenses=$entityManager->getRepository(Expense::class)->findBy(['submission' => $submission ] );
//     $mew= $this->getUser()->getId();
//     $user= $this->getUser();
//     $expenses = new Expense();
//     $expensesform = $this->createFormBuilder($expenses)
//     ->add('name', TextType::class, array(
//    'attr' => array(
//              'placeholder' => 'Name of expense',
//              'required' => true,
//    'class'=>'form-control',
//     'minlength' => '4', 'maxlength' => '211',
//          )))
//             ->add('measurement', ChoiceType::class, [
//        'placeholder' => 'Select Expense measurement',
//       'choices' => [

//             'Personnel perdiem' => 'Personnel perdiem',
//             'Professional fee' => 'Professional fee',
//             'Purchase' => 'Purchase',
//              'Other expense' => 'Other expense',

//     ],
//      'attr' => [
//                 'class' => 'form-control',
//                 'required' => true,
//             ] ,
// ])
//     ->add('unit_cost', IntegerType::class, array(
//    'attr' => array(
//              'placeholder' => 'Unit cost ',
//              'required' => true,
//    'class'=>'form-control',
//    'min' => '1', 'max' => '500000',
//          )))

//     ->add('amount', IntegerType::class, array(
//    'attr' => array(
//              'placeholder' => 'Amount ',
//              'required' => true,
//    'class'=>'form-control',
//    'min' => '1', 'max' => '50000',
//          )))
//     ->add('quantity', IntegerType::class, array(
//    'attr' => array(
//              'placeholder' => 'Quantity ',
//              'required' => true,
//    'class'=>'form-control',
//    'min' => '1', 'max' => '500000',
//          )))
//     ->add('description',  CKEditorType::class,[
//     'attr'=>['placeholder'=>'Describe your reason why',

//     'class' => 'form-control',

//                  'required' => false,

// ],])

//        ->getForm();
//         $expensesform->handleRequest($request);
//         if ($expensesform->isSubmitted() && $expensesform->isValid()) {
//             $entityManager = $this->getDoctrine()->getManager();
//             $unitcost=$expenses->getUnitCost();
//             $quantity=$expenses->getQuantity();
//             $amount=$expenses->getAmount();
//             $total_requested_expense=$quantity*$unitcost*$amount;
//             $expenses->setRequestedexpense($total_requested_expense);
//             $expenses->setSubmission($submission);

//             if($progress<=25){
//                   $submission->setProgress(25);
//          }
//             $entityManager->persist($expenses);
//             $entityManager->flush();
//   $this->addFlash(
//             'info',
//             'Expense saved successfully!'
//         );
//             return $this->redirectToRoute('submission_budget', array('id' => $submission->getId()));
//         }
//           return $this->render('submission/budget_expense.html.twig', [
//         'submission' => $submission,
//             'expenses' => $Expenses,
//             'form' => $expensesform->createView(),
//         ]);
//     }

    /**
     * @Route("/{id}/status", name="submission_status", methods={"GET","POST"})
     */
    public function statusubmission(Request $request, Submission $submission): Response {
        ////Ultimate reviewers page
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();

        ################### Are you the one? #################################
        $em = $this->getDoctrine()->getManager();
        $thisUser = $this->getUser();
        $myapplications = $em->getRepository(Submission::class)->find($submission);
        $requesteduser = $myapplications->getAuthor();
        if ($requesteduser !== $thisUser) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Sorry you are not allowed for this service ! Thank you!");
            return $this->redirectToRoute('myreviews');
        }
        ################### Are you the one? #################################
        $me = $this->getUser()->getId();

        /////
//  $this->checkValidatdeAuthor($submission, $me);

        //////
        $metoo = $this->getUser();
        $editorialDecisions = $entityManager->getRepository(EditorialDecision::class)->findBy(['submission' => $submission]);
        #dd($me_as_a_reviewer.$me);
        $measareviewer = $this->getUser();
        $author = $submission->getAuthor();

        // $reviews=$entityManager->getRepository(Review::class)->findBy(['submission' => $submission ] );
        $review = new Review();

        if (!$measareviewer == $author) {
            ////if you are the author then you can't review it///////
            $this->addFlash(
                'warining',
                'You can not see the submission you made in this page!'
            );
            return $this->redirectToRoute('myreviews');
        }
//////allow reviewer if he is only assigned to this submission
        $form = $this->createFormBuilder($review)
            ->add('comment')
            ->add('attachment', FileType::class, [
                'label' => 'Review document  file',
                'mapped' => false,
                'required' => false,
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file3 = $review->getAttachment();
            $com = $review->getComment();
            if ($file3 == "") {
                echo 'file not uploaded';
            } else {
                $file3 = $form->get('attachment')->getData();
                $fileName3 = md5(uniqid()) . '.' . $file3->guessExtension();
                $file3->move($this->getParameter('review_files'), $fileName3);
                $review->setAttachment($fileName3);
            }
            $review->setSubmission($submission);
            $review->setCreatedAt(new \DateTime());
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));
        }
        ////// if e submission has benn sent to the publication then allow researcher to upload final report of the research with respect to their attachment types
        $published = new PublishedSubmission();

        $entityManager = $this->getDoctrine()->getManager();
#          $contributors=$entityManager->getRepository(CoAuthor::class)->findBy(['submission' => $submission ] );
        $publicationstatus = $entityManager->getRepository(PublishedSubmission::class)->findBy(['submission' => $submission]);
        $finalreportform = $this->createFormBuilder($published)
            ->add('attachement_type', EntityType::class, array(
                'placeholder' => '-- Select Component Type --',
                'class' => 'App\Entity\AttachementType',
                'attr' => array(
                    'empty' => '--select--- ',
                    'required' => false,
                    'class' => 'chosen-select form-control',
                ),
            ))
            ->add('published_date', DateType::class, array(
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => array(
                    'required' => true,
                    'class' => 'form-control',
                ),
            ))
            ->add('final_report', FileType::class, [
                'label' => 'Upload your terminal report file',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();
        $finalreportform->handleRequest($request);
        if ($finalreportform->isSubmitted() && $finalreportform->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file3 = $published->getFinalReport();

            if ($file3 = '') {
                echo 'File not uploaded';
            } else {
                $file3 = $finalreportform->get('final_report')->getData();
                $fileName3 = md5(uniqid()) . '.' . $file3->guessExtension();
                $file3->move($this->getParameter('submission_files'), $fileName3);
                $published->setFinalReport($fileName3);
            }
            ////// check if there is publication has
            if ($entityManager->getRepository(PublishedSubmission::class)->findBy(['submission' => $submission, 'attachement_type' => $published->getAttachementType()])) {
                $flashbag = $this->get('session')->getFlashBag();
                $flashbag->add("danger", "Sorry you have already uploaded '" . $fileName3 . "' file is the same attachment , please change  instead !");
                return $this->redirectToRoute('submission_status', array('id' => $submission->getId()));
            }
            $published->setSubmission($submission);
            $entityManager->persist($published);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Info saved successfully!'
            );
        }
        $attachements = $entityManager->getRepository(PublishedSubmissionAttachment::class)->findBy(['published_submission' => $publicationstatus]);
        $datasetused = new PublishedSubmissionAttachment();
        $entityManager = $this->getDoctrine()->getManager();
        $publicationstatus = $entityManager->getRepository(PublishedSubmission::class)->findBy(['submission' => $submission]);
        $Expenses = $entityManager->getRepository(SubmissionBudget::class)->findBy(['submission' => $submission]);
        $reviewsatge = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['submission' => $submission]));
        $reviews = $entityManager->getRepository(Review::class)->findBy(['submission' => $submission, 'allow_to_view' => 1]);
        $contributors = $entityManager->getRepository(CoAuthor::class)->find($submission);
        return $this->render('submission/status.html.twig', [
            'co_authors' => $contributors,
            'expenses' => $Expenses,
            'comments' => $reviews,
            'datasets' => $attachements,
            'review_assignments' => $reviewsatge,
            'publicationstatus' => $publicationstatus,
            'submission' => $submission,
            'editorialDecisions' => $editorialDecisions,
            'finalreportform' => $finalreportform->createView(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/datasets", name="publication_datasets_used", methods={"GET","POST"})
     */
    public function datasets(Request $request, PublishedSubmission $publicationinfo): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $attachements = $entityManager->getRepository(PublishedSubmissionAttachment::class)->findBy(['published_submission' => $publicationstatus]);

        return $this->render('submission_includes/dataset_used.html.twig', [

            'datasets' => $attachements,

        ]);

    }
    /**
     * @Route("/{id}/dataset/new/", name="publication_datasets", methods={"GET","POST"})
     */
    public function datasetattachment(Request $request, PublishedSubmission $publicationinfo): Response {

        $datasetused = new PublishedSubmissionAttachment();

        $datasetusedform = $this->createFormBuilder($datasetused)
            ->add('attachment_type', EntityType::class, array(
                'placeholder' => '-- Select Attachment Type --',
                'class' => 'App\Entity\AttachementType',
                'attr' => array(
                    'empty' => 'Select attachment type-- ',
                    'required' => false,
                    'class' => 'chosen-select form-inline form-control col-md-6',
                ),
            ))
            ->add('description', TextareaType::class, array(

                'attr' => array(
                    'class' => 'form-inline  form-control col-md-6',
                ),
            ))

            ->add('dataset_label', TextType::class, [
                'attr' => array(
                    'required' => true,

                ),
                'attr' => array(
                    'class' => 'form-inline form-control col-md-6',
                ),

            ])
            ->add('attachment_file', FileType::class, [
                'label' => 'Upload  dataset file',
                'mapped' => false,
                'required' => true,
                'attr' => array(
                    'class' => 'form-control col-md-6',
                ),
            ])
            ->getForm();
        $datasetusedform->handleRequest($request);
        if ($datasetusedform->isSubmitted() && $datasetusedform->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file3 = $datasetused->getAttachmentFile();

            if ($file3 = '') {
                echo 'File not uploaded';
            } else {
                $file3 = $datasetusedform->get('attachment_file')->getData();
                $fileName3 = md5(uniqid()) . '.' . $file3->guessExtension();
                $file3->move($this->getParameter('datasets'), $fileName3);
                $datasetused->setAttachmentFile($fileName3);
            }
            $datasetused->setPublishedSubmission($publicationinfo);

            $entityManager->persist($datasetused);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Info saved successfully!'
            );
            return $this->redirectToRoute('publication_datasets', array('id' => $publicationinfo->getId()));
        }
        $entityManager = $this->getDoctrine()->getManager();
        $datasets = $entityManager->getRepository(PublishedSubmissionAttachment::class)->findBy(['published_submission' => $publicationinfo]);
        return $this->render('published/dataset_form.html.twig', [
            'datasets' => $datasets,

            'datasetusedform' => $datasetusedform->createView(),

        ]);
    }

    /**
     * @Route("/attachment/{id}/deleteAttachment", name = "submission_attachment_delete", methods= {"DELETE"})
     */
    public function deleteAttachment(Request $request, SubmissionAttachement $submissionAttachement): Response {
        $callForProposal = $submissionAttachement->getSubmission()->getCallForProposal();
        if ($this->isCsrfTokenValid('delete' . $submissionAttachement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($submissionAttachement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('submission_firststepold', ["id" => $callForProposal->getId()]);
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
     * @Route("/{id}/showinvitation", name="showinvitation", methods={"GET"})
     */
    public function showinvitation(ReviewAssignment $reviewAssignment): Response {
        return $this->render('review_assignment/showinvitation.html.twig', [
            'review_assignment' => $reviewAssignment,
        ]);
    }
    /**
     * @Route("/{id}/details", name="submission_show",  methods={"GET","POST"})
     */
    public function directorshow(Request $request, Submission $submission, ReviewRepository $reviewRepository, MailerInterface $email): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager = $this->getDoctrine()->getManager();
        ################### Are you the one? #################################
        $thisUser = $this->getUser();
        $requesteduser = $submission->getAuthor();
        if ($requesteduser == $thisUser) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Sorry you are not allowed for this service ! Thank you!");
            return $this->redirectToRoute('myreviews');
        }

        ################### Are you the one? #################################

        #####################################
         
        # $review = $entityManager->getRepository(Review::class)->findBy(['submission' => $submission ] );
        $budger_requests = $entityManager->getRepository(Expense::class)->findBy(['submission' => $submission]);
        $contributors = $entityManager->getRepository(CoAuthor::class)->findBy(['submission' => $submission]);
        $CollaboratingInstitutions = $entityManager->getRepository(CollaboratingInstitution::class)->findBy(['submission' => $submission]);
        $Expenses = $entityManager->getRepository(SubmissionBudget::class)->findBy(['submission' => $submission]);
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb
            ->select('SUM(e.requestedexpense) as totalRequested, SUM(e.approvedexpense) as Totalapproved')
            ->from('App\Entity\Expense', 'e')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('e.submission', ':status'),
            ))
            ->setParameter('status', $submission)
            ->getQuery()
        ;
        $Overall_budger_request = $qb->getOneOrNullResult();
        $reviewers = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['submission' => $submission]));
        $reviews = $entityManager->getRepository(Review::class)->findBy(['submission' => $submission]);
        ################ Admin Revision#########################

        // $adminvevision = new Review();
        // $adminvevisionform = $this->createForm(ReviewType::class, $adminvevision);
        // $adminvevisionform->handleRequest($request);

        $review = new Review();
        $review->setSubmission($submission);
        $review->setReviewedBy($this->getUser());

//////allow reviewer if he is only assigned to this submission
        // $form = $this->createFormBuilder($review)
        $form = $this->createForm(ReviewType::class, $review);

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
            // $review->setClosed(1);

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));
        }

        $editorialDecision = new EditorialDecision();
        $editorialDecisionform = $this->createForm(EditorialDecisionType::class, $editorialDecision);

        $editorialDecisionform->handleRequest($request);
        if ($editorialDecisionform->isSubmitted() && $editorialDecisionform->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $editorialDecision->setSubmission($submission);
            $editorialDecision->setRevisedAt(new \DateTime());
            // $editorialDecision->setCreatedAt(new \DateTime());

            $editorialDecision->setEditedBy($this->getUser());
            $entityManager->persist($editorialDecision);
            $entityManager->flush();

            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));
        }

        ################ Admin Revision#########################

        return $this->render('submission/submission_details.html.twig', [
            'submission' => $submission,
            'Overall_budger_request' => $Overall_budger_request,
            'review_assignments' => $reviewers,
            'reviews' => $reviews,

            // 'editorialDecisionform'=>$editorialDecisionform->createView(),
            'adminvevisionform' => $form->createView(),
            'co_authors' => $contributors,
            'collaborating_institutions' => $CollaboratingInstitutions,
            'expenses' => $Expenses,
        ]);
    }
    /**
     * @Route("/myassigned", name="myassigned", methods={"GET"})
     */
    public function myassigned(Request $request, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        $this_is_me = $this->getUser();
        $myassigned = array_reverse($entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $this_is_me, 'closed' => NULL]));
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
     * @Route("/my-membership", name="membership", methods={"GET"})
     */
    public function mymembership(Request $request, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();

        $myemail = $this->getUser();
        // $membership = $entityManager->getRepository(CoAuthor::class)->findBy(['email' => $this_is_me]);
        $myresearches = array_reverse($entityManager->getRepository(CoAuthor::class)->findBy(['researcher' => $myemail]));
        ////// if no throw exception
        $Allmyresearches = $paginator->paginate(
            // Doctrine Query, not results
            $myresearches,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('submission/co-authorship.html.twig', [
            'collaborations' => $Allmyresearches,
        ]);
    }

    /**
     * @Route("/myresearches", name="myreviews", methods={"GET"})
     */
    public function myresearches(Request $request, PaginatorInterface $paginator): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager = $this->getDoctrine()->getManager();
        $me = $this->getUser()->getId();
        $this_is_me = $this->getUser();
        $myresearches = array_reverse($entityManager->getRepository(Submission::class)->findBy(['author' => $me]));
        $Assignment_id = $entityManager->getRepository(ReviewAssignment::class)->findBy(['reviewer' => $this_is_me]);
        ////// if no throw exception
        $Allmyresearches = $paginator->paginate(
            // Doctrine Query, not results
            $myresearches,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('submission/my_submission_review.html.twig', [
            'submissions' => $Allmyresearches,
            'myreviews' => $Assignment_id,
        ]);
    }
    /**
     * @Route("/{id}", name="submission_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Submission $submission): Response {
        if ($this->isCsrfTokenValid('delete' . $submission->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($submission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('myreviews');
    }
}
