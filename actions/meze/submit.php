<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;

    if (!$studentId) {
        echo "<script>alert('Η σύνδεσή σου έχει λήξει. Παρακαλώ κάνε login ξανά.'); window.location.href='index.php?action=myGrades&returnTo=viewMezedakia';</script>";
        exit();
    }

    $mezeId = $_POST['meze_id'];
    $text   = $_POST['student_text'];

    if (!empty($_POST['blanks_answers'])) {
        $text .= "<br><br><div style='background:#f8f9fa; padding:10px; border-left:3px solid #17a2b8; border-radius:4px;'><b>Απαντήσεις στα κενά:</b><br>" . nl2br(htmlspecialchars($_POST['blanks_answers'])) . "</div>";
    }

    $success = $db->saveMezeSubmission($studentId, $mezeId, $text, $_FILES['files']);
    if ($success) {
        $mezeData = $db->getMezeSolution((int)$mezeId);
        $page->displayMezeSuccessWithSolution($mezeData ?: []);
        exit();
    } elseif ($db->hasStudentSubmitted($studentId, (int)$mezeId)) {
        echo "<div class='container mt-5'><div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> Έχεις ήδη υποβάλει λύση για αυτό το μεζεδάκι.</div></div>";
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Κάτι πήγε στραβά στην αποθήκευση. Παρακαλώ επικοινώνησε με τον δάσκαλο.</div></div>";
    }
    exit();
}
