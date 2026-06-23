<?php
switch ($action) {
        case 'addThemaG':
            $exFm->displayThemaGForm();
            break;

        case 'saveThemaG':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $success = $db->insertThemaG($_POST, $_FILES);
                echo $success ? "<div class='alert alert-success'>Επιτυχής αποθήκευση!</div>" : "<div class='alert alert-danger'>Αποτυχία.</div>";
            }
            $exFm->displayThemaGForm();
            break;

        case 'listThemaG':
            $res = $db->getAllThemataG();
            $exFm->listThemataG($res);
            break;

        case 'deleteThemaG':
            if (isset($_GET['id'])) {
                $db->deleteThemaG($_GET['id']);
            }
            echo "<script>window.location.href='index.php?action=listThemaG';</script>";
            exit();
            break;
}
