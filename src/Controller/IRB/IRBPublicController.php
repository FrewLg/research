<?php

namespace App\Controller\IRB;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IRBPublicController extends AbstractController
{
    #[Route('/irb-clearance', name: 'i_r_b')]
    public function index(): Response
    {
        return $this->render('irb/clearance.html.twig', [
            'controller_name' => 'IRBController',
        ]);
    }
}
