<?php

namespace App\Controller\IRB;

use App\Entity\IrbCertificate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IRBPublicController extends AbstractController
{
    #[Route('/irb-clearance', name: 'i_r_b')]
    public function index(Request $request): Response
    {
        $em=$this->getDoctrine()->getManager();
        if($request->request->get('validate')){
           $irbCertificate= $em->getRepository(IrbCertificate::class)->findOneBy(['certificateCode'=>$request->request->get('validate')]);
            if(!$irbCertificate){ $this->addFlash('error','Invalid clearance code');}
            else{
                return $this->render('irb/clearance.html.twig', [
                    'irb'=>$irbCertificate
                ]);
            }
        }
        return $this->render('irb/clearance.html.twig', [
           
        ]);
    }
}
