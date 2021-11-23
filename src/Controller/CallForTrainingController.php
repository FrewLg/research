<?php

namespace App\Controller;

use App\Entity\CallForTraining;
use App\Form\CallForTrainingType;
use App\Repository\CallForTrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/call-for-training')]
class CallForTrainingController extends AbstractController
{
    #[Route('/', name: 'call_for_training', methods: ['GET'])]
    public function index(Request $request, CallForTrainingRepository $callForTrainingRepository , PaginatorInterface $paginator): Response 
    
    {

        $em = $this->getDoctrine()->getManager();
        $callForTraining = array_reverse($em->getRepository(CallForTraining::class)->findAll());
        $info = 'All';

        // Paginate the results of the query
        $alltraining = $paginator->paginate(
            // Doctrine Query, not results
            $callForTraining,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('call_for_training/for_participants.html.twig', [
            'call_for_trainings' => $alltraining,
        ]);
    }

    #[Route('/adm', name: 'call_for_training_index', methods: ['GET'])]
    public function foradmin(CallForTrainingRepository $callForTrainingRepository): Response
    {
        return $this->render('call_for_training/index.html.twig', [
            'call_for_trainings' => $callForTrainingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'call_for_training_new', methods: ['GET', 'POST'])]
    public function new(Request $request ,    EntityManagerInterface $entityManager): Response
    {

        
        $callForTraining = new CallForTraining();
        $form = $this->createForm(CallForTrainingType::class, $callForTraining);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $callForTraining->setCreatedAt(new \Datetime());
            $callForTraining->setCollege($this->getUser()->getUserInfo()->getCollege());
          
            $entityManager->persist($callForTraining);
            $entityManager->flush();

            $flashbag = $this->get('session')->getFlashBag();
            $flashbag->add("success", "Training has been created successfully !");


            return $this->redirectToRoute('call_for_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('call_for_training/new.html.twig', [
            'call_for_training' => $callForTraining,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'call_for_training_show', methods: ['GET'])]
    public function show(CallForTraining $callForTraining): Response
    {
        return $this->render('call_for_training/show.html.twig', [
            'call_for_training' => $callForTraining,
        ]);
    }

    #[Route('/{id}/edit', name: 'call_for_training_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,  EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CallForTrainingType::class, $callForTraining);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('call_for_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('call_for_training/edit.html.twig', [
            'call_for_training' => $callForTraining,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'call_for_training_delete', methods: ['POST'])]
    public function delete(Request $request, CallForTraining $callForTraining, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$callForTraining->getId(), $request->request->get('_token'))) {
            $entityManager->remove($callForTraining);
            $entityManager->flush();
        }

        return $this->redirectToRoute('call_for_training_index', [], Response::HTTP_SEE_OTHER);
    }
}
