<?php

namespace App\Controller\IRB;

use App\Entity\IRB\Application;
use App\Entity\IRB\ApplicationAttachment;
use App\Entity\IRB\ApplicationMitigationStrategy;
use App\Entity\IRB\ApplicationResearchSubject;
use App\Entity\IRB\ApplicationReview;
use App\Entity\IRB\AttachmentType;
use App\Entity\IRB\MitigationStrategy;
use App\Entity\IRB\MitigationStrategyGroup;
use App\Entity\IRB\ResearchSubject;
use App\Entity\IRB\ResearchSubjectCategory;
use App\Entity\IRB\ReviewStatus;
use App\Entity\IRB\ReviewStatusGroup;
use App\Form\IRB\ApplicationFilterType;
use App\Form\IRB\ApplicationType;
use App\Repository\IRB\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application')]
class ApplicationController extends AbstractController
{
    #[Route('/', name: 'application_index', methods: ['GET',"POST"])]
    public function index(ApplicationRepository $applicationRepository, Request $request, PaginatorInterface $paginatorInterface): Response
    {
        $queryBuilder = $applicationRepository->getData();
        $application_filter_form=$this->createForm(ApplicationFilterType::class)->handleRequest($request);
       
        if ($application_filter_form->isSubmitted() && $application_filter_form->isValid()) {

            
            $queryBuilder = $applicationRepository->getData($application_filter_form->getData());

            

        }
        $data= $paginatorInterface->paginate(

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
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $application->setType(1);
            $entityManager->persist($application);
            $entityManager->flush();

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

    #[Route('/{id}', name: 'application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
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
}
