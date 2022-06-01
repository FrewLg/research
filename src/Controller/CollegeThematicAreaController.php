<?php

namespace App\Controller;

use App\Entity\CollegeThematicArea;
use App\Form\CollegeThematicAreaType;
use App\Repository\CollegeThematicAreaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/college/thematic/area')]
class CollegeThematicAreaController extends AbstractController
{
    #[Route('/', name: 'app_college_thematic_area_index', methods: ['GET'])]
    public function index(CollegeThematicAreaRepository $collegeThematicAreaRepository): Response
    {
        return $this->render('college_thematic_area/index.html.twig', [
            'college_thematic_areas' => $collegeThematicAreaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_college_thematic_area_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CollegeThematicAreaRepository $collegeThematicAreaRepository): Response
    {
        $collegeThematicArea = new CollegeThematicArea();
        $form = $this->createForm(CollegeThematicAreaType::class, $collegeThematicArea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collegeThematicAreaRepository->add($collegeThematicArea);
            return $this->redirectToRoute('app_college_thematic_area_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('college_thematic_area/new.html.twig', [
            'college_thematic_area' => $collegeThematicArea,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_college_thematic_area_show', methods: ['GET'])]
    public function show(CollegeThematicArea $collegeThematicArea): Response
    {
        return $this->render('college_thematic_area/show.html.twig', [
            'college_thematic_area' => $collegeThematicArea,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_college_thematic_area_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollegeThematicArea $collegeThematicArea, CollegeThematicAreaRepository $collegeThematicAreaRepository): Response
    {
        $form = $this->createForm(CollegeThematicAreaType::class, $collegeThematicArea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collegeThematicAreaRepository->add($collegeThematicArea);
            return $this->redirectToRoute('app_college_thematic_area_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('college_thematic_area/edit.html.twig', [
            'college_thematic_area' => $collegeThematicArea,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_college_thematic_area_delete', methods: ['POST'])]
    public function delete(Request $request, CollegeThematicArea $collegeThematicArea, CollegeThematicAreaRepository $collegeThematicAreaRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collegeThematicArea->getId(), $request->request->get('_token'))) {
            $collegeThematicAreaRepository->remove($collegeThematicArea);
        }

        return $this->redirectToRoute('app_college_thematic_area_index', [], Response::HTTP_SEE_OTHER);
    }
}
