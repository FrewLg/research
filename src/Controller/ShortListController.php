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

        if (!$zip) {
            $this->addFlash(
                'danger',
                'File not found! or invalid    file format'
            );
            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

        }

        if (!$zip || is_numeric($zip)) {
            $this->addFlash(
                'danger',
                'Unable to read document data! Invalid document  file'
            );
            return $this->redirectToRoute('submission_show', array('id' => $submission->getId()));

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

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);
        $count = 0;

     
      

        $copis = $submission->getCoAuthors();
        if($copis){
        foreach ($copis as $value) {
            $count = 0;

            $patternsc = array();
            $patternsc[0] = $value->getResearcher()->getUserInfo()->getFirstName();
            $patternsc[1] = $value->getResearcher()->getUserInfo()->getMidleName();
            $patternsc[2] = $value->getResearcher()->getUserInfo()->getLastName();

            
            $replacementsc = array();
            $replacementsc[0] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patternsc[0] . "</b>";
            $replacementsc[1] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patternsc[1] . "</b>";
            $replacementsc[2] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patternsc[2] . "</b>";

            $patterns = array();
$patterns[0] = $submission->getAuthor()->getUserInfo()->getFirstName();
$patterns[1] = $submission->getAuthor()->getUserInfo()->getMidleName();
$patterns[2] = $submission->getAuthor()->getUserInfo()->getLastName();
// $patterns[1] = 'considera';
$replacements = array();
$replacements[0] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[0] . "</b>";
$replacements[1] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[1] . "</b>";
$replacements[2] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[2] . "</b>";



            if (
                preg_match("/{$patternsc[0]}\b/i", $striped_content)
                || preg_match("/{$patternsc[1]}\/^\s*{(\w+)}\s*=/i", $striped_content)
                || preg_match("/{$patternsc[2]}\b/i", $striped_content)
                || preg_match("/{$patterns[0]}\b/i", $striped_content)
                || preg_match("/{$patterns[1]}\/^\s*{(\w+)}\s*=/i", $striped_content)
                || preg_match("/{$patterns[2]}\b/i", $striped_content)
                

            ) {
                $result = "Name of the researcher has been found in   proposal file!";
                 $striped_content = str_replace($patternsc, $replacementsc, $striped_content, $count);
                 $striped_content = str_replace($patterns, $replacements, $striped_content, $count);

                $found = 1;

            }

        }
        }
        else{
//////////////////////////
$patterns = array();
$patterns[0] = $submission->getAuthor()->getUserInfo()->getFirstName();
$patterns[1] = $submission->getAuthor()->getUserInfo()->getMidleName();
$patterns[2] = $submission->getAuthor()->getUserInfo()->getLastName();
// $patterns[1] = 'considera';
$replacements = array();
$replacements[0] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[0] . "</b>";
$replacements[1] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[1] . "</b>";
$replacements[2] = "<b class='text-danger' style='background-color: rgb(255, 255, 102); color: rgb(0, 0, 0);'> " . $patterns[2] . "</b>";


        if (  preg_match("/{$patterns[0]}\b/i", $striped_content)
            || preg_match("/{$patterns[1]}\/^\s*{(\w+)}\s*=/i", $striped_content)
            || preg_match("/{$patterns[2]}\b/i", $striped_content)

        ) {
            $result = "Name of the researcher has been found in   proposal file!";
            $striped_content = str_replace($patterns, $replacements, $striped_content, $count);
 
            $found = 1;

        }
    }

        if ($found) {
            $this->addFlash(
                'danger',
                "Name of the researcher has been found in   proposal file!"
            );
        } else {

            $result = "Researcher's name is not found in proposal  file!";
            $this->addFlash(
                'success',
                "Clear! the researcher's name is not found in the proposal document"
            );
        }

        if ($striped_content !== false) {
         $docu=    nl2br($striped_content) ;
        } 
        else 
        {
            echo 'Couldn\'t the file. Please check that file.';
        }

        return $this->render('submission/doc.html.twig', [
            'document' => $docu,
            // 'document' => $striped_content,
            'result' => $result,
            'count' => $count,
            'submission' => $submission,
        ]);

    }

    
}
