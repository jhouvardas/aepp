<?php
switch ($action) {
        case 'addKena':
            $exFm->displayKenaForm();
            break;

        case 'saveKena':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $success = $db->handleKenaUpload($_POST, $_FILES);
    ?>
                <div class="container mt-3">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <strong>Επιτυχία!</strong> Η άσκηση αποθηκεύτηκε και η εικόνα ανέβηκε.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <strong>Σφάλμα!</strong> Η αποθήκευση απέτυχε. Ελέγξτε αν ο φάκελος images/themata/kenaNew υπάρχει και έχει δικαιώματα εγγραφής.
                        </div>
                    <?php endif; ?>
                </div>
            <?php
            }
            $exFm->displayKenaForm();
            break;

        case 'listKena':
            $result = $db->getAllKena();
            $exFm->listKenaExercises($result);
            break;

        case 'deleteKena':
            if (isset($_GET['id'])) {
                $db->deleteKena($_GET['id']);
                echo "<script>window.location.href='index.php?action=listKena';</script>";
                exit();
            }
            break;
}
