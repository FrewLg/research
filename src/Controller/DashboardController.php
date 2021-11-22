<?php

namespace App\Controller;
use App\Entity\CallForProposal;
use App\Entity\CoAuthor;
use App\Entity\CollaboratingInstitution;
use App\Entity\College;
use App\Entity\Submission;
use App\Entity\ThematicArea;
use App\Filter\Type\FilterFunctions;
use App\Filter\Type\SubmissionFilterType; 
use App\Repository\SubmissionRepository;
use App\Utils\Constants;
use Composer\Console\HtmlOutputFormatter;
use Doctrine\ORM\Query\Expr;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\ResponseHeaderBag; 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Html;
 use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */

class DashboardController extends AbstractController {
   

    /**
     * @Route("/all/", name="aadashboard", methods={"GET","POST"})
     */
    public function dashboard( ): Response {
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
     * @Route("/theme/", name="theme", methods={"GET","POST"})
     */
    public function theme( ): Response {
        // $this->denyAccessUnlessGranted('assn_clg_cntr');
        
                $entityManager = $this->getDoctrine()->getManager(); 
                #################################           
                // $querytwo = $entityManager->createQuery(
                //                'SELECT      d.id 
                //      FROM App:Submission s  , App:User u 
                //          JOIN u.userInfo i
                //           JOIN i.department d
                //           JOIN d.college c
                //        WHERE   s.complete=:completed and c.id =:college   
                //       ORDER BY  s.author 
                //     '   
                //     ) 
        //         $querytwo = $entityManager->createQuery(
        //             'SELECT      d.id,  s.title
        //   FROM App:Department d  , App:User u ,App:Submission s
        //       JOIN u.userInfo i 
        //        JOIN d.college c
        //     WHERE   s.complete=:completed and c.id =:college   
        //    '   
        //  ) 
        //               ->setParameter('completed', 'completed' ) 
        //               ->setParameter('college', $this->getUser()->getUserInfo()->getCollege()->getId() );
        //         $recepients = $querytwo->getScalarResult();
             
        //               dd($recepients);

########################## 
        $thiscollege = $this->getUser()->getUserInfo()->getCollege();
        $submissionbytheme = $entityManager->getRepository(ThematicArea::class)->findBy(['college' => $thiscollege ]);
        $submsissionbytheme = $entityManager->getRepository(College::class)->findBy(['id' => $thiscollege ]);

             return $this->render('dashboard/bytheme.html.twig', [
                'thematic_areas'=>$submissionbytheme,
                'colleges'=>$submissionbytheme,
                // 'sub_by_departments'=>$recepients,
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
  
     /**
     * @Route("/research-theams", name="research_theams", methods={"GET","POST"})
     */
    public function theams( Request $request, PaginatorInterface $paginator  )
    {
        $em = $this->getDoctrine()->getManager();

        $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submissionRepository,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('dashboard/all_researchers.html.twig', [ 
            'submissions' => $Allsubmissions, 
        ]);

    }

 /**
     * @Route("/ex", name="authorexcel", methods={"GET","POST"})
     */
    public function excelAuthors( Request $request, PaginatorInterface $paginator  )
    {
        $em = $this->getDoctrine()->getManager();

        $submissionRepository = array_reverse($em->getRepository('App:Submission')->findAll());
        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submissionRepository,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('dashboard/all_researchers.html.twig', [ 
            'submissions' => $Allsubmissions, 
        ]);


 
//          $submissions = $em->getRepository(Submission::class)->findAll();
     
     
//         $spreadsheet = new Spreadsheet();
        
//         /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
//         $sheet = $spreadsheet->getActiveSheet();
//         $sheet->setCellValue('A1', 'Hello World !');
//         $sheet->setTitle("My First Worksheet");
        
//         $counter = 2;
//         foreach ($submissions as $phoneNumber) {
//             $sheet->setCellValue('A' . $counter, $phoneNumber->getId());
//             $counter2 = 0;
            
// ########################
//   $sheet->setCellValue('B' . $counter, $phoneNumber->getAuthor()->getUserInfo());
                    
                   
// ############################
//           $counter++;
//         }
//          $writer = new Xlsx($spreadsheet);
//          $fileName = 'Researchers.xlsx';
//         $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
//          $writer->save($temp_file);
        
        // Return the excel file as an attachment
        // return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        
 
    }

}
