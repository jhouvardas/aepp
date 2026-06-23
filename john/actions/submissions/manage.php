<?php
switch ($action) {
        case 'viewSubmissions':
            if (isset($_GET['id'])) {
                $mezeRes = $db->getMezedakiById($_GET['id']);
                $meze = $mezeRes->fetch_assoc();

                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($_GET['id']);

                // Εδώ η getGradesForMeze σου επιστρέφει πλέον ARRAY, οπότε:
                $existingGrades = $db->getGradesForMeze($_GET['id']);

                // Φέρνουμε και τα μεζεδάκια για την ταυτοποίηση Number -> ID
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }

                // Στέλνουμε και τις 5 παραμέτρους
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'massGradeZero':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $count = $db->massGradeZeroForMeze($mezeId, $userYear);
                echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Ολοκληρώθηκε! <b>$count</b> μαθητές που δεν παρέδωσαν βαθμολογήθηκαν με 0.</div></div>";

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'massEmailZero':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $studentsToEmail = $db->getStudentsWithZeroGrade($mezeId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                if (empty($studentsToEmail)) {
                    echo "<div class='container mt-3'><div class='alert alert-warning shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Δεν βρέθηκαν μαθητές με βαθμό 0 και email.</div></div>";
                } else {
                    $successCount = 0;
                    foreach ($studentsToEmail as $student) {
                        $subject = "Ενημέρωση Βαθμολογίας: Μεζεδάκι #$mezeNum";
                        $body = "
                                <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                    <h2 style='color:#dc3545;'>Ενημέρωση Βαθμολογίας (ΑΕΠΠ)</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <p>Σε ενημερώνουμε ότι στο <b>Μεζεδάκι #$mezeNum</b> η βαθμολογία σου είναι <b>0/20</b> λόγω μη έγκαιρης υποβολής.</p>
                                    <p>Αν επιθυμείς να υποβάλεις τη λύση σου τώρα για βελτίωση ή εκπρόθεσμα, επικοινώνησε με τον δάσκαλό σου ή ζήτησε παράταση μέσω της πλατφόρμας.</p>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                        if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                            $successCount++;
                        }
                    }
                    echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Ολοκληρώθηκε! Στάλθηκαν $successCount emails ενημέρωσης.</div></div>";
                }

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'sendReminder':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $studentsToEmail = $db->getStudentsWithoutSubmission($mezeId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $deadline = date('d/m/Y H:i', strtotime($meze['solutionDate']));

                if (empty($studentsToEmail)) {
                    echo "<script>alert('Όλοι οι μαθητές έχουν ήδη υποβάλει λύση ή δεν έχουν δηλωμένο email!'); window.location.href='index.php?action=listMezedakia';</script>";
                } else {
                    $successCount = 0;
                    foreach ($studentsToEmail as $student) {
                        $subject = "⏳ Υπενθύμιση: Λήξη προθεσμίας - Μεζεδάκι #$mezeNum";
                        $body = "
                                <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                    <h2 style='color:#ffc107;'>⏳ Υπενθύμιση Προθεσμίας</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <p>Σε ειδοποιούμε ότι η προθεσμία υποβολής για το <b>Μεζεδάκι #$mezeNum</b> λήγει στις <b>$deadline</b> και δεν έχουμε λάβει ακόμα την απάντησή σου.</p>
                                    <p>Παρακαλούμε συνδέσου στην πλατφόρμα και ολοκλήρωσε την άσκηση εγκαίρως.</p>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                        if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                            $successCount++;
                        }
                    }
                    echo "<script>alert('Ολοκληρώθηκε! Στάλθηκαν $successCount emails υπενθύμισης.'); window.location.href='index.php?action=listMezedakia';</script>";
                }
            }
            break;

        case 'quickGrade':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $studentId = $_POST['student_id'];
                $mezeId = $_POST['meze_id'];
                $grade = $_POST['grade'];
                $comments = isset($_POST['teacher_comments']) ? $_POST['teacher_comments'] : "";

                // 1. Αποθήκευση ή Ενημέρωση του βαθμού και των σχολίων
                $db->updateOrInsertGrade($studentId, $mezeId, $grade, $userYear, $comments);

                // 2. Υπολογισμός Μέσου Όρου (περιλαμβάνει και τον νέο βαθμό)
                $avg = $db->getStudentAverage($studentId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                // 3. Εύρεση ονόματος μαθητή για την αναφορά
                $students = $db->getTutorStudents($userYear);
                $student = null;
                foreach ($students as $s) {
                    if ($s['studentId'] == $studentId) {
                        $student = $s;
                        break;
                    }
                }
                $studentName = $student ? $student['name'] . " " . $student['lastName'] : "Μαθητής";

                // 4. Εμφάνιση της εκτυπώσιμης αναφοράς (PDF Report)
                $mezeFm->showPrintableReport($studentName, $mezeNum, $grade, $comments, $avg, $mezeId, $student);
            }
            break;

        case 'sendReportEmail':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $to = $_POST['email'];
                $subject = $_POST['subject'];
                $htmlContent = htmlspecialchars_decode($_POST['html_message']);
                $mezeId = $_POST['meze_id'];

                $replyTo = defined('SMTP_REPLY_TO') ? SMTP_REPLY_TO : null;
                $result = $db->sendSystemEmail($to, $subject, $htmlContent, $replyTo);

                if ($result === true) {
                    echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>✅ Το Email στάλθηκε επιτυχώς μέσω Gmail SMTP!</div></div>";
                } else {
                    $errorMsg = addslashes($result);
                    echo "<div class='container mt-3'><div class='alert alert-danger shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>❌ Η αποστολή απέτυχε. Σφάλμα: $errorMsg</div></div>";
                }

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;
}
