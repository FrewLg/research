<?php

namespace App\Controller;
use App\Entity\CallForProposal;
use App\Entity\CoAuthor;
use App\Entity\CollaboratingInstitution;
use App\Entity\College;
use App\Entity\Submission;

use App\Entity\TrainingParticipant;
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
         $formFilter = $this->get('form.factory')->create(SubmissionFilterType::class);
        $formFilter->handleRequest($request);
        $info = 'All';
        $Allsubmissions = array_reverse($em->getRepository('App:Submission')->findAll());
        
        
        $entityManager = $this->getDoctrine()->getManager(); 
                #################################           
                $querytwo = $entityManager->createQuery(
                               'SELECT      d.id 
                     FROM App:Submission s  , App:User u 
                         JOIN u.userInfo i
                          JOIN i.department d
                          JOIN d.college c
                       WHERE   s.complete=:completed and c.id =:college   
                      ORDER BY  s.author 
                    '   
                    ) 
                               ->setParameter('completed', 'completed' ) 
                      ->setParameter('college', $this->getUser()->getUserInfo()->getCollege()  );
                $recepients = $querytwo->getScalarResult();
             
                    
        $submissions = $entityManager->getRepository(Submission::class)->findAll();
        $submissionbytheme = $entityManager->getRepository(ThematicArea::class)->findBy(['college' => $this->getUser()->getUserInfo()->getCollege() ]);
        $allcalls = $entityManager->getRepository(CallForProposal::class)->findBy(['college' => $this->getUser()->getUserInfo()->getCollege() ]);
        $copis = $entityManager->getRepository(CoAuthor::class)->findall();

        $allcallsp = $paginator->paginate(
            // Doctrine Query, not results
            $allcalls,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
        


        $query = $entityManager->createQuery(
            'SELECT    count(b.id) as subs,  count(u.id) as review_assignment
            FROM App:ReviewAssignment s 
            JOIN s.reviewer u 
            JOIN u.userInfo pi 
            JOIN s.submission b 
          where  u.is_reviewer  is NULL  GROUP BY u.id
        ');
                $recepients = $query->getResult();
                    
                #######################
                $query2 = $entityManager->createQuery(
                    'SELECT   count(b.id) as subs,  count(u.id) as review_assignment
                    FROM App:ReviewAssignment s 
                    JOIN s.reviewer u 
                    JOIN u.userInfo pi 
                    JOIN s.submission b 
                  where  u.is_reviewer =:external   GROUP BY u.id
                ')
             ->setParameter('external', 1  ); 
                        $recepientextrnal = $query2->getResult();
                ################################
                // $recepients = $querytwo->getScalarResult();
                $all=count($recepients)  ;
                $allext=count($recepientextrnal)  ;
                // dd(count($recepients) );

               
  #######################
                  $query3 = $entityManager->createQuery(
                    'SELECT DISTINCT s.remark as decision, count(b.id)  as proposals
                    FROM App:Review s 
                    
                    JOIN s.submission b    GROUP BY s.remark
                ');
                        $remark = $query3->getScalarResult();
                 ################################ 
                  #######################
                  $query4 = $entityManager->createQuery(
                    'SELECT  i.gender as Gender, count(s.id)  as Proposals
                    FROM App:User u 
                    JOIN u.submissions s 
                    JOIN u.userInfo i 
                    
                      GROUP BY i.gender
                ');
                        $remark2 = $query4->getScalarResult();
                        // dd($remark2 );
        ################################ 

//         $rejecteds = $entityManager->createQuery(
//           'SELECT  i.gender as Gender, count(s.id)  as Proposals
//           FROM App:User u 
//           JOIN u.submissions s 
//           JOIN u.userInfo i 
//           WHERE EXISTS
// (SELECT column_name FROM table_name WHERE condition); 
//                         dd($rejecteds );

         ################################

        ########################


        return $this->render('dashboard/dashboard.html.twig', [
            'formFilter' => $formFilter->createView(),
            'submissions' => $Allsubmissions,
            'bythemes'=>$submissionbytheme,
            'allcalls'=>$allcallsp,
            'submissions' => $submissions,
            'copis' => $copis, 
            'desision' => $remark, 
            'gender_distribution'=>$remark2,
            'all' =>  $all,
            'allext' =>  $allext
        ]);
    }  
  
     /**
     * @Route("/theam", name="exportexcel", methods={"GET","POST"})
     */
    public function theams(   )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $em = $this->getDoctrine()->getManager();  
         $submissions = $em->getRepository(Submission::class)->findAll(); 
        $spreadsheet = new Spreadsheet(); 
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */


        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Title.');
        $sheet->setCellValue('C1', 'PI');
        $sheet->setCellValue('D1', 'Co-PI (s)');
        $sheet->setCellValue('E1', 'PI\'s Institute');
        $sheet->setCellValue('F1', 'Not confirmed');
        $sheet->setCellValue('G1', 'PI\'s Department');
        $sheet->setTitle("Researcher"); 
        $counter = 2;
        foreach ($submissions as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $phoneNumber->getId());
            $sheet->setCellValue('B' . $counter, $phoneNumber->getTitle());
            $counter2 = 2; 
            ########################
            $sheet->setCellValue('C' . $counter, $phoneNumber->getAuthor()->getUserInfo());
            $sheet->setCellValue('E' . $counter, $phoneNumber->getAuthor()->getUserInfo()->getCollege());
            $sheet->setCellValue('G' . $counter, $phoneNumber->getAuthor()->getUserInfo()->getDepartment());
             foreach ($phoneNumber->getCoAuthors() as $CoAuthors) {
             $sheet->setCellValue('D' . $counter, $CoAuthors->getResearcher()->getUserInfo());

             if ( $CoAuthors->getConfirmed() == NULL ){
              $sheet->setCellValue('F' . $counter, $CoAuthors->getResearcher()->getUserInfo());
               
               }

             $counter++;
            $counter2++; 
       
     }
    


                   
############################
          $counter++;
        }
         $writer = new Xlsx($spreadsheet);
         $fileName = 'Researchers.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
         $writer->save($temp_file);
         return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
         
    }



     /**
     * @Route("/allassigned-rev", name="allassigned", methods={"GET","POST"})
     */
    public function allassigned(  Request $request,   PaginatorInterface $paginator )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $entityManager = $this->getDoctrine()->getManager();  
           #######################
           $query3 = $entityManager->createQuery(
            'SELECT DISTINCT b.id ,  b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
            FROM App:Review s 
            JOIN s.submission b     
            
            JOIN b.author a
            JOIN a.userInfo i

            WHERE s.remark=:remark
        ') 
                ->setParameter('remark', 'Accepted with minor revision' ) ;

                $rejecteds = $query3->getResult();

         ################################ 
                 $Allsubmissions = $paginator->paginate(
                  // Doctrine Query, not results
                  $rejecteds,
                  // Define the page parameter
                  $request->query->getInt('page', 1),
                  // Items per page
                  10
              );
              $info='All Accepted with minor revision';
         ################################
         return $this->render('dashboard/submissions.html.twig', [
           'submissions' => $Allsubmissions,
          'info' => $info,
      ]);
         
    }



     /**
     * @Route("/rejecteds", name="allrejected", methods={"GET","POST"})
     */
    public function allrejected(  Request $request,   PaginatorInterface $paginator )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $entityManager = $this->getDoctrine()->getManager();  
           #######################
           $query3 = $entityManager->createQuery(
            'SELECT DISTINCT  b.id ,  b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
            FROM App:Review s 
            JOIN s.submission b     
            
            JOIN b.author a
            JOIN a.userInfo i

            WHERE s.remark=:remark AND  NOT s.remark=:remark1 AND NOT s.remark=:remark2 

        ') 

                ->setParameter('remark', 'Declined' )  
               ->setParameter('remark1', 'Accepted' )  
              ->setParameter('remark2', '	Accepted with minor revision' )  
             ;

                $rejecteds = $query3->getResult();

                 

         ################################ 

//          $query3 = $entityManager->createQuery(
//           'SELECT  b.id ,  b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
//           FROM App:Review s 
//           JOIN s.submission b     
          
//           JOIN b.author a
//           JOIN a.userInfo i

//           WHERE   EXISTS
          
//         (SELECT   r.id FROM App:Review r

//           JOIN r.submission n  

//           -- WHERE n.id = b.id  OR    r.remark=:remark  OR s.remark=:remark1  OR   s.remark=:remark2  AND  NOT   s.remark=:remark3  OR  r.remark=:remark4)  

//           WHERE       EXISTS
          
//         (SELECT   rs.id FROM App:Review rs

//           JOIN rs.submission ns  

//           WHERE ns.id = b.id  OR    rs.remark=:remark  OR s.remark=:remark1  OR   s.remark=:remark2  AND  NOT   s.remark=:remark3  OR  rs.remark=:remark4 )   
                 
// )
//                 ') 

//               ->setParameter('remark', '	Declined' )   
//               ->setParameter('remark1', '	Declined' )  
//               ->setParameter('remark2', '	Declined' )  
//               ->setParameter('remark3', '	Accepted' )  
//               ->setParameter('remark4', '	Accepted' )  
// ;
//               $rejecteds = $query3->getResult();

         ######################
                 $Allsubmissions = $paginator->paginate(
                  // Doctrine Query, not results
                  $rejecteds,
                  // Define the page parameter
                  $request->query->getInt('page', 1),
                  // Items per page
                  10
              );
              $info='All rejected';
         ################################
         return $this->render('dashboard/submissions.html.twig', [
           'submissions' => $Allsubmissions,
          'info' => $info,
      ]);
         
    }


     /**
     * @Route("/minor-rev", name="minor_rev", methods={"GET","POST"})
     */
    public function minorrev(  Request $request,   PaginatorInterface $paginator )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $entityManager = $this->getDoctrine()->getManager();  
           #######################
           $query3 = $entityManager->createQuery(
            'SELECT  DISTINCT b.id ,  b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
            FROM App:Review s 
            JOIN s.submission b     
            
            JOIN b.author a
            JOIN a.userInfo i

            WHERE s.remark=:remark
        ') 
                ->setParameter('remark', 'Accepted with minor revision' ) ;

                $rejecteds = $query3->getResult();

         ################################ 
                 $Allsubmissions = $paginator->paginate(
                  // Doctrine Query, not results
                  $rejecteds,
                  // Define the page parameter
                  $request->query->getInt('page', 1),
                  // Items per page
                  10
              );
              $info='All Accepted with minor revision';
         ################################
         return $this->render('dashboard/submissions.html.twig', [
           'submissions' => $Allsubmissions,
          'info' => $info,
      ]);
         
    }



     /**
     * @Route("/allaccepted", name="allaccepted", methods={"GET","POST"})
     */
    public function allaccepted(  Request $request,   PaginatorInterface $paginator )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $entityManager = $this->getDoctrine()->getManager();  
           #######################
           $query3 = $entityManager->createQuery(
            'SELECT DISTINCT  b.id ,  b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
            FROM App:Review s 
            JOIN s.submission b     
            
            JOIN b.author a
            JOIN a.userInfo i

            WHERE s.remark=:remark
        ') 
                ->setParameter('remark', 'Accepted' ) ;

                $rejecteds = $query3->getResult();

         ################################ 
                 $Allsubmissions = $paginator->paginate(
                  // Doctrine Query, not results
                  $rejecteds,
                  // Define the page parameter
                  $request->query->getInt('page', 1),
                  // Items per page
                  10
              );
              $info='All Accepted';
         ################################
         return $this->render('dashboard/submissions.html.twig', [
           'submissions' => $Allsubmissions,
          'info' => $info,
      ]);
         
    }



     /**
     * @Route("/major-rev", name="all_minor", methods={"GET","POST"})
     */
    public function allminor(  Request $request,   PaginatorInterface $paginator )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 
        $entityManager = $this->getDoctrine()->getManager();  
           #######################
       
         #######################
         $query3 = $entityManager->createQuery(
          'SELECT DISTINCT  b.id ,    b.title   ,b.sent_at as sentAt, b.complete, i.first_name as firstName, i.midle_name, i.last_name
          FROM App:Review s 
          JOIN s.submission b     
          
          JOIN b.author a
          JOIN a.userInfo i
          
          WHERE   s.remark=:remark 
          -- HAVING     s.remark=:remarktwo

      ')  
              // ->setParameter('remarktwo', "Accepted with minor revision" )  
              ->setParameter('remark', 'Accepted with major revision' ) 
              ;

              $rejecteds = $query3->getResult();

       ################################ 

                 $Allsubmissions = $paginator->paginate( 
                  // Doctrine Query, not results
                  $rejecteds,
                  // Define the page parameter
                  $request->query->getInt('page', 1),
                  // Items per page
                  10
              );
              $info='All Accepted with major revision';
         ################################
         return $this->render('dashboard/submissions.html.twig', [
           'submissions' => $Allsubmissions,
          'info' => $info,
      ]);
         
    }

 
     /**
     * @Route("/participant", name="exportexcelparticipant", methods={"GET","POST"})
     */
    public function trainingparticipant(  )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
 
          $submissions = $em->getRepository(TrainingParticipant::class)->findAll();
         $spreadsheet = new Spreadsheet();
         /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Full name');
         $sheet->setCellValue('C1', 'Participant\'s Institute');
        $sheet->setCellValue('D1', 'Participant\'s College');
        $sheet->setTitle("Participants");
 
        $counter = 2;
        foreach ($submissions as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $phoneNumber->getId()); 
            $sheet->setCellValue('B' . $counter, $phoneNumber->getParticipant()->getUserInfo());
            $sheet->setCellValue('C' . $counter, $phoneNumber->getParticipant()->getUserInfo()->getCollege());
            $sheet->setCellValue('D' . $counter, $phoneNumber->getParticipant()->getUserInfo()->getDepartment()); 
          $counter++;
        }
         $writer = new Xlsx($spreadsheet);
         $fileName = 'Traninig participant.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
         $writer->save($temp_file);
        
         return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
         
    }


 /**
     * @Route("/research-theams", name="research_theams", methods={"GET","POST"})
     */
    public function allresearchers( Request $request, PaginatorInterface $paginator  )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $submission = array_reverse($em->getRepository('App:Submission')->findAll());
        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submission,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
 
        return $this->render('dashboard/test.html.twig', [ 
            'submissions' => $Allsubmissions, 
        ]);

    } 
 
    

}
