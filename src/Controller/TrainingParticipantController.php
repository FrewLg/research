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

#[Route('/apply-training')]
class TrainingParticipantController extends AbstractController
{
    #[Route('/', name: 'training_participant_index', methods: ['GET'])]
    public function index(TrainingParticipantRepository $trainingParticipantRepository): Response
    {
        return $this->render('training_participant/index.html.twig', [
            'training_participants' => $trainingParticipantRepository->findAll(),
        ]);
    }

    #[Route('/apply/{id}/', name: 'participate', methods: ['GET', 'POST'])]
    public function new(Request $request , CallForTraining $callForTraining, EntityManagerInterface $entityManager): Response
    {
        $trainingParticipant = new TrainingParticipant();
      
        $userdetails = $this->getUser()->getUserInfo(); 

        $$callForTraining = $entityManager->getRepository('App:CallForTraining')->find($callForTraining);
        if (  $userdetails->getFirstName() == '' || $userdetails->getMidleName() == '' ||
            $userdetails->getLastName() == '' ||
            $userdetails->getCollege() == '' ||
            $userdetails->getEducationLevel() == '' || $userdetails->getAcademicRank() == '') {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "Please complete your profile first before you  register for participation  !");

            return $this->redirectToRoute('myprofile');
        }

        $p_i_college = $this->getUser()->getUserInfo()->getCollege();

        if (!$p_i_college == $callForTraining->getCollege()) {
            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("danger", "You are not allowed to apply from" . $p_i_college . " !");

            return $this->redirectToRoute('researchworks');
        }
 
        $user = $this->getUser();  
        
            $trainingParticipant->setParticipant($user);
        $trainingParticipant->setTraining($callForTraining); 
        $trainingParticipant->setAppliedAt(new \Datetime()); 
        $entityManager->persist($trainingParticipant);
        $entityManager->flush(); 
            
        $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("success", "You have been successfully registered for training. Thank You!");
 
            $applicantmessages = $em->getRepository('App:EmailMessage')->findOneBy(['email_key' => 'SUCCESSFUL_TRAINING_PARTICIPATION']);
                $applicantsubject = $applicantmessages->getSubject();
                $applicantbody = $applicantmessages->getBody();

                $submission_url = 'submission/' . $submission->getId() . '/status';
                $applicant = $submission->getParticipant()->getEmail();
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

            return $this->redirectToRoute('training_participant_index', [], Response::HTTP_SEE_OTHER);
   

         
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
