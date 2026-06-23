<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'];
    $mezeId    = $_POST['meze_id'];
    $hours     = (int)$_POST['requested_hours'];

    $overallAverage = $db->getStudentOverallAverage($studentId, $currentYear);
    $grades         = $db->getStudentGradesForStudent($studentId, $currentYear);
    $tempOnTime = 0;
    if (is_array($grades)) {
        foreach ($grades as $g) {
            if ($g['is_on_time']) $tempOnTime++;
        }
    }
    $delaysCount = (is_array($grades) ? count($grades) : 0) - $tempOnTime;

    if ($overallAverage > 15 && $delaysCount <= 5) {
        $db->submitExtensionRequest($studentId, $mezeId, $hours, $currentYear);
        $page->displayRequestSuccess();
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Δεν πληροίτε τις προϋποθέσεις για αίτημα παράτασης (Μ.Ο. > 15 και έως 5 καθυστερήσεις).</div></div>";
    }
    exit();
}
