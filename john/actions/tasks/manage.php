<?php
switch ($action) {
        case 'grade_task':
            if (isset($_GET['task_id'])) {
                $task = $db->getTaskById($_GET['task_id']);
                $students = $db->getStudentsByGroupId($task['group_id'], $userYear);
                $grades = $db->getTaskGrades($_GET['task_id']);
                $reportFm->showTaskGradesForm($task, $students, $grades);
            }
            break;

        case 'save_task_grades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $taskId = $_POST['task_id'];
                $sendEmails = isset($_POST['send_emails']) && $_POST['send_emails'] == '1';

                $task = $db->getTaskById($taskId);
                $taskName = $task ? mb_substr(strip_tags($task['task_text']), 0, 60) . "..." : "Ομαδική Εργασία";
                $students = $db->getTutorStudents($userYear);

                // Φέρνουμε τους υπάρχοντες βαθμούς για να ελέγξουμε αν υπήρξε αλλαγή
                $existingGrades = $db->getTaskGrades($taskId);
                $emailCount = 0;

                if (!isset($_POST['grades']) || !is_array($_POST['grades'])) {
                    $_POST['grades'] = [];
                }

                foreach ($_POST['grades'] as $stId => $grade) {
                    $comment = $_POST['comments'][$stId] ?? '';
                    if ($grade !== "") {
                        $oldGrade = isset($existingGrades[$stId]['grade_value']) ? $existingGrades[$stId]['grade_value'] : null;
                        $oldComment = isset($existingGrades[$stId]['teacher_comments']) ? $existingGrades[$stId]['teacher_comments'] : '';
                        $isChanged = ($oldGrade != $grade || $oldComment != $comment);

                        $db->saveTaskGrade($taskId, $stId, $grade, $comment);

                        if ($sendEmails && $isChanged) {
                            $studentKey = array_search($stId, array_column($students, 'studentId'));
                            $student = ($studentKey !== false) ? $students[$studentKey] : null;

                            if ($student && !empty($student['email'])) {
                                $subject = "Ενημέρωση Βαθμολογίας: Ομαδική Εργασία";
                                $body = "
                                    <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                        <h2 style='color:#007bff;'>Ενημέρωση Βαθμολογίας</h2>
                                        <p>Γεια σου <b>{$student['name']}</b>,</p>
                                        <p>Η βαθμολογία σου για την εργασία <i>\"$taskName\"</i> καταχωρήθηκε.</p>
                                        <div style='background:#f8f9fa; padding:15px; border-radius:5px; margin:15px 0;'>
                                            <p style='margin:5px 0;'><b>Βαθμός:</b> <span style='color:#dc3545; font-size:1.2em;'>$grade/20</span></p>
                                            <div style='margin:5px 0;'><b>Σχόλια:</b><br><div style='white-space: pre-wrap; font-style: italic; padding-top: 5px;'>$comment</div></div>
                                        </div>
                                        <p>Καλή συνέχεια!</p>
                                    </div>";
                                if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                                    $emailCount++;
                                }
                            }
                        }
                    }
                }

                $alertMsg = $sendEmails ? "Επιτυχής αποθήκευση! Στάλθηκαν $emailCount emails ενημέρωσης." : "Επιτυχής αποθήκευση!";
                echo "<script>alert('$alertMsg'); window.location.href='index.php?action=list_all_tasks';</script>";
            }
            break;
}
