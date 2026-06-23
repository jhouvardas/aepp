<?php
switch ($action) {
        case 'giveExtension':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stId = $_POST['student_id'];
                $mId = $_POST['meze_id'];
                $hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 24;
                if ($db->hasExtension($stId, $mId, $userYear)) {
                    $db->removeLateSubmission($stId, $mId, $userYear);
                } else {
                    $db->allowLateSubmission($stId, $mId, $userYear, $hours);
                }
            ?>
                <script>
                    window.location.href = 'index.php?action=viewSubmissions&id=<?php echo $_POST['meze_id']; ?>';
                </script>
            <?php
                exit();
            }
            break;

        case 'extendMezeForAll':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $mId = $_POST['meze_id'];
                if ($db->hasGlobalExtension($mId, $userYear)) {
                    $db->removeGlobalExtension($mId, $userYear);
                } else {
                    $hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 24;
                    $db->extendMezeForAll($mId, $hours, $userYear);
                }
            ?>
                <script>
                    window.location.href = 'index.php?action=listMezedakia';
                </script>
    <?php
                exit();
            }
            break;

        case 'setMezeToday':
            if (isset($_GET['id'])) {
                $db->setMezeDateToday($_GET['id']);
                $mezeId = (int)$_GET['id'];
                if (isset($_GET['source']) && $_GET['source'] === 'edit_page') {
                    echo "<script>window.location.href='index.php?action=editMezedaki&id=$mezeId&status=meze_set_today';</script>";
                } else {
                    echo "<script>window.location.href='index.php?action=listMezedakia&status=meze_set_today';</script>";
                }
            }
            exit();
            break;

        case 'toggleGroupDeadline':
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['meze_id']) && isset($_POST['group_id'])) {
                $db->toggleGroupDeadline($_POST['meze_id'], $_POST['group_id']);
                $mezeId = (int)$_POST['meze_id'];
                if (isset($_POST['source']) && $_POST['source'] === 'edit_page') {
                    echo "<script>window.location.href='index.php?action=editMezedaki&id=$mezeId&status=group_deadline_toggled';</script>";
                } else {
                    echo "<script>window.location.href='index.php?action=listMezedakia';</script>";
                }
            }
            exit();
            break;





        case 'view_extension_requests':
            $requests = $db->getPendingExtensionRequests($userYear);
            $students = $db->getTutorStudents($userYear);
            $mezeFm->listExtensionRequests($requests, $students);
            break;

        case 'processExtension':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $requestId = $_POST['request_id'];
                $stId = $_POST['student_id'];
                $mId = $_POST['meze_id'];
                $hours = $_POST['hours'];
                $approve = isset($_POST['approve']);

                $success = $db->processExtensionRequest($requestId, $stId, $mId, $hours, $userYear, $approve);

                // Αυτόματη αποστολή email ειδοποίησης για το αποτέλεσμα του αιτήματος
                if ($success) {
                    $students = $db->getTutorStudents($userYear);
                    $studentKey = array_search($stId, array_column($students, 'studentId'));
                    $student = ($studentKey !== false) ? $students[$studentKey] : null;
                    $mezeNum = $db->getMezeNumberById($mId);

                    if ($student && !empty($student['email'])) {
                        if ($approve) {
                            $subject = "Έγκριση Παράτασης: Μεζεδάκι #$mezeNum";
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #28a745;'>Έγκριση Παράτασης!</h2>
                                    <p>Γεια σου <b>" . $student['name'] . "</b>,</p>
                                    <p>Το αίτημά σου για παράταση στο <b>Μεζεδάκι #$mezeNum</b> εγκρίθηκε από τον δάσκαλο.</p>
                                    <p>Έχεις πλέον <b>$hours επιπλέον ώρες</b> από τώρα για να υποβάλεις τη λύση σου στην πλατφόρμα.</p>
                                    <p><a href='http://jhouv.eu/aepp/index.php?action=viewMezedakia' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Μετάβαση στα Μεζεδάκια</a></p>
                                <p>Καλή συνέχεια και καλή μελέτη,<br><b>Ο Δάσκαλος</b></p>
                                </div>";
                        } else {
                            $subject = "Ενημέρωση Αιτήματος Παράτασης: Μεζεδάκι #$mezeNum";
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #dc3545;'>Απόρριψη Αιτήματος Παράτασης</h2>
                                    <p>Γεια σου <b>" . $student['name'] . "</b>,</p>
                                    <p>Σε ενημερώνουμε ότι το αίτημά σου για παράταση στο <b>Μεζεδάκι #$mezeNum</b> δεν έγινε δεκτό από τον δάσκαλο.</p>
                                    <p>Μπορείς να επικοινωνήσεις με τον δάσκαλό σου για οποιαδήποτε διευκρίνιση.</p>
                                <p>Καλή συνέχεια,<br><b>Ο Δάσκαλος</b></p>
                                </div>";
                        }
                        $db->sendSystemEmail($student['email'], $subject, $body);
                    }
                }
                $status = $approve ? 'ext_approved' : 'ext_rejected';
                echo "<script>window.location.href='index.php?action=view_extension_requests&status=$status';</script>";
            }
            break;

        case 'toggleMezeLock':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $db->toggleMezeLock($_GET['id'], $_GET['status']);
                echo "<script>window.location.href='index.php?action=listMezedakia';</script>";
                exit();
            }
            break;
}
