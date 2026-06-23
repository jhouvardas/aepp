<?php
switch ($action) {
        case 'massHideMezedakia':
            $db->massHideOldMezedakia();
            // Ενημέρωση και επιστροφή στη λίστα
            echo "<script>alert('Όλα τα παλιά μεζεδάκια μεταφέρθηκαν στο 2030 και κρύφτηκαν από τους μαθητές!'); window.location.href='index.php?action=listMezedakia';</script>";
            exit();
            break;

        case 'massDeleteSubmissions':
            $db->massDeleteOldSubmissions();
            echo "<script>alert('Όλες οι παλιές υποβολές και τα αρχεία τους διαγράφηκαν επιτυχώς! Το σύστημα ελάφρυνε.'); window.location.href='index.php?action=listMezedakia';</script>";
            exit();
            break;

        case 'saveMezedaki':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $success = $db->insertMezedaki($_POST, $_FILES);
                if ($success) {
                    if (isset($_POST['return_to_list']) && $_POST['return_to_list'] == '1') {
                        echo "<script>window.location.href='index.php?action=listMezedakia&status=update_success';</script>";
                        exit();
                    } else {
                        echo "<div class='container mt-2'><div class='alert alert-success shadow'>Το Μεζεδάκι #" . $_POST['mezeNumber'] . " αποθηκεύτηκε επιτυχώς!</div></div>";
                    }
                }
            }
            $nextNum = $db->getNextMezeNumber();
            $mezeFm->addMezedakiForm([], $nextNum);
            break;

        case 'addMezedaki':
            $nextNum = $db->getNextMezeNumber();
            $mezeFm->addMezedakiForm([], $nextNum);
            break;

        case 'mezeBank':
            $result = $db->getAllMezedakiaForAdmin();
            $mezeFm->mezeBank($result, $db);
            break;

        case 'manage_exercise_types':
            $types = $db->getExerciseTypes();
            $mezeFm->manageExerciseTypesForm($types);
            break;

        case 'save_exercise_type':
            if (isset($_POST['type_name'])) {
                $db->insertExerciseType($_POST['type_name']);
                echo "<script>window.location.href='index.php?action=manage_exercise_types';</script>";
            }
            break;

        case 'delete_exercise_type':
            if (isset($_GET['id'])) {
                $db->deleteExerciseType($_GET['id']);
                echo "<script>window.location.href='index.php?action=manage_exercise_types';</script>";
            }
            break;

        case 'listMezedakia':
            $result = $db->getAllMezedakiaForAdmin();
            $mezeFm->listMezedakia($result, $db); // Περνάμε το $db object
            break;

        case 'deleteMezedaki':
            if (isset($_GET['id'])) {
                // Καλούμε τη διαγραφή
                $db->deleteMezedaki($_GET['id']);

                // Χρησιμοποιούμε JavaScript για το redirect αν το header() αποτύχει 
                // Λύνει το πρόβλημα της λευκής σελίδας 100%
                echo "<script>window.location.href='index.php?action=listMezedakia';</script>";
                exit();
            }
            break;

        case 'editMezedaki':
            if (isset($_GET['id'])) {
                $result = $db->getMezedakiById($_GET['id']);
                $mezeFm->editMezedakiForm($result->fetch_assoc());
            }
            break;

        case 'updateMezedaki':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Ορίζουμε το $success για να ξέρει το if τι να ελέγξει
                $success = $db->updateMezedaki($_POST, $_FILES);

                if ($success) {
                    if (isset($_POST['return_to_list']) && $_POST['return_to_list'] == '1') {
                        // Επιστροφή στη λίστα
                        echo "<script>window.location.href='index.php?action=listMezedakia&status=update_success';</script>";
                    } else {
                        // Επιστροφή στη φόρμα επεξεργασίας
                        echo "<script>window.location.href='index.php?action=editMezedaki&id=" . (int)$_POST['mezeId'] . "&status=update_success';</script>";
                    }
                    exit();
                }
                // Αν αποτύχει, θα βγει από το switch και θα συνεχίσει η ροή της σελίδας
            }
            break;
}
