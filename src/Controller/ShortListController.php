<?php

namespace App\Controller;

use App\Entity\CallForProposal;
use App\Entity\ReviewAssignment;
use App\Entity\Submission;
use App\Entity\SubmissionAttachement;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// use Lexik\Bundle\TranslationBundle\Util\Csrf\CsrfCheckerTrait;

/**
 * @Route("/short-list")
 */
class ShortListController extends AbstractController {
    use CsrfCheckerTrait;

    // private $filename;

    // public function __construct($filePath) {
    //     $this->filename = $filePath;
    // }

    // public function __construct($filePath) {
    //     $this->filename = $filePath;
    // }/home/ghost/Desktop/projects/research-ju/public/uploads/submission/football-betting-and-its-impact-proposal-final-619602dda84ba146154715.docx

    /**
     * @Route("/{id}/find", name="short_list", methods={"DELETE", "GET","POST"})
     */
    public function findall(CallForProposal $callForProposal, ReviewAssignment $reviewAssignment): Response {
        $this->denyAccessUnlessGranted('short_list_view');

        $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->remove($reviewAssignment);

        $this->addFlash(
            'success',
            'Reviewer allowed to edit the review  successfully!'
        );
        $entityManager->flush();

        return $this->redirectToRoute('review_assignment_new', array('id' => $reviewAssignment->getSubmission()->getId()));

    }

    /**
     * @Route("/{id}/findone", name="short_list_findone", methods={"DELETE", "GET","POST"})
     */
    public function findone(Submission $submission, Request $request, PaginatorInterface $paginator): Response {
        // $this->denyAccessUnlessGranted('short_list_view');
        $entityManager = $this->getDoctrine()->getManager();

        $fileName = $entityManager->getRepository(SubmissionAttachement::class)->
            findOneBy(['submission' => $submission]);

        $filePath = $this->getParameter('upload_destination') . '/' . $fileName->getFile();
        if (!$filePath) {
            $this->addFlash(
                'danger',
                'File not found!'
            );
            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

        }

        $content = file_get_contents($filePath);

        $handle = fopen($filePath, "r");
        $zip = zip_open($filePath);

        $striped_content = '';
        $content = '';

        if (!$filePath || !file_exists($filePath)) {
            $this->addFlash(
                'danger',
                'File not found!'
            );
            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

        }

        $zip = zip_open($filePath);


        if (!$zip ) {
            $this->addFlash(
                'danger',
                'File not found!'
            );
            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

        }

        // if (!$zip || is_numeric($zip)) {
        //     $this->addFlash(
        //         'danger',
        //         'Numeric data!'
        //     );
        //     return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

        // }

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) {
                continue;
            }

            if (zip_entry_name($zip_entry) != "word/document.xml") {
                continue;
            }

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        } // end while

        zip_close($zip);

        

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);
        
        $search =  $submission->getAuthor()->getUserInfo()->getFirstName();
        if(preg_match("/{$search}/i", $striped_content)) {
          $result="Researcher's name is found in file!";

          $striped_content= str_replace($search, "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> ".$search."</b>", $striped_content);
          
            $this->addFlash(
                'danger',
                "Researcher's name is found in file!"
            );
        }
        else{

            $result="Researcher's name is not found in file!"; 
            $this->addFlash(
                'success',
                "Clear! the researcher's name is not found in the proposal document"
            );
        }
        
        return $this->render('submission/doc.html.twig', [
             'document' => $striped_content,
             'result' => $result,
             'name' => $submission->getAuthor()->getUserInfo(),
         ]);

    }


    public function read_file_docx($filename) {

        $striped_content = '';
        $content = '';

        if (!$filename || !file_exists($filename)) {
            return false;
        }

        $zip = zip_open($filename);

        if (!$zip || is_numeric($zip)) {
            return false;
        }

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) {
                continue;
            }

            if (zip_entry_name($zip_entry) != "word/document.xml") {
                continue;
            }

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        } // end while

        zip_close($zip);

        //echo $content;
        //echo "<hr>";
        //file_put_contents('1.xml', $content);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;

    }
}
