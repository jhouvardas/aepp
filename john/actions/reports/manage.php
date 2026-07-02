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

                    // Σχολές Προτίμησης
                    $db->ensureSchoolsTables();
                    $schoolYears = $db->getSchoolYears();
                    if (!empty($schoolYears)) {
                        $prefs = $db->getStudentPreferences((int)$studentId);
                        ?>
                        <div class="container mt-3 mb-4 d-print-none">
                            <div class="card shadow-sm border-info">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-university"></i> Σχολές Προτίμησης (<?php echo $schoolYears[0]; ?>)</span>
                                    <span class="badge bg-white text-info"><?php echo count($prefs); ?> / 10</span>
                                </div>
                                <?php if (empty($prefs)): ?>
                                    <div class="card-body text-muted"><i class="fa fa-info-circle"></i> Δεν έχει επιλέξει σχολές ακόμα.</div>
                                <?php else: ?>
                                    <div class="card-body p-0">
                                        <ol class="list-group list-group-numbered list-group-flush">
                                            <?php foreach ($prefs as $p): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                                    <div class="ms-2">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($p['department']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($p['university']); ?><?php if ($p['city']): ?> &middot; <?php echo htmlspecialchars($p['city']); ?><?php endif; ?></small>
                                                    </div>
                                                    <span class="badge bg-warning text-dark ms-2"><?php echo htmlspecialchars($p['base_points']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
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
