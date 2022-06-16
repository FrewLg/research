<?php

namespace App\Controller;
use App\Entity\CallForProposal;
use App\Entity\Submission;
use App\Entity\ThematicArea;
use App\Entity\TrainingParticipant;
use App\Utils\Constants;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/export")
 */

class ExportController extends AbstractController {

    /**
     * @Route("/{id}/research-teams", name="export_researchersexcel", methods={"GET","POST"})
     */
    public function theams(CallForProposal $call) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $submissions = $em->getRepository(Submission::class)->findBy(['callForProposal'=>$call]);
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

                if ($CoAuthors->getConfirmed() == NULL) {
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
     * @Route("/participant", name="exportexcelparticipant", methods={"GET","POST"})
     */
    public function trainingparticipant() {
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
     * @Route("/{id}/external-rev", name="alexternal_rev", methods={"GET","POST"})
     */
    public function externalreviewers(CallForProposal $call): Response {
        $this->denyAccessUnlessGranted('assn_clg_cntr');

        $entityManager = $this->getDoctrine()->getManager();
        #######################
        $query2 = $entityManager->createQuery(
            'SELECT  u.email , u.id, pi.last_name , pi.first_name, pi.midle_name,  pi.image, u.is_reviewer,   count(b.id) as subs,  count(u.id) as review_assignment
            FROM App:ReviewAssignment s
            JOIN s.reviewer u
            JOIN u.userInfo pi
            JOIN s.submission b
            JOIN b.callForProposal c
            where  u.is_reviewer =:external and c.id=:call   GROUP BY u.id ORDER BY  pi.first_name
        ')
            ->setParameter('call', $call)
            ->setParameter('external', 1);
        $recepientextrnal = $query2->getResult();
        $spreadsheet = new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Full name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Number of assignments');
        $sheet->setCellValue('E1', 'Staff Membership');
        $sheet->setTitle("External reviewers");
        $idcounter = 1;

        $counter = 2;
        foreach ($recepientextrnal as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $idcounter);
            $sheet->setCellValue('B' . $counter, $phoneNumber['first_name'] . $phoneNumber['midle_name'] . $phoneNumber['last_name']);
            $sheet->setCellValue('C' . $counter, $phoneNumber['email']);
            $sheet->setCellValue('D' . $counter, $phoneNumber['review_assignment']);
            if ($phoneNumber['is_reviewer'] == 1) {
                $sheet->setCellValue('E' . $counter, "External reviewer");

            }

            $idcounter++;
            $counter++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName = 'External reviewers.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    /**
     * @Route("/{id}/allaccepted", name="allaccepted", methods={"GET","POST"})
     */
    public function allaccepted(Request $request, CallForProposal $call, PaginatorInterface $paginator) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager = $this->getDoctrine()->getManager();
        $filterform = $this->createFormBuilder()->add("status", ChoiceType::class, [
            "multiple" => true,
            "required" => true,
            "expanded" => true,
            "choices" => [
                "Accepted" => Constants::SUBMISSION_STATUS_ACCEPTED,
                "Accepted with minor revision" => Constants::SUBMISSION_STATUS_ACCEPTED_WITH_MINOR_REVISION,
                "Accepted with major revision" => Constants::SUBMISSION_STATUS_ACCEPTED_WITH_MAJOR_REVISION,
                "Decline" => Constants::SUBMISSION_STATUS_DECLINED,
            ],
        ])->getForm();
        $status = $request->query->get("status");

        $filterform->handleRequest($request);
        if ($filterform->isSubmitted() && $filterform->isValid()) {

            $submissions = $this->getDoctrine()->getRepository(Submission::class)->getSubmissions(["status" => $filterform->getData()['status']]);
            if ($request->query->get("export")) {
                $spreadsheet = new Spreadsheet();
                /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'No.');
                $sheet->setCellValue('B1', 'Title');
                $sheet->setCellValue('C1', 'Decision ');
                $sheet->setTitle("Editorial decisions");

                $idcounter = 1;
                $counter = 2;
                foreach ($submissions as $phoneNumber) {
                    $sheet->setCellValue('A' . $counter, $idcounter);
                    $sheet->setCellValue('B' . $counter, $phoneNumber['title']);
                    $sheet->setCellValue('C' . $counter, $phoneNumber['remark']);

                    $counter++;
                    $idcounter++;
                }
                $writer = new Xlsx($spreadsheet);
                $fileName = 'Editorial decisions.xlsx';
                $temp_file = tempnam(sys_get_temp_dir(), $fileName);

                $writer->save($temp_file);

                return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

            }
        } else {
            $submissions = $this->getDoctrine()->getRepository(Submission::class)->getSubmissions();

        }

        $Allsubmissions = $paginator->paginate(
            // Doctrine Query, not results
            $submissions,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            25, array('wrap-queries' => true)
        );
        $info = 'All Accepted';
        ################################
        return $this->render('dashboard/submissions.html.twig', [
            'submissions' => $Allsubmissions,
            'info' => $info,
            'call' => $call,
            'filterform' => $filterform->createView(),
        ]);

    }

    /**
     * @Route("/{id}/theme", name="allbythemeex", methods={"GET","POST"})
     */
    public function allbythemeex(CallForProposal $call) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $calls = $em->getRepository(CallForProposal::class)->find($call);
        //  $submissions = $call->getSubmissions();
        $themes = $calls->getThematicArea();
        $spreadsheet = new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */

        $submissions = $em->getRepository(ThematicArea::class)->submissionByThemeCall($call);
        // dd($submissions);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Thematic area.');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'PI');
        $sheet->setCellValue('E1', 'PI\'s Institute');
        $sheet->setTitle("Researchs by Thematic areas ");
        $counter = 2;
//         dd($submiss ions );
//         $allsubs = array( $submissions); 

//     $firstKey = array_keys($submissions); 
    

//     #################################
//     foreach ($submissions as $row) {
//         $theEmails[] = $row['email'] . ' ';
//         $theNames[] = $row['username'] . ' ';
//         $theFirstNames[] = $row['first_name'] . ' ';
//     }

//      $length = count($submissions);
//     for ($i = 0; $i < $length; $i++) {
//          $theFirstName = $theFirstNames[$i];
     

//     }
//     #################################
//     $a=array("a"=>"red","b"=>"green","c"=>"blue");
//     array_walk($a, "myfunction");
// // var_dump($firstKey);
// foreach ($submissions as $mis=>$cal){
//     dd($mis);
//                 } 
// // dd($firstKey);
        foreach ($submissions  as $phoneNumber  ) {
            $sheet->setCellValue('A' . $counter, $counter);
            $sheet->setCellValue('B' . $counter, $phoneNumber->getName());
            // $sheet->setCellValue('D' . $counter, $phoneNumber->getAuthor());
            $counter2 = 2;
            $title[]=$phoneNumber;
         
 
            ########################
            // $sheet->setCellValue('C' . $counter, $phoneNumber->getAuthor()->getUserInfo());
// echo $phoneNumber->getName().'<br>';
            foreach ($phoneNumber->getSubmissions(['callForProposal'=>$call]) as $CoAuthors) {
                // foreach ($em->getRepository(ThematicArea::class)->submissionByCall($call )->getSubmissions() as $CoAuthors) {
                $sheet->setCellValue('C' . $counter, $CoAuthors->getTitle());
                $sheet->setCellValue('D' . $counter, $CoAuthors->getAuthor());
                $sheet->setCellValue('E' . $counter, $CoAuthors->getAuthor()->getUserInfo()->getCollege());
                //  $counter++;
                $counter2++;
// echo $CoAuthors->getTitle().'<br>';
// echo $CoAuthors->getAuthor().'<br>'; 
                $counter++;
                $counter2++;
            }

############################
            // $counter++;
        }
        // dd($submissions );
        $writer = new Xlsx($spreadsheet);
        $fileName = 'All submissions by theme of this call.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    function myfunction($value,$key)
{
echo "The key $key has the value $value<br>";
} 
    /**
     * @Route("/{id}/rev-result", name="review_result", methods={"GET","POST"})
     */

    public function results(CallForProposal $call) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        //  $submissions = $em->getRepository(Submission::class)->findAll();

        $submissions = $em->getRepository(Submission::class)->submissionByCall($call);

        $spreadsheet = new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Title');
        $sheet->setCellValue('C1', 'Review decision');
        $sheet->setCellValue('D1', 'Reviewer');
        $sheet->setTitle("Research review result ");
        //  dd($submissions);
        $counter = 2;
        foreach ($submissions as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $counter);
            $sheet->setCellValue('B' . $counter, $phoneNumber->getTitle());
            $counter2 = 2;
            ########################
            // $sheet->setCellValue('C' . $counter, $phoneNumber->getAuthor()->getUserInfo());
            foreach ($phoneNumber->getReviews() as $CoAuthors) {

                //  $sheet->setCellValue('C' . $counter, $CoAuthors->getReviewedBy()->getIsReviewer());

                if ($CoAuthors->getRemark() == 1) {

                    $sheet->setCellValue('C' . $counter, "Declined");

                } elseif ($CoAuthors->getRemark() == 2) {
                    $sheet->setCellValue('C' . $counter, "Accepted with major revision");

                } elseif ($CoAuthors->getRemark() == 3) {
                    $sheet->setCellValue('C' . $counter, "Accepted with minor revision");

                } elseif ($CoAuthors->getRemark() == 4) {
                    $sheet->setCellValue('C' . $counter, "Accepted");

                }

                if ($CoAuthors->getReviewedBy()->getIsReviewer() == NULL) {
                    $sheet->setCellValue('D' . $counter, "Internal");

                } elseif ($CoAuthors->getReviewedBy()->getIsReviewer() == 1) {
                    $sheet->setCellValue('D' . $counter, "External");

                }
                $counter++;
                $counter2++;
            }

############################
            $counter++;
        }
        $counter = 2;
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Review result of call from ' . $call->getCollege() . ' announced  on ' . $call->getPostDate()->format('Y-m-d') . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    public function exportall($query) {

        $spreadsheet = new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Full name');
        $sheet->setCellValue('C1', 'Email ');
        $sheet->setCellValue('D1', 'Number of assignments');
        $sheet->setCellValue('E1', 'Staff Membership');
        $sheet->setTitle("Internal reviewers");

        $idcounter = 1;
        $counter = 2;
        foreach ($$query as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $idcounter);
            $sheet->setCellValue('B' . $counter, $phoneNumber['first_name'] . $phoneNumber['midle_name'] . $phoneNumber['last_name']);
            $sheet->setCellValue('C' . $counter, $phoneNumber['email']);
            $sheet->setCellValue('D' . $counter, $phoneNumber['review_assignment']);
            if ($phoneNumber['is_reviewer'] == NULL) {

                $sheet->setCellValue('E' . $counter, "Internal reviewer");

            }
            $counter++;
            $idcounter++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Internal reviewers.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    /**
     * @Route("/{id}/internal-rev", name="internal_rev", methods={"GET","POST"})
     */
    public function internalreviewers(CallForProposal $call): Response {
        $this->denyAccessUnlessGranted('assn_clg_cntr');

        $entityManager = $this->getDoctrine()->getManager();
        #######################
        $query2 = $entityManager->createQuery(
            'SELECT  u.email , u.id, pi.last_name , pi.first_name, pi.midle_name,  pi.image, u.is_reviewer,   count(b.id) as subs,  count(u.id) as review_assignment
            FROM App:ReviewAssignment s
            JOIN s.reviewer u
            JOIN u.userInfo pi
            JOIN s.submission b
            JOIN b.callForProposal c
            where  u.is_reviewer is NULL and c.id =:id    GROUP BY u.id ORDER BY  pi.first_name
        ')
            ->setParameter('id', $call);
        $recepientextrnal = $query2->getResult();
        ########################

        //   dd($recepientextrnal);
        $spreadsheet = new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Full name');
        $sheet->setCellValue('C1', 'Email ');
        $sheet->setCellValue('D1', 'Number of assignments');
        $sheet->setCellValue('E1', 'Staff Membership');
        $sheet->setTitle("Internal reviewers");

        $idcounter = 1;
        $counter = 2;
        foreach ($recepientextrnal as $phoneNumber) {
            $sheet->setCellValue('A' . $counter, $idcounter);
            $sheet->setCellValue('B' . $counter, $phoneNumber['first_name'] . $phoneNumber['midle_name'] . $phoneNumber['last_name']);
            $sheet->setCellValue('C' . $counter, $phoneNumber['email']);
            $sheet->setCellValue('D' . $counter, $phoneNumber['review_assignment']);
            if ($phoneNumber['is_reviewer'] == NULL) {

                $sheet->setCellValue('E' . $counter, "Internal reviewer");

            }
            $counter++;
            $idcounter++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Internal reviewers.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

}
