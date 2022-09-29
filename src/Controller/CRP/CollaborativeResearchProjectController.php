<?php

namespace App\Controller\CRP;

use App\Entity\CRP\CollaborativeResearchProject;
use App\Entity\CRP\Deliverables;
use App\Form\CRP\CollaborativeResearchProjectType;
use App\Repository\CRP\CollaborativeResearchProjectRepository;
use App\Repository\CRP\DeliverablesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/crp/ju')]
class CollaborativeResearchProjectController extends AbstractController
{
    #[Route('/', name: 'app_c_r_p_collaborative_research_project_index', methods: ['GET'])]
    public function index(CollaborativeResearchProjectRepository $collaborativeResearchProjectRepository): Response
    {
        return $this->render('crp/collaborative_research_project/index.html.twig', [
            'collaborative_research_projects' => $collaborativeResearchProjectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_c_r_p_collaborative_research_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CollaborativeResearchProjectRepository $collaborativeResearchProjectRepository): Response
    {
        $collaborativeResearchProject = new CollaborativeResearchProject();
        $form = $this->createForm(CollaborativeResearchProjectType::class, $collaborativeResearchProject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collaborativeResearchProjectRepository->add($collaborativeResearchProject);
            return $this->redirectToRoute('app_c_r_p_collaborative_research_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crp/collaborative_research_project/new.html.twig', [
            'collaborative_research_project' => $collaborativeResearchProject,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_c_r_p_collaborative_research_project_show', methods: ['GET'])]
    public function show(CollaborativeResearchProject $collaborativeResearchProject): Response
    {
        return $this->render('crp/collaborative_research_project/show.html.twig', [
            'collaborative_research_project' => $collaborativeResearchProject,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_c_r_p_collaborative_research_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollaborativeResearchProject $collaborativeResearchProject, CollaborativeResearchProjectRepository $collaborativeResearchProjectRepository): Response
    {
        $form = $this->createForm(CollaborativeResearchProjectType::class, $collaborativeResearchProject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collaborativeResearchProjectRepository->add($collaborativeResearchProject);
            return $this->redirectToRoute('app_c_r_p_collaborative_research_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crp/collaborative_research_project/edit.html.twig', [
            'collaborative_research_project' => $collaborativeResearchProject,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_c_r_p_collaborative_research_project_delete', methods: ['POST'])]
    public function delete(Request $request, CollaborativeResearchProject $collaborativeResearchProject, CollaborativeResearchProjectRepository $collaborativeResearchProjectRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collaborativeResearchProject->getId(), $request->request->get('_token'))) {
            $collaborativeResearchProjectRepository->remove($collaborativeResearchProject);
        }

        return $this->redirectToRoute('app_c_r_p_collaborative_research_project_index', [], Response::HTTP_SEE_OTHER);
    }
      /**
     * @Route("/{id}/done", name="toggle_status", methods={"POST"})
     */
    public function done(Request $request, Deliverables $task, DeliverablesRepository $collaborativeResearchProjectRepository): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$task->getId(), $request->request->get('_token'))) {
        $task->setStatus(1); 
        $collaborativeResearchProjectRepository->add($task);

        }

        return $this->redirectToRoute('app_c_r_p_collaborative_research_project_show', ['id'=>$task->getCollaborativeResearchProject()->getId()]);
    }
 


}
