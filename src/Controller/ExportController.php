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
 * @Route("/export")
 */

class    ExportController   extends AbstractController {
   
    
     /**
     * @Route("/research-theams", name="exportexcel", methods={"GET","POST"})
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
         foreach ($rejecteds as $phoneNumber) {
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
          $fileName = 'rejecteds.xlsx';
         $temp_file = tempnam(sys_get_temp_dir(), $fileName);
          $writer->save($temp_file);
          return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
          
         
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
     * @Route("/external-rev", name="alexternal_rev", methods={"GET","POST"})
     */
    public function externalreviewers(Request $request , PaginatorInterface $paginator ): Response
    {
        $this->denyAccessUnlessGranted('assn_clg_cntr');

        $entityManager = $this->getDoctrine()->getManager(); 
        #######################
        $query2 = $entityManager->createQuery(
            'SELECT  u.email , u.id, pi.last_name , pi.first_name, pi.midle_name,  pi.image, u.is_reviewer,   count(b.id) as subs,  count(u.id) as review_assignment
            FROM App:ReviewAssignment s 
            JOIN s.reviewer u 
            JOIN u.userInfo pi 
            JOIN s.submission b 
            where  u.is_reviewer =:external    GROUP BY u.id
        ')
        ->setParameter('external', 1  ); 
    $recepientextrnal = $query2->getResult();
      ######################## 

      dd($recepientextrnal);
            $spreadsheet = new Spreadsheet();
           /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
          $sheet = $spreadsheet->getActiveSheet();
          $sheet->setCellValue('A1', 'No.');
        //   $sheet->setCellValue('B1', 'Full name');
        //    $sheet->setCellValue('C1', 'Participant\'s Institute');
        //   $sheet->setCellValue('D1', 'Participant\'s College');
          $sheet->setTitle("External reviewers");
   
          $counter = 2;
          foreach ($recepientextrnal as $phoneNumber) {
              $sheet->setCellValue('A' . $counter, $phoneNumber->getId()); 
            //   $sheet->setCellValue('B' . $counter, $phoneNumber->getEmail());
            //   $sheet->setCellValue('C' . $counter, $phoneNumber->getParticipant()->getUserInfo()->getCollege());
            //   $sheet->setCellValue('D' . $counter, $phoneNumber->getParticipant()->getUserInfo()->getDepartment()); 
            $counter++;
          }
           $writer = new Xlsx($spreadsheet);
           $fileName = 'External reviewers.xlsx';
          $temp_file = tempnam(sys_get_temp_dir(), $fileName);
          
           $writer->save($temp_file);
          
           return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    } 



}
