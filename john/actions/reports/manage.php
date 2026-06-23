<?php
switch ($action) {
        case 'fullReport':
            $students = $db->getTutorStudents($userYear);
            $gradesReport = $db->getFullGradesReport($userYear);
            $reportFm->showFullGradesTable($students, $gradesReport);
            break;
        case 'studentReport':
        case 'viewStudentProfile':
            $students = $db->getTutorStudents($userYear);

            if (isset($_GET['studentId'])) {
                $studentId = $_GET['studentId'];

                // Βελτίωση: Αναζήτηση απευθείας στον πίνακα αντί για loop στην PHP
                $studentKey = array_search($studentId, array_column($students, 'studentId'));
                $studentInfo = ($studentKey !== false) ? $students[$studentKey] : null;

                if ($studentInfo) {
                    $grades = $db->getStudentGradesForStudent($studentId, $userYear);
                    $average = $db->getStudentOverallAverage($studentId, $userYear);
                    $tasks = $db->getStudentGroupTasks($studentId);
                    $financials = $db->getStudentFinancials($studentId);
                    $trend = $db->getStudentPerformanceTrend($studentId, $userYear);
                    $reportFm->showFullStudentProfile($studentInfo, $grades, $tasks, $financials, $average, $trend);
                }
            } else {
                $reportFm->showStudentSelectionList($students);
            }
            break;
        case 'oldStudentReport':
            if (isset($_GET['studentId']) && isset($_GET['name'])) {
                $grades = $db->getStudentGrades($_GET['studentId'], $userYear);
                $reportFm->showStudentReport($_GET['name'], $grades);
            }
            break;

        case 'deleteSpecificGrade':
            if (isset($_GET['studentId']) && isset($_GET['mezeId'])) {
                $db->deleteSpecificGrade($_GET['studentId'], $_GET['mezeId'], $userYear);

                // Αντί για header, χρησιμοποιούμε JavaScript για να γυρίσουμε πίσω
                echo "<script>window.location.href='index.php?action=manageGrades&id=" . $_GET['mezeId'] . "';</script>";
                exit();
            }
            break;
}
