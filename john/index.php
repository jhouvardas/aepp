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
    $theoryFm = new TheoryAdminFormMaker();
    $exFm     = new ExerciseAdminFormMaker();
    $reportFm = new ReportAdminFormMaker();

    $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

    $page->displayHeadMatter(); // Καλείται πρώτα το head
    $page->displayMenu($userYear, $db); // Περιλαμβάνουμε το userYear και το $db object
    if (isset($_GET['status'])) $page->showToast($_GET['status']);

    // Sanitization της παραμέτρου action
    $action = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action']) : 'dashboard';

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
                    <base href="../">
                    <title>Προεπισκόπηση Μεζεδακίου #<?php echo $meze['mezeNumber']; ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                    <link rel="stylesheet" href="aepp.css">
                </head>

                <body class="p-4 bg-light">
                    <div class="container">
                        <div class="preview-box">
                            <h2 class="border-bottom pb-2 mb-4 text-dark font-weight-bold">
                                Προεπισκόπηση: Μεζεδάκι #<?php echo $meze['mezeNumber']; ?>
                                <?php if (isset($meze['isSos']) && $meze['isSos'] == 1): ?>
                                    <span class="badge bg-danger ms-2" style="font-size: 1rem;"><i class="fa fa-fire"></i> SOS</span>
                                <?php endif; ?>
                            </h2>

                            <!-- Ενότητα Εκφώνησης -->
                            <div class="mb-5">
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
            if (isset($_POST['tutor_user'])) {
                $_SESSION['tutor_user'] = $_POST['tutor_user'];
                echo "<div class='container mt-2'><div class='alert alert-info'>Το έτος εργασίας ορίστηκε σε: " . $_SESSION['tutor_user'] . "</div></div>";
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
                $count = $db->massGradeZeroForMeze($_GET['id'], $userYear);
                echo "<script>alert('Ολοκληρώθηκε! $count μαθητές που δεν παρέδωσαν βαθμολογήθηκαν με 0.'); window.location.href='index.php?action=viewSubmissions&id=" . $_GET['id'] . "';</script>";
                exit();
            }
            break;

        case 'massEmailZero':
            if (isset($_GET['id'])) {
                $mezeId = $_GET['id'];
                $studentsToEmail = $db->getStudentsWithZeroGrade($mezeId, $userYear);
                $mezeNum = $db->getMezeNumberById($mezeId);

                if (empty($studentsToEmail)) {
                    echo "<script>alert('Δεν βρέθηκαν μαθητές με βαθμό 0 και email.'); window.history.back();</script>";
                    exit();
                }

                require_once '../phpmailer/class.phpmailer.php';
                require_once '../phpmailer/class.smtp.php';
                require_once 'config.php';

                $successCount = 0;
                foreach ($studentsToEmail as $student) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USER;
                        $mail->Password = SMTP_PASS;
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
                        $mail->addAddress($student['email']);
                        $mail->isHTML(true);
                        $mail->Subject = "Ενημέρωση Βαθμολογίας: Μεζεδάκι #$mezeNum";
                        $mail->Body = "
                            <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
                                <h2 style='color:#dc3545;'>Ενημέρωση Βαθμολογίας (ΑΕΠΠ)</h2>
                                <p>Γεια σου <b>{$student['name']}</b>,</p>
                                <p>Σε ενημερώνουμε ότι στο <b>Μεζεδάκι #$mezeNum</b> η βαθμολογία σου είναι <b>0/20</b> λόγω μη έγκαιρης υποβολής.</p>
                                <p>Αν επιθυμείς να υποβάλεις τη λύση σου τώρα για βελτίωση ή εκπρόθεσμα, επικοινώνησε με τον δάσκαλό σου ή ζήτησε παράταση μέσω της πλατφόρμας.</p>
                                <p>Καλή συνέχεια!</p>
                            </div>";
                        $mail->send();
                        $successCount++;
                    } catch (Exception $e) { /* Σφάλμα σε μεμονωμένο email */
                    }
                }
                echo "<script>alert('Ολοκληρώθηκε! Στάλθηκαν $successCount emails ενημέρωσης.'); window.location.href='index.php?action=viewSubmissions&id=$mezeId';</script>";
                exit();
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

                // Χρήση PHPMailer από τον τοπικό φάκελο (έκδοση 5.2.x)
                require_once '../phpmailer/class.phpmailer.php';
                require_once '../phpmailer/class.smtp.php';

                require_once 'config.php';

                $mail = new PHPMailer(true);

                try {
                    // Ρυθμίσεις Gmail SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USER;
                    $mail->Password   = SMTP_PASS;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    // Στοιχεία Αποστολέα
                    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
                    $mail->addAddress($to);
                    $mail->addReplyTo(SMTP_REPLY_TO, 'Πληροφορίες');

                    // Περιεχόμενο
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $htmlContent;
                    $mail->AltBody = strip_tags($htmlContent);

                    $mail->send();
                    echo "<script>alert('✅ Το Email στάλθηκε επιτυχώς μέσω Gmail SMTP!'); window.location.href='index.php?action=viewSubmissions&id=$mezeId';</script>";
                } catch (Exception $e) {
                    $errorMsg = addslashes($mail->ErrorInfo);
                    echo "<script>alert('❌ Η αποστολή απέτυχε. Σφάλμα: $errorMsg'); window.location.href='index.php?action=viewSubmissions&id=$mezeId';</script>";
                }
                exit();
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
                        require_once '../phpmailer/class.phpmailer.php';
                        require_once '../phpmailer/class.smtp.php';
                        require_once 'config.php';

                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = SMTP_USER;
                            $mail->Password   = SMTP_PASS;
                            $mail->SMTPSecure = 'tls';
                            $mail->Port       = 587;
                            $mail->CharSet    = 'UTF-8';

                            $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
                            $mail->addAddress($student['email']);
                            $mail->isHTML(true);

                            if ($approve) {
                                $mail->Subject = "Έγκριση Παράτασης: Μεζεδάκι #$mezeNum";
                                $mail->Body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #28a745;'>Έγκριση Παράτασης!</h2>
                                    <p>Γεια σου <b>" . $student['name'] . "</b>,</p>
                                    <p>Το αίτημά σου για παράταση στο <b>Μεζεδάκι #$mezeNum</b> εγκρίθηκε από τον δάσκαλο.</p>
                                    <p>Έχεις πλέον <b>$hours επιπλέον ώρες</b> από τώρα για να υποβάλεις τη λύση σου στην πλατφόρμα.</p>
                                    <p><a href='https://jhouv.eu/aepp/index.php?action=viewMezedakia' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Μετάβαση στα Μεζεδάκια</a></p>
                                    <p>Καλή συνέχεια και καλή μελέτη,<br><b>Αντώνης Χουβαρδάς</b></p>
                                </div>";
                            } else {
                                $mail->Subject = "Ενημέρωση Αιτήματος Παράτασης: Μεζεδάκι #$mezeNum";
                                $mail->Body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #dc3545;'>Απόρριψη Αιτήματος Παράτασης</h2>
                                    <p>Γεια σου <b>" . $student['name'] . "</b>,</p>
                                    <p>Σε ενημερώνουμε ότι το αίτημά σου για παράταση στο <b>Μεζεδάκι #$mezeNum</b> δεν έγινε δεκτό από τον δάσκαλο.</p>
                                    <p>Μπορείς να επικοινωνήσεις με τον δάσκαλό σου για οποιαδήποτε διευκρίνιση.</p>
                                    <p>Καλή συνέχεια,<br><b>Αντώνης Χουβαρδάς</b></p>
                                </div>";
                            }
                            $mail->send();
                        } catch (Exception $e) { /* Silent fail */
                        }
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
                $students = $db->getStudentsByGroupId($task['group_id'], $userYear);
                $grades = $db->getTaskGrades($_GET['task_id']);
                $reportFm->showTaskGradesForm($task, $students, $grades);
            }
            break;

        case 'save_task_grades':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST['grades'] as $stId => $grade) {
                    if ($grade !== "") $db->saveTaskGrade($_POST['task_id'], $stId, $grade, $_POST['comments'][$stId]);
                }
                echo "<script>window.location.href='index.php?action=list_all_tasks';</script>";
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

    $page->displayEndMatter();
