<?php

namespace App\Controller;

use App\Entity\TrainingParticipant; 
use App\Entity\CallForTraining;
use App\Form\TrainingParticipantType;
use App\Repository\TrainingParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Knp\Component\Pager\PaginatorInterface;


use Dompdf\Dompdf;
use Dompdf\Options;
#[Route('/apply-training')]
class TrainingParticipantController extends AbstractController
{
    #[Route('/{id}/', name: 'training_participant_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, CallForTraining $callForTraining ): Response
    {
        $em = $this->getDoctrine()->getManager();

        $allcallForTraining = $em->getRepository('App:TrainingParticipant')->findBy(['training'=>$callForTraining]);

        $paginatedcallForTraining = $paginator->paginate(
            // Doctrine Query, not results
            $allcallForTraining,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('training_participant/index.html.twig', [
            'training_participants' => $paginatedcallForTraining  ,
            'callForTraining'=>$callForTraining,
        ]);
    }

    #[Route('/apply/{id}/', name: 'participate', methods: ['GET', 'POST'])]
    public function new(Request $request , CallForTraining $callForTraining, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $trainingParticipant = new TrainingParticipant();
      
        $userdetails = $this->getUser()->getUserInfo(); 
        $user = $this->getUser();  

        $$callForTraining = $entityManager->getRepository('App:CallForTraining')->find($callForTraining);
        if (  $userdetails->getFirstName() == '' || $userdetails->getMidleName() == '' ||
            $userdetails->getLastName() == '' ||
            $userdetails->getCollege() == ''  
             ) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Please complete your profile first before you  register for participation  !");

            return $this->redirectToRoute('myprofile');
        }

        $ifexists = $entityManager->getRepository('App:TrainingParticipant')->findBy(['participant'=>$user, 'training'=>$callForTraining] );

        if($ifexists){

            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("warning", "You have already been registered! Thank you");
            return $this->redirectToRoute('homepage');

        }

        $p_i_college = $this->getUser()->getUserInfo()->getCollege();

        if (!$p_i_college == $callForTraining->getCollege()) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "You are not allowed to apply from" . $p_i_college . " !");

            return $this->redirectToRoute('researchworks');
        }
 
        
            $trainingParticipant->setParticipant($user);
        $trainingParticipant->setTraining($callForTraining); 
        $trainingParticipant->setAppliedAt(new \Datetime()); 
        $entityManager->persist($trainingParticipant);
        $entityManager->flush(); 
            
        $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("success", "You have been successfully registered for training. Thank You!");
 
            $applicantmessages = $entityManager->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'SUCCESSFUL_TRAINING_PARTICIPATION']);
                $applicantsubject = $applicantmessages->getSubject();
                $applicantbody = $applicantmessages->getBody();

                $submission_url = 'submission/' . $trainingParticipant->getId() . '/status';
                $applicant = $trainingParticipant->getParticipant()->getEmail();
                $applicantname = $trainingParticipant->getParticipant()->getUserInfo()->getFirstName();
                $emailtwo = (new TemplatedEmail())
                    ->from(new Address('research@ju.edu.et', $this->getParameter('app_name')))
                    ->to($applicant)
                    ->subject($applicantsubject)
                    ->htmlTemplate('emails/general.html.twig')
                    ->context([
                        'subject' => $applicantsubject,
                        'body' => $applicantbody,
                        'title' => $callForTraining->getName(),
                        'submission_url' => $submission_url,
                        'name' => $applicantname,
                        'Authoremail' => $applicant])
                ;

                $mailer->send($emailtwo);  

            return $this->redirectToRoute('homepage');
   

         
    }

    /**
     * @Route("/{id}/cert", name="cert", methods={"GET"})
     */
    public function exportcertnow(Request $request, TrainingParticipant $uid) {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $this->getDoctrine()->getManager();

 
        $submission = $em->getRepository('App:TrainingParticipant')->findOneBy(['id' => $uid]);

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('tempDir', '/home/ghost/Desktop/pdf-export/tmp');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->set_option("isPhpEnabled", true);

        $html = $this->renderView('training_participant/cert.html.twig', [
            'name' => $this->getUser(),
             'desc' => $submission->getTraining()->getDescription(),
             'type' => $submission->getTraining()->getTrainingType(),
             
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // $font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        // $font = null;
        // $dompdf->getCanvas()->page_text(72, 18,  $font, 10, array(0, 0, 0));

        ob_end_clean();
        $filename = $submission->getParticipant();

        $dompdf->stream($filename . "file.pdf", [
            "Attachment" => false,
        ]);
    }

    #[Route('/{id}', name: 'training_participant_show', methods: ['GET'])]
    public function show(TrainingParticipant $trainingParticipant): Response
    {
        return $this->render('training_participant/show.html.twig', [
            'training_participant' => $trainingParticipant,
        ]);
    }

    #[Route('/{id}/edit', name: 'training_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingParticipant $trainingParticipant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrainingParticipantType::class, $trainingParticipant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('training_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training_participant/edit.html.twig', [
            'training_participant' => $trainingParticipant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'training_participant_delete', methods: ['POST'])]
    public function delete(Request $request, TrainingParticipant $trainingParticipant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainingParticipant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trainingParticipant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('training_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
