<?php
if ($action === 'viewThemaGD' && isset($_POST['viewThemaGD'])) {
    $year   = $_POST['year'];
    $school = $_POST['typosSxoleiou'];
    $period = $_POST['typosEksetaseon'];
    $type   = $_POST['thema_type'];
    $result = $db->getThemaGDByCriteria($year, $school, $period, $type);
    $fm->getThemataGDForm();
    $page->displayThemaGD($result);
} else {
    $fm->getThemataGDForm();
}
