<?php

namespace App\Controller\IRB;

use App\Entity\IRB\Amendment;
use App\Entity\IRB\AmendmentAttachment;
use App\Entity\IRB\Application;
use App\Entity\IRB\ApplicationAttachment;
use App\Entity\IRB\ApplicationMitigationStrategy;
use App\Entity\IRB\ApplicationResearchSubject;
use App\Entity\IRB\ApplicationReview;
use App\Entity\IRB\AttachmentType;
use App\Entity\IRB\IRBReview;
use App\Entity\IRB\IRBStatus;
use App\Entity\IRB\MitigationStrategy;
use App\Entity\IRB\MitigationStrategyGroup;
use App\Entity\IRB\RenewalRequest;
use App\Entity\IRB\ResearchSubject;
use App\Entity\IRB\ResearchSubjectCategory;
use App\Entity\IRB\ReviewStatus;
use App\Entity\IRB\ReviewStatusGroup;
use App\Entity\IRB\Revision;
use App\Entity\IRB\RevisionAttachment;
use App\Entity\IrbCertificate;
use App\Form\IRB\ApplicationFilterType;
use App\Form\IRB\AmendmentType;
use App\Form\IRB\ApplicationType;
use App\Form\IRB\RevisionType;
use App\Repository\IRB\ApplicationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application')]
class ApplicationController extends AbstractController
{
    #[Route('/', name: 'application_index', methods: ['GET', "POST"])]
    public function index(ApplicationRepository $applicationRepository, Request $request, PaginatorInterface $paginatorInterface): Response
    {

           $this->denyAccessUnlessGranted('BOARD_VIEW');
        
            $queryBuilder = $applicationRepository->getData([], $this->isGranted('ROLE_SECRETARY') ? null : $this->getUser(), true);
        $application_filter_form = $this->createForm(ApplicationFilterType::class)->handleRequest($request);

        if ($application_filter_form->isSubmitted() && $application_filter_form->isValid()) {
            $queryBuilder = $applicationRepository->getData($application_filter_form->getData(), $this->isGranted('ROLE_SECRETARY'));
        }
        $data = $paginatorInterface->paginate(

            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('application/index.html.twig', [
            'applications' => $data,
            'application_filter_form' => $application_filter_form->createView(),
        ]);
    }

    #[Route('/new', name: 'application_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $em = $this->getDoctrine()->getManager();
        $application = new Application();
        $application->setSubmittedBy($this->getUser());
        if ($request->getMethod() != "POST") {
            foreach ($em->getRepository(ResearchSubject::class)->findBy(array(), ["type" => "ASC"]) as $key => $value) {

                $applicationResearch =  new ApplicationResearchSubject();
                $applicationResearch->setSubject($value);
                $application->addApplicationResearchSubject($applicationResearch);
            }
            foreach ($em->getRepository(MitigationStrategy::class)->findAll() as $key => $value) {

                $mitigation =  new ApplicationMitigationStrategy();
                $mitigation->setStrategy($value);
                $application->addApplicationMitigationStrategy($mitigation);
            }
            foreach ($em->getRepository(ReviewStatus::class)->findAll() as $key => $value) {

                $review =  new ApplicationReview();
                $review->setReview($value);
                $application->addApplicationReview($review);
            }
            foreach ($em->getRepository(AttachmentType::class)->findAll() as $key => $value) {

                $attachment =  new ApplicationAttachment();
                $attachment->setType($value);
                $application->addApplicationAttachment($attachment);
            }
        }
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setStatus($entityManager->getRepository(IRBStatus::class)->find(1));

            // $application->setType(1);
            $application = $this->removeUnchecked($application);

            $entityManager->persist($application);
            $entityManager->flush();
            $this->addFlash("success", "Request sent successfully");
            return $this->redirectToRoute('application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/new.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
            'subject_category' => $em->getRepository(ResearchSubjectCategory::class)->findAll(),
            'mitigation_strategy_group' => $em->getRepository(MitigationStrategyGroup::class)->findAll(),
            'review_status_group' => $em->getRepository(ReviewStatusGroup::class)->findAll()

        ]);
    }

    #[Route('/{id}', name: 'application_show')]
    public function show(Application $application, Request $request, EntityManagerInterface $entityManager): Response
    {

        if ($request->request->get('renewal')) {
            if (true) {
                $renewal = new RenewalRequest();
                $renewal->setApplication($application);
                $renewal->setRequestedAt(new DateTime());
                $entityManager->persist($renewal);
                $entityManager->flush();
                $this->addFlash("success", "Revision sent successfully");
            } else {
                $this->addFlash("danger", "You can't renew IRB clearance for this application");
            }
            return $this->redirectToRoute('application_show', ["id" => $application->getId()], Response::HTTP_SEE_OTHER);
        }
        $amendment = new Amendment();
        $amendment->setApplication($application);
        $form = $this->createForm(AmendmentType::class, $amendment);
        $form->handleRequest($request);

        $revision = new Revision();
        $revision->setApplication($application);
        foreach ($entityManager->getRepository(AttachmentType::class)->findAll() as $key => $value) {

            $attachment =  new RevisionAttachment();
            $attachment->setType($value);
            $revision->addRevisionAttachments($attachment);
        }
        $form2 = $this->createForm(RevisionType::class, $revision);
        $form2->handleRequest($request);
        $review = $entityManager->getRepository(IRBReview::class)->findOneBy(['application' => $application]);

        if ($form->isSubmitted() && $form->isValid()) {
            $att = $request->files->get('amendment')["attachment"];
            foreach ($att as $key => $value) {
                $amendmentAtachment = new AmendmentAttachment();
                $amendmentAtachment->setUploadFile($value);
                $amendmentAtachment->setName($value->getClientOriginalName());
                $amendmentAtachment->setAmendment($amendment);
                $entityManager->persist($amendmentAtachment);
            }
            $entityManager->persist($amendment);
            $entityManager->flush();
            $this->addFlash("success", "Amendment requested successfully");
            return $this->redirectToRoute('application_show', ["id" => $application->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($form2->isSubmitted() && $form2->isValid()) {
            foreach ($revision->getRevisionAttachments() as $k => $val) {
                if (!$val->getChecked()) {
                    $revision->removeRevisionAttachments($val);
                }
            }
            $entityManager->persist($revision);
            $entityManager->flush();
            $this->addFlash("success", "Revision sent successfully");
            return $this->redirectToRoute('application_show', ["id" => $application->getId()], Response::HTTP_SEE_OTHER);
        }






        return $this->render('application/show.html.twig', [

            'application' => $application,
            'amendment' => $amendment,
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'review' => $review,
            'subject_category' => $entityManager->getRepository(ResearchSubjectCategory::class)->findAll(),
            'mitigation_strategy_group' => $entityManager->getRepository(MitigationStrategyGroup::class)->findAll(),
            'review_status_group' => $entityManager->getRepository(ReviewStatusGroup::class)->findAll()
        ]);
    }

    public function removeUnchecked(Application $application)
    {
        $vars = [
            $application->getApplicationAttachments(),
            $application->getApplicationMitigationStrategies(),
            $application->getApplicationReviews(),
            $application->getApplicationResearchSubjects()
        ];
        foreach ($vars as $key => $value) {
            foreach ($value as $k => $val) {
                if (!$val->getChecked()) {
                    $value->removeElement($val);
                }
            }
        }
        return $application;
    }

    #[Route('/{id}/edit', name: 'application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'application_delete', methods: ['POST'])]
    public function delete(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $application->getId(), $request->request->get('_token'))) {
            $entityManager->remove($application);
            $entityManager->flush();
        }

        return $this->redirectToRoute('application_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/certify", name="irb_cert", methods={"GET"})
     */
    public function exportcertnow(IrbCertificate $uid)
    {

        // 

        $em = $this->getDoctrine()->getManager();


        $submission = $em->getRepository('App:IrbCertificate')->findOneBy(['irbApplication' => $uid]);

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('tempDir', '/tmp');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->set_option("isPhpEnabled", true);

        $html = $this->renderView('application/cert.html.twig', [
            'name' => $this->getUser(),
            'desc' => $submission->getValidUntil(),
            'type' => $submission->getCertificateCode(),
            'date' => $submission->getIrbApplication(),
            'about' => $submission->getIrbApplication(),
            'training' => $submission->getIrbApplication()->getType(),

        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // $font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        // $font = null;
        // $dompdf->getCanvas()->page_text(72, 18,  $font, 10, array(0, 0, 0));

        ob_end_clean();
        $filename = $submission->getParticipant();

        $dompdf->stream($filename . "- certificate.pdf", [
            "Attachment" => true,
        ]);
    }
}
