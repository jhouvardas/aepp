    <?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    error_reporting(E_ALL);

    // Χρήση spl_autoload_register αντί της deprecated __autoload
    spl_autoload_register(function ($name) {
        if (file_exists($name . '.php')) {
            include_once $name . '.php';
        } else if (file_exists('../' . $name . '.php')) {
            include_once '../' . $name . '.php';
        }
    });

    $page = new AdminPageMaker();
    $db = new AdminDbHandler();

    $mezeFm   = new MezeAdminFormMaker();
    $formMaker = new FormMaker();
    $theoryFm = new TheoryAdminFormMaker();
    $exFm     = new ExerciseAdminFormMaker();
    $reportFm = new ReportAdminFormMaker();

    $userYear = $db->getCurrentTutorYear();

    // Sanitization της παραμέτρου action
    $action = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action']) : 'dashboard';

    // Λίστα ενεργειών που δεν πρέπει να εκτυπώνουν το κοινό μενού/header (π.χ. εξαγωγή αρχείων)
    $noLayoutActions = ['export_word_exam', 'previewMeze'];

    if (!in_array($action, $noLayoutActions)) {
        $page->displayHeadMatter(); // Καλείται πρώτα το head
        $page->displayMenu($userYear, $db); // Περιλαμβάνουμε το userYear και το $db object
        if (isset($_GET['status'])) $page->showToast($_GET['status']);

        // --- ΕΛΕΓΧΟΣ ΚΑΙ ΕΜΦΑΝΙΣΗ ΓΕΝΕΘΛΙΩΝ (ΑΥΡΙΟ) ΓΙΑ ΤΟ ADMIN ---
        if (!empty($userYear)) {
            $students = $db->getTutorStudents($userYear);
            $birthdayStudents = [];
            $tomorrowMD = date('m-d', strtotime('+1 day'));
            if (is_array($students)) {
                foreach ($students as $student) {
                    if (!empty($student['birthday']) && $student['birthday'] !== '0000-00-00' && $student['birthday'] !== '-') {
                        if (date('m-d', strtotime($student['birthday'])) === $tomorrowMD) {
                            $birthdayStudents[] = $student['name'] . ' ' . $student['lastName'];
                        }
                    }
                }
            }
            if (!empty($birthdayStudents)) {
                $names = implode(', ', $birthdayStudents);
                echo "<div class='container mt-3'><div class='alert alert-info text-center shadow-sm border-info' style='border-radius: 10px;'><i class='fa fa-gift text-primary fa-2x align-middle me-3'></i><span class='align-middle fs-5'><strong>Υπενθύμιση:</strong> Αύριο έχει γενέθλια: <strong>{$names}</strong>! 🎈</span></div></div>";
            }
        }
        // --- ΤΕΛΟΣ ΕΛΕΓΧΟΥ ΓΕΝΕΘΛΙΩΝ ---
    }

    switch ($action) {
        case 'save_theory':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $success = $db->insertTheoryItem(
                    $_POST['book_id'],
                    $_POST['chapter_num'],
                    $_POST['question_text'],
                    $_POST['answer_text'],
                    $_POST['page_number'] ?? 0,
                    $_FILES['q_file'] ?? null, // Προσθήκη αρχείου ερώτησης
                    $_FILES['a_file'] ?? null  // Προσθήκη αρχείου απάντησης
                );
                if ($success) {
                    echo "<div class='container mt-2'><div class='alert alert-success'>Η ερώτηση προστέθηκε επιτυχώς!</div></div>";
                }
            }
            // No break - για να ξαναδείξει τη φόρμα
            // No break - θέλουμε να ξαναδείξει τη φόρμα
        case 'add_theory':
            $books = $db->getTheoryBooks();
            $theoryFm->addTheoryForm($books);
            break;

        // Πρόσθεσε αυτά τα cases στο switch σου
        case 'manage_books':
            $books = $db->getTheoryBooks();
            $theoryFm->manageBooksForm($books);
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
                $theoryFm->editTheoryForm($questionData, $books);
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
                    $_POST['page_number'] ?? 0,
                    $_FILES['q_file'] ?? null, // Προσθήκη αρχείου ερώτησης
                    $_FILES['a_file'] ?? null  // Προσθήκη αρχείου απάντησης
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
            $theoryFm->listTheoryQuestions($questions);
            break;

        case 'list_for_test':
            $questions = $db->getAllQuestionsOrdered(); // Χρησιμοποιούμε την ήδη υπάρχουσα μέθοδο
            $theoryFm->listTheoryQuestionsForTests($questions);
            break;

        case 'create_exam':
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['selected_questions'])) {
                $selectedIds = $_POST['selected_questions'];
                $examQuestions = $db->getMultipleQuestionsByIds($selectedIds);
                $theoryFm->previewExam($examQuestions);
            } else {
                echo "<div class='container mt-5'><div class='alert alert-warning'>Δεν επιλέξατε ερωτήσεις!</div></div>";
            }
            break;

        case 'export_word_exam':
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['selected_questions'])) {
                $selectedIds = $_POST['selected_questions'];
                $examQuestions = $db->getMultipleQuestionsByIds($selectedIds);

                // Ρυθμίσεις Header για να αναγνωριστεί ως έγγραφο Word
                header("Content-Type: application/vnd.ms-word; charset=UTF-8");
                header("Content-Disposition: attachment;Filename=Diagonisma_AEPP.doc");
                header("Pragma: no-cache");
                header("Expires: 0");

                echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
                echo "<head><meta charset='utf-8'><title>Διαγώνισμα ΑΕΠΠ</title></head><body style='font-family: Arial, sans-serif;'>";
                echo "<h2 style='text-align: center; text-decoration: underline;'>ΔΙΑΓΩΝΙΣΜΑ ΑΕΠΠ</h2><br><br>";
                $i = 1;

                // Δημιουργία δυναμικού απόλυτου URL για τις εικόνες (απαραίτητο για το Word)
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $baseUploadUrl = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/uploads/';

                while ($row = $examQuestions->fetch_assoc()) {
                    echo "<div style='margin-bottom: 24px;'>";
                    echo "<strong style='font-size: 1.1em;'>Θέμα " . $i++ . ":</strong><br>";
                    echo "<div style='margin-top: 10px;'>" . $row['question_text'] . "</div>";
                    if (!empty($row['question_image'])) {
                        echo "<div style='margin-top: 15px; text-align: center;'><img src='" . $baseUploadUrl . $row['question_image'] . "' alt='Εικόνα Θέματος' style='max-width: 100%; height: auto;' /></div>";
                    }
                    echo "</div><br>";
                }
                echo "</body></html>";
                exit();
            }
            break;

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
                    echo "<div class='container mt-2'><div class='alert alert-success shadow'>Το Μεζεδάκι #" . $_POST['mezeNumber'] . " αποθηκεύτηκε επιτυχώς!</div></div>";
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
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <base href="../">
                    <title>Μεζεδάκι #<?php echo $meze['mezeNumber']; ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                    <link rel="stylesheet" href="aepp.css?v=<?php echo @filemtime(__DIR__ . '/../aepp.css'); ?>">
                    <style>
                        @media print {
                            .preview-box {
                                border: none !important;
                                padding: 0 !important;
                                box-shadow: none !important;
                            }

                            .bg-light {
                                background-color: transparent !important;
                            }

                            .container {
                                padding: 0 !important;
                                max-width: 100% !important;
                            }

                            .shadow-sm,
                            .shadow {
                                box-shadow: none !important;
                            }

                            .border {
                                border: 1px solid #dee2e6 !important;
                            }

                            .html-content-wrapper {
                                padding: 0 !important;
                                border: none !important;
                            }

                            h4 {
                                page-break-after: avoid;
                            }
                        }
                    </style>
                </head>

                <body class="p-0 p-md-4 bg-light">
                    <!-- Control Panel Εκτύπωσης -->
                    <div class="container d-print-none mb-4">
                        <div class="card shadow border-info">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                                <div class="mb-2 mb-md-0">
                                    <strong class="text-info me-3"><i class="fa fa-print"></i> Επιλογές Εκτύπωσης:</strong>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="chkQuestion" checked onchange="document.getElementById('questionBlock').classList.toggle('d-none', !this.checked)">
                                        <label class="form-check-label fw-bold" for="chkQuestion">Εκφώνηση</label>
                                    </div>
                                    <?php if (!empty($meze['mezeHints'])): ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="chkHints" checked onchange="document.getElementById('hintsBlock').classList.toggle('d-none', !this.checked)">
                                            <label class="form-check-label fw-bold" for="chkHints">Υποδείξεις</label>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="chkSolution" checked onchange="document.getElementById('solutionBlock').classList.toggle('d-none', !this.checked)">
                                        <label class="form-check-label fw-bold" for="chkSolution">Λύση</label>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-info text-white shadow-sm fw-bold px-4" onclick="window.print()"><i class="fa fa-print"></i> Εκτύπωση</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container px-0 px-sm-3">
                        <div class="preview-box">
                            <h2 class="border-bottom pb-2 mb-4 text-dark font-weight-bold">
                                Μεζεδάκι #<?php echo $meze['mezeNumber']; ?>
                                <?php if (isset($meze['isSos']) && $meze['isSos'] == 1): ?>
                                    <span class="badge bg-danger ms-2" style="font-size: 1rem;"><i class="fa fa-fire"></i> SOS</span>
                                <?php endif; ?>
                            </h2>

                            <!-- Ενότητα Εκφώνησης -->
                            <div class="mb-5" id="questionBlock">
                                <h4 class="text-primary mb-3"><i class="fa fa-file-text-o"></i> Εκφώνηση</h4>
                                <?php if (!empty($meze['mezeImage'])): ?>
                                    <div class="text-center mb-3">
                                        <img src="images/mezedakia/<?php echo $meze['mezeImage']; ?>" class="img-fluid exam-img">
                                    </div>
                                <?php endif; ?>
                                <div class="html-content-wrapper p-3 border rounded bg-white">
                                    <?php echo $meze['mezeText']; ?>
                                </div>
                            </div>

                            <!-- Ενότητα Hints -->
                            <?php if (!empty($meze['mezeHints'])): ?>
                                <div class="mb-5" id="hintsBlock">
                                    <h4 class="text-info mb-3"><i class="fa fa-lightbulb-o"></i> Υποδείξεις / Hints</h4>
                                    <div class="alert alert-info border-info shadow-sm">
                                        <?php echo nl2br($meze['mezeHints']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Ενότητα Λύσης -->
                            <div class="mb-4" id="solutionBlock">
                                <h4 class="text-success mb-3"><i class="fa fa-check-circle"></i> Λύση</h4>
                                <div class="p-3 border border-success rounded bg-light shadow-sm">
                                    <?php if (!empty($meze['mezeSolutionImage'])): ?>
                                        <div class="text-center mb-3">
                                            <img src="images/mezedakia/<?php echo $meze['mezeSolutionImage']; ?>" class="img-fluid exam-img border-success">
                                        </div>
                                    <?php endif; ?>
                                    <div class="solution-text">
                                        <?php echo !empty($meze['mezeSolution']) ? $meze['mezeSolution'] : (empty($meze['mezeSolutionImage']) ? '<i class="text-muted">Δεν έχει καταχωρηθεί λύση.</i>' : ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Bootstrap 5 JS Bundle (Περιλαμβάνει Popper) -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                    <script>
                        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
                            let hintsCheck = document.getElementById('chkHints');
                            if (hintsCheck) {
                                hintsCheck.checked = false; // Απόκρυψη Hints από προεπιλογή στην αυτόματη εκτύπωση
                                document.getElementById('hintsBlock').classList.add('d-none');
                            }
                            setTimeout(() => window.print(), 800);
                        }
                    </script>
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

                $mezeFm->showGradesForm($students, $mezeId, $meze['mezeNumber'], $indexedGrades);
            }
            break;

        case 'saveGrades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $mezeId = $_POST['meze_id'];
                $count = 0;

                if (!isset($_POST['grades']) || !is_array($_POST['grades'])) {
                    $_POST['grades'] = [];
                }

                foreach ($_POST['grades'] as $studentId => $grade) {
                    if ($grade !== '') { // Αποθηκεύουμε μόνο αν έχει μπει βαθμός
                        $db->saveMezeGrade($studentId, $mezeId, $grade, $userYear);
                        $count++;
                    }
                }
                echo "<div class='container mt-3'><div class='alert alert-success shadow'>Επιτυχής αποθήκευση $count βαθμολογιών!</div></div>";
                // Επιστροφή στη λίστα (χρησιμοποιούμε getAllMezedakiaForAdmin για την admin view)
                $result = $db->getAllMezedakiaForAdmin();
                $mezeFm->listMezedakia($result, $db);
            }
            break;
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
        case 'fullReport':
            $students = $db->getTutorStudents($userYear);
            $gradesReport = $db->getFullGradesReport($userYear);
            $reportFm->showFullGradesTable($students, $gradesReport);
            break;
        case 'studentReport':
        case 'viewStudentProfile':
            $students = $db->getTutorStudents($userYear);

            if (isset($_GET['studentId'])) {
                $studentId = $_GET['studentId'];

                // Βελτίωση: Αναζήτηση απευθείας στον πίνακα αντί για loop στην PHP
                $studentKey = array_search($studentId, array_column($students, 'studentId'));
                $studentInfo = ($studentKey !== false) ? $students[$studentKey] : null;

                if ($studentInfo) {
                    $grades = $db->getStudentGradesForStudent($studentId, $userYear);
                    $average = $db->getStudentOverallAverage($studentId, $userYear);
                    $tasks = $db->getStudentGroupTasks($studentId);
                    $financials = $db->getStudentFinancials($studentId);
                    $trend = $db->getStudentPerformanceTrend($studentId, $userYear);
                    $reportFm->showFullStudentProfile($studentInfo, $grades, $tasks, $financials, $average, $trend);
                }
            } else {
                $reportFm->showStudentSelectionList($students);
            }
            break;
        case 'oldStudentReport':
            if (isset($_GET['studentId']) && isset($_GET['name'])) {
                $grades = $db->getStudentGrades($_GET['studentId'], $userYear);
                $reportFm->showStudentReport($_GET['name'], $grades);
            }
            break;

        case 'deleteSpecificGrade':
            if (isset($_GET['studentId']) && isset($_GET['mezeId'])) {
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
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'massGradeZero':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $count = $db->massGradeZeroForMeze($mezeId, $userYear);
                echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Ολοκληρώθηκε! <b>$count</b> μαθητές που δεν παρέδωσαν βαθμολογήθηκαν με 0.</div></div>";

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'massEmailZero':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $studentsToEmail = $db->getStudentsWithZeroGrade($mezeId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                if (empty($studentsToEmail)) {
                    echo "<div class='container mt-3'><div class='alert alert-warning shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Δεν βρέθηκαν μαθητές με βαθμό 0 και email.</div></div>";
                } else {
                    $successCount = 0;
                    foreach ($studentsToEmail as $student) {
                        $subject = "Ενημέρωση Βαθμολογίας: Μεζεδάκι #$mezeNum";
                        $body = "
                                <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                    <h2 style='color:#dc3545;'>Ενημέρωση Βαθμολογίας (ΑΕΠΠ)</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <p>Σε ενημερώνουμε ότι στο <b>Μεζεδάκι #$mezeNum</b> η βαθμολογία σου είναι <b>0/20</b> λόγω μη έγκαιρης υποβολής.</p>
                                    <p>Αν επιθυμείς να υποβάλεις τη λύση σου τώρα για βελτίωση ή εκπρόθεσμα, επικοινώνησε με τον δάσκαλό σου ή ζήτησε παράταση μέσω της πλατφόρμας.</p>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                        if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                            $successCount++;
                        }
                    }
                    echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Ολοκληρώθηκε! Στάλθηκαν $successCount emails ενημέρωσης.</div></div>";
                }

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

        case 'sendReminder':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $studentsToEmail = $db->getStudentsWithoutSubmission($mezeId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $deadline = date('d/m/Y H:i', strtotime($meze['solutionDate']));

                if (empty($studentsToEmail)) {
                    echo "<script>alert('Όλοι οι μαθητές έχουν ήδη υποβάλει λύση ή δεν έχουν δηλωμένο email!'); window.location.href='index.php?action=listMezedakia';</script>";
                } else {
                    $successCount = 0;
                    foreach ($studentsToEmail as $student) {
                        $subject = "⏳ Υπενθύμιση: Λήξη προθεσμίας - Μεζεδάκι #$mezeNum";
                        $body = "
                                <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                    <h2 style='color:#ffc107;'>⏳ Υπενθύμιση Προθεσμίας</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <p>Σε ειδοποιούμε ότι η προθεσμία υποβολής για το <b>Μεζεδάκι #$mezeNum</b> λήγει στις <b>$deadline</b> και δεν έχουμε λάβει ακόμα την απάντησή σου.</p>
                                    <p>Παρακαλούμε συνδέσου στην πλατφόρμα και ολοκλήρωσε την άσκηση εγκαίρως.</p>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                        if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                            $successCount++;
                        }
                    }
                    echo "<script>alert('Ολοκληρώθηκε! Στάλθηκαν $successCount emails υπενθύμισης.'); window.location.href='index.php?action=listMezedakia';</script>";
                }
            }
            break;

        case 'quickGrade':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $studentId = $_POST['student_id'];
                $mezeId = $_POST['meze_id'];
                $grade = $_POST['grade'];
                $comments = isset($_POST['teacher_comments']) ? $_POST['teacher_comments'] : "";

                // 1. Αποθήκευση ή Ενημέρωση του βαθμού και των σχολίων
                $db->updateOrInsertGrade($studentId, $mezeId, $grade, $userYear, $comments);

                // 2. Υπολογισμός Μέσου Όρου (περιλαμβάνει και τον νέο βαθμό)
                $avg = $db->getStudentAverage($studentId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                // 3. Εύρεση ονόματος μαθητή για την αναφορά
                $students = $db->getTutorStudents($userYear);
                $student = null;
                foreach ($students as $s) {
                    if ($s['studentId'] == $studentId) {
                        $student = $s;
                        break;
                    }
                }
                $studentName = $student ? $student['name'] . " " . $student['lastName'] : "Μαθητής";

                // 4. Εμφάνιση της εκτυπώσιμης αναφοράς (PDF Report)
                $mezeFm->showPrintableReport($studentName, $mezeNum, $grade, $comments, $avg, $mezeId, $student);
            }
            break;

        case 'sendReportEmail':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $to = $_POST['email'];
                $subject = $_POST['subject'];
                $htmlContent = htmlspecialchars_decode($_POST['html_message']);
                $mezeId = $_POST['meze_id'];

                $replyTo = defined('SMTP_REPLY_TO') ? SMTP_REPLY_TO : null;
                $result = $db->sendSystemEmail($to, $subject, $htmlContent, $replyTo);

                if ($result === true) {
                    echo "<div class='container mt-3'><div class='alert alert-success shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>✅ Το Email στάλθηκε επιτυχώς μέσω Gmail SMTP!</div></div>";
                } else {
                    $errorMsg = addslashes($result);
                    echo "<div class='container mt-3'><div class='alert alert-danger shadow alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>❌ Η αποστολή απέτυχε. Σφάλμα: $errorMsg</div></div>";
                }

                $mezeRes = $db->getMezedakiById($mezeId);
                $meze = $mezeRes->fetch_assoc();
                $studentsList = $db->getTutorStudents($userYear);
                $submissions = $db->getSubmissionsByMeze($mezeId);
                $existingGrades = $db->getGradesForMeze($mezeId);
                $allMezeRes = $db->getAllMezedakia();
                $allMezedakia = [];
                while ($m = $allMezeRes->fetch_assoc()) {
                    $allMezedakia[] = $m;
                }
                $mezeFm->showSubmissionsForGrading($submissions, $studentsList, $meze, $allMezedakia, $existingGrades);
            }
            break;

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

        case 'manage_groups':
            $groups = $db->getGroups($userYear);
            $students = $db->getTutorStudents($userYear);
            $assignments = $db->getAssignedStudents();
            $reportFm->manageGroupsForm($groups, $students, $db, $assignments);
            break;

        case 'assign_tasks':
            $groups = $db->getGroups($userYear);
            $books = $db->getTheoryBooks();
            $exFm->assignTasksForm($groups, $db, $books);
            break;

        case 'list_all_tasks':
            $tasks = $db->getAllGroupTasks($userYear);
            $exFm->listAllTasks($tasks);
            break;

        case 'save_group':
            $db->createGroup($_POST['group_name'], $userYear);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'rename_group':
            if (isset($_POST['group_id']) && isset($_POST['new_group_name'])) {
                $db->renameGroup($_POST['group_id'], $_POST['new_group_name'], $userYear);
            }
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'add_student_to_group':
            $db->addStudentToGroup($_POST['student_id'], $_POST['group_id']);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'save_group_task':
            $db->saveGroupTask($_POST['group_id'], $_POST['task_text'], $_POST['book_id'] ?? null, $_FILES['task_file'] ?? null);
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
                $students = $db->getStudentsByGroupId($task['group_id'], $userYear);
                $grades = $db->getTaskGrades($_GET['task_id']);
                $reportFm->showTaskGradesForm($task, $students, $grades);
            }
            break;

        case 'save_task_grades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $taskId = $_POST['task_id'];
                $sendEmails = isset($_POST['send_emails']) && $_POST['send_emails'] == '1';

                $task = $db->getTaskById($taskId);
                $taskName = $task ? mb_substr(strip_tags($task['task_text']), 0, 60) . "..." : "Ομαδική Εργασία";
                $students = $db->getTutorStudents($userYear);

                // Φέρνουμε τους υπάρχοντες βαθμούς για να ελέγξουμε αν υπήρξε αλλαγή
                $existingGrades = $db->getTaskGrades($taskId);
                $emailCount = 0;

                if (!isset($_POST['grades']) || !is_array($_POST['grades'])) {
                    $_POST['grades'] = [];
                }

                foreach ($_POST['grades'] as $stId => $grade) {
                    $comment = $_POST['comments'][$stId] ?? '';
                    if ($grade !== "") {
                        $oldGrade = isset($existingGrades[$stId]['grade_value']) ? $existingGrades[$stId]['grade_value'] : null;
                        $oldComment = isset($existingGrades[$stId]['teacher_comments']) ? $existingGrades[$stId]['teacher_comments'] : '';
                        $isChanged = ($oldGrade != $grade || $oldComment != $comment);

                        $db->saveTaskGrade($taskId, $stId, $grade, $comment);

                        if ($sendEmails && $isChanged) {
                            $studentKey = array_search($stId, array_column($students, 'studentId'));
                            $student = ($studentKey !== false) ? $students[$studentKey] : null;

                            if ($student && !empty($student['email'])) {
                                $subject = "Ενημέρωση Βαθμολογίας: Ομαδική Εργασία";
                                $body = "
                                    <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                        <h2 style='color:#007bff;'>Ενημέρωση Βαθμολογίας</h2>
                                        <p>Γεια σου <b>{$student['name']}</b>,</p>
                                        <p>Η βαθμολογία σου για την εργασία <i>\"$taskName\"</i> καταχωρήθηκε.</p>
                                        <div style='background:#f8f9fa; padding:15px; border-radius:5px; margin:15px 0;'>
                                            <p style='margin:5px 0;'><b>Βαθμός:</b> <span style='color:#dc3545; font-size:1.2em;'>$grade/20</span></p>
                                            <div style='margin:5px 0;'><b>Σχόλια:</b><br><div style='white-space: pre-wrap; font-style: italic; padding-top: 5px;'>$comment</div></div>
                                        </div>
                                        <p>Καλή συνέχεια!</p>
                                    </div>";
                                if ($db->sendSystemEmail($student['email'], $subject, $body) === true) {
                                    $emailCount++;
                                }
                            }
                        }
                    }
                }

                $alertMsg = $sendEmails ? "Επιτυχής αποθήκευση! Στάλθηκαν $emailCount emails ενημέρωσης." : "Επιτυχής αποθήκευση!";
                echo "<script>alert('$alertMsg'); window.location.href='index.php?action=list_all_tasks';</script>";
            }
            break;

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

        case 'group_email_form':
            $groups = $db->getGroups($userYear);
            $reportFm->groupEmailForm($groups);
            break;

        case 'send_group_email':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $groupId = $_POST['group_id'];
                $subject = $_POST['subject'];
                $messageHtml = $_POST['message'];

                $students = $db->getStudentsByGroupId($groupId, $userYear);
                $groupInfo = null;

                $groups = $db->getGroups($userYear);
                foreach ($groups as $g) {
                    if ($g['id'] == $groupId) {
                        $groupInfo = $g;
                        break;
                    }
                }

                $groupName = $groupInfo ? $groupInfo['group_name'] : "Ομάδα";

                // New arrays for results
                $successfulRecipients = [];
                $failedRecipients = [];

                if (!empty($students)) {
                    foreach ($students as $student) {
                        if (!empty($student['email'])) {
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #007bff;'>Ενημέρωση Ομάδας: $groupName</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;'>
                                        $messageHtml
                                    </div>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                            $res = $db->sendSystemEmail($student['email'], $subject, $body);
                            if ($res === true) {
                                $successfulRecipients[] = [
                                    'name' => $student['name'] . ' ' . $student['lastName'],
                                    'email' => $student['email']
                                ];
                            } else {
                                $failedRecipients[] = [
                                    'name' => $student['name'] . ' ' . $student['lastName'],
                                    'email' => $student['email'],
                                    'error' => $res
                                ];
                            }
                        } else {
                            $failedRecipients[] = [
                                'name' => $student['name'] . ' ' . $student['lastName'],
                                'email' => 'Δεν υπάρχει email',
                                'error' => 'Δεν έχει δηλωθεί διεύθυνση email για αυτόν τον μαθητή.'
                            ];
                        }
                    }
                }
                if (count($successfulRecipients) > 0) {
                    $db->logGroupEmail($groupId, $subject, $messageHtml, $userYear);
                }

                $_SESSION['email_results'] = [
                    'groupName' => $groupName,
                    'subject' => $subject,
                    'message' => $messageHtml,
                    'successful' => $successfulRecipients,
                    'failed' => $failedRecipients
                ];

                echo "<script>window.location.href='index.php?action=group_email_results';</script>";
            }
            break;

        case 'group_email_history':
            $history = $db->getGroupEmailHistory($userYear);
            $reportFm->showGroupEmailHistory($history);
            break;

        case 'group_email_results':
            if (isset($_SESSION['email_results'])) {
                $results = $_SESSION['email_results'];
                $reportFm->showGroupEmailResults(
                    $results['groupName'],
                    $results['subject'],
                    $results['message'],
                    $results['successful'],
                    $results['failed']
                );
                unset($_SESSION['email_results']);
            } else {
                echo "<script>window.location.href='index.php?action=group_email_form';</script>";
            }
            break;

        case 'retry_failed_emails':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $subject = $_POST['subject'];
                $messageHtml = $_POST['message'];
                $failedRecipientsJson = $_POST['failed_recipients'];
                $recipientsToRetry = json_decode(htmlspecialchars_decode($failedRecipientsJson), true);

                $successfulRecipients = [];
                $failedRecipients = [];
                $groupName = $_POST['group_name'];

                if (!empty($recipientsToRetry)) {
                    foreach ($recipientsToRetry as $student) {
                        if (!empty($student['email']) && $student['email'] !== 'Δεν υπάρχει email') {
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #007bff;'>Ενημέρωση Ομάδας: $groupName</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;'>
                                        $messageHtml
                                    </div>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                            $res = $db->sendSystemEmail($student['email'], $subject, $body);
                            if ($res === true) {
                                $successfulRecipients[] = $student;
                            } else {
                                $failedRecipients[] = ['name' => $student['name'], 'email' => $student['email'], 'error' => $res];
                            }
                        } else {
                            $failedRecipients[] = $student; // Keep them in the failed list
                        }
                    }
                }

                $_SESSION['email_results'] = [
                    'groupName' => $groupName . " (Επανάληψη)",
                    'subject' => $subject,
                    'message' => $messageHtml,
                    'successful' => $successfulRecipients,
                    'failed' => $failedRecipients
                ];

                echo "<script>window.location.href='index.php?action=group_email_results';</script>";
            }
            break;

        default:
            // Παίρνουμε τα μεζεδάκια (η μέθοδος πλέον δεν θα "σκάσει" ποτέ)
            $mezedakia = $db->getAllMezedakiaForAdmin();

            if (empty($userYear)) {
                // Φιλικό μήνυμα αντί για Fatal Error
                echo "<div class='container mt-4'><div class='alert alert-warning shadow-sm text-center'>
                        <i class='fa fa-user-circle'></i> Παρακαλώ πληκτρολογήστε το <b>Username</b> σας και πατήστε <b>Ορισμός</b> για να φορτώσουν τα μεζεδάκια.
                      </div></div>";
            } else {
                // Κλήση της λίστας
                $mezeFm->listMezedakia($mezedakia, $db);
            }
            break;
    }

    if (!in_array($action, $noLayoutActions)) {
        $page->displayEndMatter();
    }
