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
                echo "<script>window.location.href='index.php?action=manage_books';</script>";
                exit();
            }
            break;

        case 'delete_book':
            if (isset($_GET['id'])) {
                $db->deleteBook($_GET['id']);
                echo "<script>window.location.href='index.php?action=manage_books';</script>";
                exit();
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
                echo "<script>window.location.href='index.php?action=list_theory';</script>";
                exit();
            }
            break;

        case 'delete_theory':
            if (isset($_GET['id'])) {
                $db->deleteTheoryQuestion($_GET['id']);
                echo "<script>window.location.href='index.php?action=list_theory';</script>";
                exit();
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
                echo "<script>window.location.href='index.php?action=listKena';</script>";
                exit();
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
            echo "<script>window.location.href='index.php?action=listThemaG';</script>";
            exit();
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
            $result = $db->getAllMezedakiaForAdmin();
            $fm->listMezedakia($result, $db); // Περνάμε το $db object
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

        case 'previewMeze':
            if (isset($_GET['id'])) {
                $res = $db->getMezedakiById($_GET['id']);
                $meze = $res->fetch_assoc();
            ?>
                <!DOCTYPE html>
                <html lang="el">

                <head>
                    <meta charset="UTF-8">
                    <base href="../">
                    <title>Προεπισκόπηση Μεζεδακίου #<?php echo $meze['mezeNumber']; ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                    <link rel="stylesheet" href="aepp.css">
                    <style>
                        body {
                            background-color: #f8f9fa;
                            padding: 20px;
                        }

                        .preview-box {
                            background: white;
                            padding: 30px;
                            border-left: 5px solid #17a2b8;
                            border-bottom: 1px solid #dee2e6;
                            min-height: 100vh;
                        }
                    </style>
                </head>

                <body>
                    <div class="container">
                        <div class="preview-box">
                            <h2 class="border-bottom pb-2 mb-4 text-dark font-weight-bold">Προεπισκόπηση: Μεζεδάκι #<?php echo $meze['mezeNumber']; ?></h2>

                            <!-- Ενότητα Εκφώνησης -->
                            <div class="mb-5">
                                <h4 class="text-primary mb-3"><i class="fa fa-file-text-o"></i> Εκφώνηση</h4>
                                <?php if (!empty($meze['mezeImage'])): ?>
                                    <div class="text-center mb-3">
                                        <img src="images/mezedakia/<?php echo $meze['mezeImage']; ?>" class="img-fluid rounded border shadow-sm" style="max-height: 400px;">
                                    </div>
                                <?php endif; ?>
                                <div class="html-content-wrapper p-3 border rounded bg-white">
                                    <?php echo $meze['mezeText']; ?>
                                </div>
                            </div>

                            <!-- Ενότητα Hints -->
                            <?php if (!empty($meze['mezeHints'])): ?>
                                <div class="mb-5">
                                    <h4 class="text-info mb-3"><i class="fa fa-lightbulb-o"></i> Υποδείξεις / Hints</h4>
                                    <div class="alert alert-info border-info shadow-sm">
                                        <?php echo nl2br($meze['mezeHints']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Ενότητα Λύσης -->
                            <div class="mb-4">
                                <h4 class="text-success mb-3"><i class="fa fa-check-circle"></i> Λύση</h4>
                                <div class="p-3 border border-success rounded bg-light shadow-sm">
                                    <?php if (!empty($meze['mezeSolutionImage'])): ?>
                                        <div class="text-center mb-3">
                                            <img src="images/mezedakia/<?php echo $meze['mezeSolutionImage']; ?>" class="img-fluid rounded border border-success" style="max-height: 400px;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="solution-text">
                                        <?php echo !empty($meze['mezeSolution']) ? $meze['mezeSolution'] : (empty($meze['mezeSolutionImage']) ? '<i class="text-muted">Δεν έχει καταχωρηθεί λύση.</i>' : ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>

                </html>
            <?php
                exit();
            }
            break;

        case 'manageGrades':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();

                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
                $students = $db->getTutorStudents($userYear);

                // Φέρνουμε τους βαθμούς
                $gradesList = $db->getGradesForMeze($mezeId);

                // ΜΕΤΑΤΡΟΠΗ: Φτιάχνουμε ένα array όπου το κλειδί είναι το student_id
                $indexedGrades = [];
                if (!empty($gradesList)) {
                    foreach ($gradesList as $g) {
                        // Χρησιμοποιούμε το student_id ως κλειδί
                        $indexedGrades[$g['student_id']] = $g['grade_value'];
                    }
                }

                $fm->showGradesForm($students, $mezeId, $meze['mezeNumber'], $indexedGrades);
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
                // Επιστροφή στη λίστα (χρησιμοποιούμε getAllMezedakiaForAdmin για την admin view)
                $result = $db->getAllMezedakiaForAdmin();
                $fm->listMezedakia($result, $db);
            }
            break;
        case 'setYear':
            if (isset($_POST['tutor_user'])) {
                $_SESSION['tutor_user'] = $_POST['tutor_user'];
                echo "<div class='container mt-2'><div class='alert alert-info'>Το έτος εργασίας ορίστηκε σε: " . $_SESSION['tutor_user'] . "</div></div>";
            }
            // Επιστροφή στο dashboard
            $fm->listMezedakia($db->getAllMezedakiaForAdmin(), $db);
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
                $mezeRes = $db->getMezedakiById($_GET['id']);
                $meze = $mezeRes->fetch_assoc();

                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
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
                $fm->showSubmissionsForGrading($submissions, $studentsList, $meze['mezeNumber'], $allMezedakia, $existingGrades);
            }
            break;

        case 'quickGrade':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $studentId = $_POST['student_id'];
                $mezeId = $_POST['meze_id'];
                $grade = $_POST['grade'];
                $comments = isset($_POST['teacher_comments']) ? $_POST['teacher_comments'] : "";
                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

                // 1. Αποθήκευση ή Ενημέρωση του βαθμού και των σχολίων
                $db->updateOrInsertGrade($studentId, $mezeId, $grade, $userYear, $comments);

                // 2. Υπολογισμός Μέσου Όρου (περιλαμβάνει και τον νέο βαθμό)
                $avg = $db->getStudentAverage($studentId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                // 3. Εύρεση ονόματος μαθητή για την αναφορά
                $students = $db->getTutorStudents($userYear);
                $studentName = "Μαθητής";
                foreach ($students as $s) {
                    if ($s['studentId'] == $studentId) {
                        $studentName = $s['name'] . " " . $s['lastName'];
                        break;
                    }
                }

                // 4. Εμφάνιση της εκτυπώσιμης αναφοράς (PDF Report)
                $fm->showPrintableReport($studentName, $mezeNum, $grade, $comments, $avg, $mezeId);
            }
            break;

        case 'giveExtension':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $uYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
                $db->allowLateSubmission($_POST['student_id'], $_POST['meze_id'], $uYear);
            ?>
                <script>
                    window.location.href = 'index.php?action=viewSubmissions&id=<?php echo $_POST['meze_id']; ?>';
                </script>
    <?php
                exit();
            }
            break;

        case 'toggleMezeLock':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $db->toggleMezeLock($_GET['id'], $_GET['status']);
                echo "<script>window.location.href='index.php?action=listMezedakia';</script>";
                exit();
            }
            break;

        case 'manage_groups':
            $uYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $groups = $db->getGroups($uYear);
            $students = $db->getTutorStudents($uYear);
            $assignments = $db->getAssignedStudents();
            $fm->manageGroupsForm($groups, $students, $db, $assignments);
            break;

        case 'assign_tasks':
            $uYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $groups = $db->getGroups($uYear);
            $books = $db->getTheoryBooks();
            $fm->assignTasksForm($groups, $db, $books);
            break;

        case 'save_group':
            $uYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
            $db->createGroup($_POST['group_name'], $uYear);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'add_student_to_group':
            $db->addStudentToGroup($_POST['student_id'], $_POST['group_id']);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'save_group_task':
            $db->saveGroupTask($_POST['group_id'], $_POST['task_text'], $_POST['book_id'], $_FILES['task_file']);
            echo "<script>window.location.href='index.php?action=assign_tasks';</script>";
            break;

        case 'remove_student_from_group':
            if (isset($_GET['student_id'])) {
                $db->removeStudentFromGroup($_GET['student_id']);
            }
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'grade_task':
            if (isset($_GET['task_id'])) {
                $task = $db->getTaskById($_GET['task_id']);
                $students = $db->getStudentsByGroupId($task['group_id']);
                $grades = $db->getTaskGrades($_GET['task_id']);
                $fm->showTaskGradesForm($task, $students, $grades);
            }
            break;

        case 'save_task_grades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST['grades'] as $stId => $grade) {
                    if ($grade !== "") $db->saveTaskGrade($_POST['task_id'], $stId, $grade, $_POST['comments'][$stId]);
                }
                echo "<script>window.location.href='index.php?action=assign_tasks';</script>";
            }
            break;

        default:
            $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

            // Παίρνουμε τα μεζεδάκια (η μέθοδος πλέον δεν θα "σκάσει" ποτέ)
            $mezedakia = $db->getAllMezedakiaForAdmin();

            if (empty($userYear)) {
                // Φιλικό μήνυμα αντί για Fatal Error
                echo "<div class='container mt-4'><div class='alert alert-warning shadow-sm text-center'>
                        <i class='fa fa-user-circle'></i> Παρακαλώ πληκτρολογήστε το <b>Username</b> σας και πατήστε <b>Ορισμός</b> για να φορτώσουν τα μεζεδάκια.
                      </div></div>";
            } else {
                // Κλήση της λίστας
                $fm->listMezedakia($mezedakia, $db);
            }
            break;
    }

    $page->displayEndMatter();
