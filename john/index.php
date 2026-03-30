<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Προσαρμοσμένο autoload για να βρίσκει κλάσεις και στον πάνω φάκελο
function __autoload($name)
{
    if (file_exists($name . '.php')) {
        include_once $name . '.php';
    } else if (file_exists('../' . $name . '.php')) {
        include_once '../' . $name . '.php';
    }
}

$page = new AdminPageMaker();
$db = new AdminDbHandler();
$fm = new AdminFormMaker();

$page->displayHeadMatter();
$page->displayMenu();

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

switch ($action) {
    case 'save_theory':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $success = $db->insertTheoryItem(
                $_POST['book_id'],
                $_POST['chapter_num'],
                $_POST['question_text'],
                $_POST['answer_text'],
                $_POST['page_number'],
                $_FILES['q_image'], // Προσθήκη αρχείου ερώτησης
                $_FILES['a_image']  // Προσθήκη αρχείου απάντησης
            );
            if ($success) {
                echo "<div class='container mt-2'><div class='alert alert-success'>Η ερώτηση προστέθηκε επιτυχώς!</div></div>";
            }
        }
        // No break - για να ξαναδείξει τη φόρμα
        // No break - θέλουμε να ξαναδείξει τη φόρμα
    case 'add_theory':
        $books = $db->getTheoryBooks();
        $fm->addTheoryForm($books);
        break;

    // Πρόσθεσε αυτά τα cases στο switch σου
    case 'manage_books':
        $books = $db->getTheoryBooks();
        $fm->manageBooksForm($books);
        break;

    case 'save_book':
        if (isset($_POST['book_title'])) {
            $db->insertBook($_POST['book_title']);
            header("Location: index.php?action=manage_books"); // Refresh για να φανεί το νέο
        }
        break;

    case 'delete_book':
        if (isset($_GET['id'])) {
            $db->deleteBook($_GET['id']);
            header("Location: index.php?action=manage_books");
        }
        break;




    case 'edit_theory':
        if (isset($_GET['id'])) {
            $questionData = $db->getQuestionById($_GET['id']);
            $books = $db->getTheoryBooks();
            $fm->editTheoryForm($questionData, $books);
        }
        break;

    case 'update_theory':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db->updateTheoryItem(
                $_POST['id'],
                $_POST['book_id'],
                $_POST['chapter_num'],
                $_POST['question_text'],
                $_POST['answer_text'],
                $_POST['page_number'],
                $_FILES['q_image'], // Προσθήκη αρχείου ερώτησης
                $_FILES['a_image']  // Προσθήκη αρχείου απάντησης
            );
            header("Location: index.php?action=list_theory");
        }
        break;

    case 'delete_theory':
        if (isset($_GET['id'])) {
            $db->deleteTheoryQuestion($_GET['id']);
            header("Location: index.php?action=list_theory");
        }
        break;

    case 'list_theory':
        // 1. Παίρνουμε τα δεδομένα από την AdminDbHandler
        $questions = $db->getAllTheoryQuestions();
        // 2. Τα στέλνουμε στην AdminFormMaker για να φτιάξει τον πίνακα
        $fm->listTheoryQuestions($questions);
        break;

    case 'list_for_test':
        $questions = $db->getAllQuestionsOrdered(); // Χρησιμοποιούμε την ήδη υπάρχουσα μέθοδο
        $fm->listTheoryQuestionsForTests($questions);
        break;

    case 'create_exam':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['selected_questions'])) {
            $selectedIds = $_POST['selected_questions'];
            $examQuestions = $db->getMultipleQuestionsByIds($selectedIds);
            $fm->previewExam($examQuestions);
        } else {
            echo "<div class='container mt-5'><div class='alert alert-warning'>Δεν επιλέξατε ερωτήσεις!</div></div>";
        }
        break;

    case 'addKena':
        $fm->displayKenaForm();
        break;

    case 'saveKena':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $success = $db->handleKenaUpload($_POST, $_FILES);
?>
            <div class="container mt-3">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Επιτυχία!</strong> Η άσκηση αποθηκεύτηκε και η εικόνα ανέβηκε.
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Σφάλμα!</strong> Η αποθήκευση απέτυχε. Ελέγξτε αν ο φάκελος images/themata/kenaNew υπάρχει και έχει δικαιώματα εγγραφής.
                    </div>
                <?php endif; ?>
            </div>
<?php
        }
        $fm->displayKenaForm();
        break;

    case 'listKena':
        $result = $db->getAllKena();
        $fm->listKenaExercises($result);
        break;

    case 'deleteKena':
        if (isset($_GET['id'])) {
            $db->deleteKena($_GET['id']);
            header("Location: index.php?action=listKena");
        }
        break;

    case 'addThemaG':
        $fm->displayThemaGForm();
        break;

    case 'saveThemaG':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $success = $db->insertThemaG($_POST, $_FILES);
            echo $success ? "<div class='alert alert-success'>Επιτυχής αποθήκευση!</div>" : "<div class='alert alert-danger'>Αποτυχία.</div>";
        }
        $fm->displayThemaGForm();
        break;

    case 'listThemaG':
        $res = $db->getAllThemataG();
        $fm->listThemataG($res);
        break;

    case 'deleteThemaG':
        if (isset($_GET['id'])) {
            $db->deleteThemaG($_GET['id']);
        }
        header("Location: index.php?action=listThemaG");
        break;

    case 'saveMezedaki':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $success = $db->insertMezedaki($_POST, $_FILES);
            if ($success) {
                echo "<div class='container mt-2'><div class='alert alert-success shadow'>Το Μεζεδάκι #" . $_POST['mezeNumber'] . " αποθηκεύτηκε επιτυχώς!</div></div>";
            }
        }
        $fm->addMezedakiForm();
        break;

    case 'addMezedaki':
        $fm->addMezedakiForm();
        break;

    case 'listMezedakia':
        $result = $db->getAllMezedakia();
        $fm->listMezedakia($result); // Η μέθοδος με τον πίνακα που φτιάξαμε
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
            $fm->editMezedakiForm($result->fetch_assoc());
        }
        break;

    case 'updateMezedaki':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ορίζουμε το $success για να ξέρει το if τι να ελέγξει
            $success = $db->updateMezedaki($_POST, $_FILES);

            if ($success) {
                // Χρήση JavaScript αντί για header για να αποφύγουμε το Warning
                echo "<script>window.location.href='index.php?action=listMezedakia';</script>";
                exit();
            }
            // Αν αποτύχει, θα βγει από το switch και θα συνεχίσει η ροή της σελίδας
        }
        break;
    case 'manageGrades':
        if (isset($_GET['id'])) {
            // Χρήση του tutor_user από το session
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

            $students = $db->getTutorStudents($userYear);
            $displayNumber = $db->getMezeNumberById($_GET['id']);
            $existingGrades = $db->getGradesForMeze($_GET['id']);

            if ($students) {
                $fm->showGradesForm($students, $_GET['id'], $displayNumber, $existingGrades);
            } else {
                echo "<div class='container mt-3'><div class='alert alert-warning'>Παρακαλώ ορίστε πρώτα το 'Username Tutor' πάνω δεξιά (π.χ. jhouv2026).</div></div>";
            }
        }
        break;

    case 'saveGrades':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $mezeId = $_POST['meze_id'];
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $count = 0;
            foreach ($_POST['grades'] as $studentId => $grade) {
                if ($grade !== '') { // Αποθηκεύουμε μόνο αν έχει μπει βαθμός
                    $db->saveMezeGrade($studentId, $mezeId, $grade, $userYear);
                    $count++;
                }
            }
            echo "<div class='container mt-3'><div class='alert alert-success shadow'>Επιτυχής αποθήκευση $count βαθμολογιών!</div></div>";
            // Επιστροφή στη λίστα
            $result = $db->getAllMezedakia();
            $fm->listMezedakia($result);
        }
        break;
    case 'setYear':
        if (isset($_POST['tutor_user'])) {
            $_SESSION['tutor_user'] = $_POST['tutor_user'];
            echo "<div class='container mt-2'><div class='alert alert-info'>Το έτος εργασίας ορίστηκε σε: " . $_SESSION['tutor_user'] . "</div></div>";
        }
        // Επιστροφή στο dashboard
        $fm->listMezedakia($db->getAllMezedakia());
        break;
    case 'fullReport':
        $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
        $students = $db->getTutorStudents($userYear);
        $gradesReport = $db->getFullGradesReport($userYear);
        $fm->showFullGradesTable($students, $gradesReport);
        break;
    case 'studentReport':
        if (isset($_GET['studentId']) && isset($_GET['name'])) {
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $grades = $db->getStudentGrades($_GET['studentId'], $userYear);
            $fm->showStudentReport($_GET['name'], $grades);
        }
        break;

    case 'deleteSpecificGrade':
        if (isset($_GET['studentId']) && isset($_GET['mezeId'])) {
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $db->deleteSpecificGrade($_GET['studentId'], $_GET['mezeId'], $userYear);

            // Αντί για header, χρησιμοποιούμε JavaScript για να γυρίσουμε πίσω
            echo "<script>window.location.href='index.php?action=manageGrades&id=" . $_GET['mezeId'] . "';</script>";
            exit();
        }
        break;

    case 'viewSubmissions':
        if (isset($_GET['id'])) {
            // 1. Παίρνουμε τα στοιχεία του μεζέ
            $mezeRes = $db->getMezedakiById($_GET['id']);
            $meze = $mezeRes->fetch_assoc();

            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

            // 2. Παίρνουμε τους μαθητές (Επιστρέφει έτοιμο Array από την AdminDbHandler)
            $studentsList = $db->getTutorStudents($userYear);

            // 3. Παίρνουμε τις υποβολές
            $submissions = $db->getSubmissionsByMeze($_GET['id']);

            // 4. Εμφάνιση της σελίδας (Προσοχή: περνάμε το $studentsList)
            $fm->showSubmissionsForGrading($submissions, $studentsList, $meze['mezeNumber']);
        }
        break;
    case 'quickGrade':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $studentId = $_POST['student_id'];
            $mezeId = $_POST['meze_id'];
            $grade = $_POST['grade'];
            $comments = isset($_POST['teacher_comments']) ? $_POST['teacher_comments'] : "";
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

            // 1. Αποθήκευση
            $db->updateOrInsertGrade($studentId, $mezeId, $grade, $userYear, $comments);

            // 2. Λήψη δεδομένων για το Report
            $avg = $db->getStudentAverage($studentId, $userYear);
            $mezeNum = $db->getMezeNumberById($mezeId);

            // Εύρεση ονόματος (χρησιμοποιούμε την getTutorStudents που έχουμε ήδη)
            $students = $db->getTutorStudents($userYear);
            $studentName = "";
            foreach ($students as $s) {
                if ($s['studentId'] == $studentId) $studentName = $s['name'] . " " . $s['lastName'];
            }

            // 3. Εμφάνιση του Report αντί για alert
            $fm->showPrintableReport($studentName, $mezeNum, $grade, $comments, $avg);
        }
        break;

    default:
        echo "<div class='container mt-5'><h3>Καλωσήρθες, Γιάννη.</h3><p>Επίλεξε μια ενέργεια από το μενού.</p></div>";
        break;
}

$page->displayEndMatter();
