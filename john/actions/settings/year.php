<?php
switch ($action) {
        case 'setYear':
            if (isset($_POST['exam_year'])) {
                $_SESSION['exam_year'] = trim($_POST['exam_year']);
                $_SESSION['tutor_user'] = trim($_POST['exam_year']); // Για συμβατότητα με το παλιό
                echo "<div class='container mt-2'><div class='alert alert-info shadow-sm'><i class='fa fa-calendar-check-o'></i> Το σχολικό έτος (εξετάσεων) ορίστηκε σε: <b>" . $_SESSION['exam_year'] . "</b></div></div>";
            } elseif (isset($_POST['tutor_user'])) { // Fallback
                $_SESSION['tutor_user'] = trim($_POST['tutor_user']);
                $_SESSION['exam_year'] = trim($_POST['tutor_user']);
                echo "<div class='container mt-2'><div class='alert alert-info shadow-sm'><i class='fa fa-calendar-check-o'></i> Το σχολικό έτος (εξετάσεων) ορίστηκε σε: <b>" . $_SESSION['tutor_user'] . "</b></div></div>";
            }
            // Επιστροφή στο dashboard
            $mezeFm->listMezedakia($db->getAllMezedakiaForAdmin(), $db);
            break;
}
