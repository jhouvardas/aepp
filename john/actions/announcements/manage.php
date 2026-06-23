<?php
switch ($action) {
        case 'manage_announcements':
            $announcements = $db->getAllAnnouncements($userYear);
            $mezeFm->listAnnouncements($announcements);
            break;

        case 'add_announcement':
            $mezeFm->announcementForm();
            break;

        case 'save_announcement':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $db->insertAnnouncement($_POST['title'], $_POST['content'], $_FILES, $userYear);
                echo "<script>window.location.href='index.php?action=manage_announcements';</script>";
                exit();
            }
            break;

        case 'edit_announcement':
            if (isset($_GET['id'])) {
                $announcement = $db->getAnnouncementById($_GET['id']);
                $mezeFm->announcementForm($announcement);
            }
            break;

        case 'update_announcement':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $deleteImage = isset($_POST['deleteImage']) ? "1" : "0";
                $db->updateAnnouncement($_POST['id'], $_POST['title'], $_POST['content'], $_FILES, $deleteImage, $userYear);
                echo "<script>window.location.href='index.php?action=manage_announcements';</script>";
                exit();
            }
            break;

        case 'delete_announcement':
            if (isset($_GET['id'])) {
                $db->deleteAnnouncement($_GET['id']);
                echo "<script>window.location.href='index.php?action=manage_announcements';</script>";
                exit();
            }
            break;

        case 'notify_announcement':
            if (isset($_GET['id'])) {
                $announcement = $db->getAnnouncementById($_GET['id']);
                $students = $db->getTutorStudents($userYear);

                if ($announcement && !empty($students)) {
                    $successCount = 0;
                    foreach ($students as $student) {
                        if (empty($student['email'])) continue;
                        $subject = "Νέα Ανακοίνωση: " . $announcement['title'];
                        $body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                            <h2 style='color: #007bff;'>" . htmlspecialchars($announcement['title']) . "</h2>
                            <p>Γεια σου <b>" . htmlspecialchars($student['name']) . "</b>,</p>
                            <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;'>
                                " . $announcement['content'] . "
                            </div>
                            <p><a href='http://jhouv.eu/aepp/index.php?action=announcements#ann-" . $announcement['id'] . "' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Προβολή Ανακοίνωσης</a></p>
                            <p>Καλή συνέχεια!</p>
                        </div>";
                        if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                            $successCount++;
                        }
                    }
                    echo "<script>alert('Εστάλησαν $successCount emails επιτυχώς!'); window.location.href='index.php?action=manage_announcements';</script>";
                } else {
                    echo "<script>alert('Δεν βρέθηκαν μαθητές με έγκυρο email.'); window.location.href='index.php?action=manage_announcements';</script>";
                }
                exit();
            }
            break;
}
