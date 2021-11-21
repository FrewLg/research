<?php

namespace App\Controller;
use App\Entity\CallForProposal;
use App\Entity\CoAuthor;
use App\Entity\CollaboratingInstitution;
use App\Entity\EditorialDecision;
use App\Entity\Expense;
use App\Entity\PublishedSubmission;
use App\Entity\PublishedSubmissionAttachment;
use App\Entity\Review;
use App\Entity\ReviewAssignment;
use App\Entity\Submission;
use App\Entity\SubmissionAttachement;
use App\Entity\SubmissionBudget;
use App\Filter\Type\FilterFunctions;
use App\Filter\Type\SubmissionFilterType;
use App\Form\EditorialDecisionType;
use App\Form\ReviewType;
use App\Form\SubmissionType;
use App\Message\SendEmailMessage;
use App\Repository\CallForProposalRepository;
use App\Repository\EvaluationFormRepository;
use App\Repository\ReviewRepository;
use App\Repository\SubmissionRepository;
use App\Utils\Constants;
use Doctrine\ORM\Query\Expr;
use Dompdf\Dompdf;
use Dompdf\Options;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */

class DashboardController extends AbstractController {
   

    /**
     * @Route("/all/", name="aadashboard", methods={"GET","POST"})
     */
    public function dashboard(MailerInterface $mailer): Response {
        // $this->denyAccessUnlessGranted('assn_clg_cntr');
        
                $entityManager = $this->getDoctrine()->getManager();
                // $query = $entityManager->createQuery(
                //  'SELECT   s.id  
                //     FROM App:CoAuthor c
                //         JOIN c.submission s
                //       WHERE  
 	            //   c.confirmed =:confirmation and s.complete=:completed and  c.confirmed is NOT NULL  GROUP BY s.id' ) 
                //      ->setParameter('confirmation', 1 ) 
                //      ->setParameter('completed', 'completed' );
                // $recepients = $query->getResult();
                #################################           
                $querytwo = $entityManager->createQuery(
                               'SELECT   s.id,  c.confirmed
                     FROM App:CoAuthor c
                         JOIN c.submission s
                       WHERE  
                    c.confirmed =:confirmation and s.complete=:completed and  c.confirmed is NOT NULL
                    and s.id=c.submissionid
                      GROUP BY s.id
         
                    -- UNION 

                    -- SELECT   s.id  
                    --  FROM App:CoAuthor c
                    --      JOIN c.submission s
                    --    WHERE  
                    -- c.confirmed =:confirmation and s.complete=:completed and  c.confirmed is NOT NULL  GROUP BY s.id
                   


                    ' 
                    
                    ) 
                      ->setParameter('confirmation', 1 ) 
                      ->setParameter('completed', 'completed' );
                $recepients = $querytwo->getResult();
             
                      dd($recepients);
##########################
//                 $recepients = $query->getResult();
//                 $querytwo = $entityManager->createQuery(
//                'SELECT SupplierName
//                FROM Suppliers
//                WHERE EXISTS (SELECT ProductName FROM Products WHERE Products.SupplierID = Suppliers.supplierID AND Price = 22);         );
// ');
                // dd($recepients);
                //  $recepientst = $querytwo->getResult();
                // dd($recepientst);

             return $this->render('dashboard/index.html.twig', [
                
            ]);
    }

    /**
     * @Route("/", name="dashboard", methods={"GET","POST"})
     */
    public function index(Request $request, SubmissionRepository $submissionRepository, PaginatorInterface $paginator, FilterBuilderUpdaterInterface $query_builder_updater): Response {
        $this->denyAccessUnlessGranted('assn_clg_cntr');
        $em = $this->getDoctrine()->getManager();
        //  $submissionRepository = array_reverse($em->getRepository(Submission::class)->findAll());
        $formFilter = $this->get('form.factory')->create(SubmissionFilterType::class);
        $formFilter->handleRequest($request);
        $info = 'All';
        $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
        if ($request->query->has($formFilter->getName())) {
            $filter = new FilterFunctions();
            $lexikFormFilter = $this->get('lexik_form_filter.query_builder_updater');
            $submissionRepository = $filter->filter($request, $formFilter, $em, $lexikFormFilter, 'App:User');
        }

        // Paginate the results of the query
        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submissionRepository,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
        return $this->render('dashboard/dashboard.html.twig', [
            'formFilter' => $formFilter->createView(),
            'submissions' => $Allsubmissions,
            'info' => $info,
        ]);
    }  
  

}
