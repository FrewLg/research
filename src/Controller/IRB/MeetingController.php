<?php

namespace App\Controller\IRB;

use App\Entity\IRB\Meeting;
use App\Form\IRB\MeetingType;
use App\Repository\IRB\MeetingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/irb-meeting')]
class MeetingController extends AbstractController
{
    #[Route('/', name: 'i_r_b_meeting_index', methods: ['GET'])]
    public function index(MeetingRepository $meetingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('mng_brd_mtng');
        
        $query = $meetingRepository->getData();
        $meetings = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('irb/meeting/index.html.twig', [
            'meetings' => $meetings,
        ]);
    }

    #[Route('/new', name: 'i_r_b_meeting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $meeting = new Meeting();
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meeting->setCreatedBy($this->getUser());
            $meeting->setCollege($this->getUser()->getUserInfo()?->getCollege());
            $entityManager->persist($meeting);
            $entityManager->flush();
            $this->addFlash("success", "Meeting Created");

            return $this->redirectToRoute('i_r_b_meeting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('irb/meeting/new.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'i_r_b_meeting_show', methods: ['GET'])]
    public function show(Meeting $meeting): Response
    {
        return $this->render('irb/meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[Route('/{id}/edit', name: 'i_r_b_meeting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();
            $meeting->getApplications()->map(function ($app) use ($meeting) {
                
                $app->setMeeting($meeting);
            });
            $entityManager->flush();

            return $this->redirectToRoute('i_r_b_meeting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('irb/meeting/edit.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'i_r_b_meeting_delete', methods: ['POST'])]
    public function delete(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $meeting->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meeting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('i_r_b_meeting_index', [], Response::HTTP_SEE_OTHER);
    }
}
