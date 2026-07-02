    <?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    error_reporting(E_ALL);

    spl_autoload_register(function ($name) {
        if (file_exists($name . '.php')) {
            include_once $name . '.php';
        } else if (file_exists('../' . $name . '.php')) {
            include_once '../' . $name . '.php';
        }
    });

    $page     = new AdminPageMaker();
    $db       = new AdminDbHandler();
    $mezeFm   = new MezeAdminFormMaker();
    $formMaker = new FormMaker();
    $theoryFm = new TheoryAdminFormMaker();
    $exFm     = new ExerciseAdminFormMaker();
    $reportFm = new ReportAdminFormMaker();

    $userYear = $db->getCurrentTutorYear();

    if (!isset($_SESSION['exam_year']) || empty($_SESSION['exam_year'])) {
        $_SESSION['exam_year'] = $userYear;
        $_SESSION['tutor_user'] = $userYear;
    }

    $action = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action']) : 'dashboard';

    // --- ΕΛΕΓΧΟΣ LOGIN ---
    $login_error = '';
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        if (isset($_COOKIE['admin_remember'])) {
            $cookie_parts = explode('::', base64_decode($_COOKIE['admin_remember']));
            if (count($cookie_parts) === 2) {
                try {
                    if ($db->login($cookie_parts[0], $cookie_parts[1])) {
                        $_SESSION['admin_logged_in'] = true;
                        header("Location: index.php");
                        exit();
                    }
                } catch (Exception $e) {
                    setcookie('admin_remember', '', time() - 3600, "/");
                }
            }
        }

        if ($action === 'process_login' && isset($_POST['username']) && isset($_POST['password'])) {
            try {
                if ($db->login($_POST['username'], $_POST['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    if (isset($_POST['remember']) && $_POST['remember'] == '1') {
                        $cookie_val = base64_encode($_POST['username'] . '::' . $_POST['password']);
                        setcookie('admin_remember', $cookie_val, time() + (86400 * 30), "/");
                    }
                    header("Location: index.php");
                    exit();
                }
            } catch (Exception $e) {
                $login_error = "Λάθος όνομα χρήστη ή κωδικός.";
            }
        }
        $action = 'login';
    }

    if ($action === 'logout') {
        unset($_SESSION['admin_logged_in']);
        setcookie('admin_remember', '', time() - 3600, "/");
        header("Location: index.php");
        exit();
    }

    $noLayoutActions = ['export_word_exam', 'previewMeze', 'login', 'process_login'];

    if (!in_array($action, $noLayoutActions)) {
        $page->displayHeadMatter();
        $page->displayMenu($userYear, $db);
        if (isset($_GET['status'])) $page->showToast($_GET['status']);

        // --- ΓΕΝΕΘΛΙΑ ΑΥΡΙΟ (ADMIN) ---
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
        // --- ΤΕΛΟΣ ΓΕΝΕΘΛΙΩΝ ---
    }

    if ($action === 'login') {
        $page->displayLoginForm($login_error);
    } else {
        $actionMap = [
            // Theory
            'save_theory'           => 'actions/theory/manage.php',
            'add_theory'            => 'actions/theory/manage.php',
            'manage_books'          => 'actions/theory/manage.php',
            'save_book'             => 'actions/theory/manage.php',
            'delete_book'           => 'actions/theory/manage.php',
            'edit_theory'           => 'actions/theory/manage.php',
            'update_theory'         => 'actions/theory/manage.php',
            'delete_theory'         => 'actions/theory/manage.php',
            'list_theory'           => 'actions/theory/manage.php',
            'list_for_test'         => 'actions/theory/manage.php',
            'create_exam'           => 'actions/theory/manage.php',
            'export_word_exam'      => 'actions/theory/export.php',
            // Kena
            'addKena'               => 'actions/kena/manage.php',
            'saveKena'              => 'actions/kena/manage.php',
            'listKena'              => 'actions/kena/manage.php',
            'deleteKena'            => 'actions/kena/manage.php',
            // Thema G
            'addThemaG'             => 'actions/thema/manage.php',
            'saveThemaG'            => 'actions/thema/manage.php',
            'listThemaG'            => 'actions/thema/manage.php',
            'deleteThemaG'          => 'actions/thema/manage.php',
            // Mezedakia
            'massHideMezedakia'     => 'actions/meze/manage.php',
            'massDeleteSubmissions' => 'actions/meze/manage.php',
            'saveMezedaki'          => 'actions/meze/manage.php',
            'addMezedaki'           => 'actions/meze/manage.php',
            'mezeBank'              => 'actions/meze/manage.php',
            'manage_exercise_types' => 'actions/meze/manage.php',
            'save_exercise_type'    => 'actions/meze/manage.php',
            'delete_exercise_type'  => 'actions/meze/manage.php',
            'listMezedakia'         => 'actions/meze/manage.php',
            'deleteMezedaki'        => 'actions/meze/manage.php',
            'editMezedaki'          => 'actions/meze/manage.php',
            'updateMezedaki'        => 'actions/meze/manage.php',
            'previewMeze'           => 'actions/meze/preview.php',
            // Grades
            'manageGrades'          => 'actions/grades/manage.php',
            'saveGrades'            => 'actions/grades/manage.php',
            'deleteSpecificGrade'   => 'actions/reports/manage.php',
            // Settings
            'setYear'               => 'actions/settings/year.php',
            // Reports
            'fullReport'            => 'actions/reports/manage.php',
            'studentReport'         => 'actions/reports/manage.php',
            'viewStudentProfile'    => 'actions/reports/manage.php',
            'oldStudentReport'      => 'actions/reports/manage.php',
            // Submissions
            'viewSubmissions'       => 'actions/submissions/manage.php',
            'massGradeZero'         => 'actions/submissions/manage.php',
            'massEmailZero'         => 'actions/submissions/manage.php',
            'sendReminder'          => 'actions/submissions/manage.php',
            'quickGrade'            => 'actions/submissions/manage.php',
            'sendReportEmail'       => 'actions/submissions/manage.php',
            'deleteSubmission'      => 'actions/submissions/manage.php',
            // Extensions
            'giveExtension'         => 'actions/extensions/manage.php',
            'extendMezeForAll'      => 'actions/extensions/manage.php',
            'setMezeToday'          => 'actions/extensions/manage.php',
            'toggleGroupDeadline'   => 'actions/extensions/manage.php',
            'view_extension_requests' => 'actions/extensions/manage.php',
            'processExtension'      => 'actions/extensions/manage.php',
            'toggleMezeLock'        => 'actions/extensions/manage.php',
            // Groups & Tasks
            'print_group'           => 'actions/groups/manage.php',
            'assign_tasks'          => 'actions/groups/manage.php',
            'list_all_tasks'        => 'actions/groups/manage.php',
            'save_group_task'       => 'actions/groups/manage.php',
            'grade_task'            => 'actions/tasks/manage.php',
            'save_task_grades'      => 'actions/tasks/manage.php',
            // Announcements
            'manage_announcements'  => 'actions/announcements/manage.php',
            'add_announcement'      => 'actions/announcements/manage.php',
            'save_announcement'     => 'actions/announcements/manage.php',
            'edit_announcement'     => 'actions/announcements/manage.php',
            'update_announcement'   => 'actions/announcements/manage.php',
            'delete_announcement'   => 'actions/announcements/manage.php',
            'notify_announcement'   => 'actions/announcements/manage.php',
            // Emails & SMS
            'group_email_form'      => 'actions/emails/manage.php',
            'send_group_email'      => 'actions/emails/manage.php',
            'group_email_history'   => 'actions/emails/manage.php',
            'group_email_results'   => 'actions/emails/manage.php',
            'retry_failed_emails'   => 'actions/emails/manage.php',
            'mass_sms_form'         => 'actions/emails/manage.php',
            'send_mass_sms'         => 'actions/emails/manage.php',
            // Schools / Universities
            'importSchools'         => 'actions/schools/manage.php',
            'listSchools'           => 'actions/schools/manage.php',
            'deleteSchoolYear'      => 'actions/schools/manage.php',
            'allSchoolPreferences'  => 'actions/schools/manage.php',
        ];

        if (isset($actionMap[$action])) {
            require $actionMap[$action];
        } else {
            // Default: dashboard (λίστα μεζεδακίων - μόνο προγραμματισμένα + 5 πρόσφατα)
            $mezedakia = $db->getDashboardMezedakia();
            $totalMezeCount = $db->getTotalMezedakiaCount();
            if (empty($userYear)) {
                echo "<div class='container mt-4'><div class='alert alert-warning shadow-sm text-center'>
                        <i class='fa fa-user-circle'></i> Παρακαλώ πληκτρολογήστε το <b>Username</b> σας και πατήστε <b>Ορισμός</b> για να φορτώσουν τα μεζεδάκια.
                      </div></div>";
            } else {
                $dashStats    = $db->getDashboardSubmissionStats($userYear);
                $studentStats = $db->getStudentDashboardStats($userYear);
                $allStudents  = $db->getTutorStudents($userYear);
                $mezeFm->displayDashboardActivity($dashStats);
                $mezeFm->displayStudentOverview($allStudents, $studentStats);
            }
        }
    }

    if (!in_array($action, $noLayoutActions)) {
        $page->displayEndMatter();
    }
