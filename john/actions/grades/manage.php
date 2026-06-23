<?php
switch ($action) {
        case 'manageGrades':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();

                $students = $db->getTutorStudents($userYear);

                // Φέρνουμε τους βαθμούς
                $gradesList = $db->getGradesForMeze($mezeId);

                // ΜΕΤΑΤΡΟΠΗ: Φτιάχνουμε ένα array όπου το κλειδί είναι το student_id
                $indexedGrades = [];
                if (!empty($gradesList)) {
                    foreach ($gradesList as $g) {
                        // Χρησιμοποιούμε το student_id ως κλειδί
                        $indexedGrades[$g['student_id']] = $g['grade_value'];
                    }
                }

                $mezeFm->showGradesForm($students, $mezeId, $meze['mezeNumber'], $indexedGrades);
            }
            break;

        case 'saveGrades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $mezeId = $_POST['meze_id'];
                $count = 0;

                if (!isset($_POST['grades']) || !is_array($_POST['grades'])) {
                    $_POST['grades'] = [];
                }

                foreach ($_POST['grades'] as $studentId => $grade) {
                    if ($grade !== '') { // Αποθηκεύουμε μόνο αν έχει μπει βαθμός
                        $db->saveMezeGrade($studentId, $mezeId, $grade, $userYear);
                        $count++;
                    }
                }
                echo "<div class='container mt-3'><div class='alert alert-success shadow'>Επιτυχής αποθήκευση $count βαθμολογιών!</div></div>";
                // Επιστροφή στη λίστα (χρησιμοποιούμε getAllMezedakiaForAdmin για την admin view)
                $result = $db->getAllMezedakiaForAdmin();
                $mezeFm->listMezedakia($result, $db);
            }
            break;
}
