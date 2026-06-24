<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($name) {
    if (file_exists($name . '.php')) {
        include_once $name . '.php';
    }
});

// Έλεγχος login πριν από οποιοδήποτε output
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
if ($action === 'viewMezedakia' && !isset($_SESSION['student_id'])) {
    header("Location: index.php?action=myGrades&returnTo=viewMezedakia");
    exit();
}

$page   = new PageMaker();
$theory = new TheoryMaker();
$fm     = new FormMaker();
$db     = new DbHandler();
$page->displayHeadMatter();
$page->displayMenu();

// --- ΕΛΕΓΧΟΣ ΚΑΙ ΕΜΦΑΝΙΣΗ ΓΕΝΕΘΛΙΩΝ ---
$currentYear = $db->getCurrentTutorYear();
$students    = $db->getTutorStudents($currentYear);
$birthdayStudents = [];
$todayMD = date('m-d');
if (is_array($students)) {
    foreach ($students as $student) {
        if (!empty($student['birthday']) && $student['birthday'] !== '0000-00-00' && $student['birthday'] !== '-') {
            if (date('m-d', strtotime($student['birthday'])) === $todayMD) {
                $birthdayStudents[] = $student['name'] . ' ' . $student['lastName'];
                $db->sendBirthdayEmailIfNeeded($student);
            }
        }
    }
}
if (!empty($birthdayStudents)) {
    $names = implode(', ', $birthdayStudents);
    echo "<div class='container mt-3'><div class='alert alert-warning text-center shadow-sm border-warning' style='border-radius: 15px;'><i class='fa fa-birthday-cake text-danger fa-2x align-middle me-3' style='animation: pulse-sos 2s infinite;'></i><span class='align-middle fs-5'>Χρόνια πολλά! Σήμερα έχει γενέθλια: <strong>{$names}</strong>! 🎉 🎈</span></div></div>";
}
// --- ΤΕΛΟΣ ΕΛΕΓΧΟΥ ΓΕΝΕΘΛΙΩΝ ---

$actionMap = [
    'listKenaDynamic'       => 'actions/content/kena.php',
    'showThemaGDForm'       => 'actions/content/thema_gd.php',
    'viewThemaGD'           => 'actions/content/thema_gd.php',
    'viewMezedakia'         => 'actions/meze/view.php',
    'submitMezeAnswer'      => 'actions/meze/submit.php',
    'requestExtension'      => 'actions/meze/extension.php',
    'forgotPassword'        => 'actions/student/password.php',
    'processForgotPassword' => 'actions/student/password.php',
    'myGrades'              => 'actions/student/my_grades.php',
    'showMyGrades'          => 'actions/student/show_grades.php',
    'announcements'         => 'actions/content/announcements.php',
];

$actionFile = isset($actionMap[$action]) ? $actionMap[$action] : 'actions/home.php';
require $actionFile;

$page->displayEndMatter();
