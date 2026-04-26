<?php
session_start();

spl_autoload_register(function ($name) {
    if (file_exists($name . '.php')) {
        include_once $name . '.php';
    }
});

$page = new PageMaker();
$theory = new TheoryMaker();
$fm = new FormMaker();
$db = new DbHandler();
$page->displayHeadMatter();
$page->displayMenu();
?>

<!--<div class="container-fluid">-->
<!--<h1>ΑΕΠΠ</h1>-->
<?php // $page->megaliteros(); 
// Μέσα στο index.php, εκεί που διαχειρίζεσαι τα actions
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

switch ($action) {
    case 'listKenaDynamic':

        $exercises = $db->getAllKenaExercises();
        $page->displayKenaGallery($exercises);
        break;

    case 'showThemaGDForm': // Το νέο όνομα για το menu
        $fm->getThemataGDForm();
        break;

    case 'viewThemaGD': // Το action που στέλνει η φόρμα
        if (isset($_POST['viewThemaGD'])) {
            $year = $_POST['year'];
            $school = $_POST['typosSxoleiou'];
            $period = $_POST['typosEksetaseon'];
            $type = $_POST['thema_type']; // Γ ή Δ

            // Καλούμε τη νέα μέθοδο που θα φτιάξουμε στο DbHandler
            $result = $db->getThemaGDByCriteria($year, $school, $period, $type);

            $fm->getThemataGDForm();
            $page->displayThemaGD($result); // Το νέο PageMaker display
        }
        break;
    case 'viewMezedakia':
        $result = $db->getAllMezedakia();
        $page->displayMezedakiaList($result);
        break;

    case 'submitMezeAnswer':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $studentId = $_POST['student_id'];
            $mezeId = $_POST['meze_id'];
            $pass = $_POST['st_access'];
            $text = $_POST['student_text'];

            // Ενσωμάτωση των αυτόματων απαντήσεων από τα κενά (αν υπάρχουν)
            if (!empty($_POST['blanks_answers'])) {
                $text .= "<br><br><div style='background:#f8f9fa; padding:10px; border-left:3px solid #17a2b8; border-radius:4px;'><b>Απαντήσεις στα κενά:</b><br>" . nl2br(htmlspecialchars($_POST['blanks_answers'])) . "</div>";
            }

            // 1. Έλεγχος κωδικού (6 ψηφία) - Επιτρέπουμε και τα master passwords
            if (strlen($pass) != 6 && $pass !== $db->getCurrentTutorYear() && $pass !== date('Ym')) {
                echo "<script>alert('Ο κωδικός πρέπει να είναι ακριβώς 6 ψηφία!'); window.history.back();</script>";
                exit();
            }

            // 2. Έλεγχος αν ο κωδικός είναι σωστός
            if ($db->checkStudentPassword($studentId, $pass)) {
                // 3. Αποθήκευση
                $success = $db->saveMezeSubmission($studentId, $mezeId, $text, $_FILES['files']);
                if ($success) {
                    // ΑΝΤΙ ΓΙΑ ALERT: Εμφάνιση της σελίδας επιτυχίας
                    $page->displayMezeSuccess();
                    exit();
                } else {
                    echo "<div class='container mt-5'><div class='alert alert-danger'>Κάτι πήγε στραβά στην αποθήκευση. Παρακαλώ επικοινώνησε με τον δάσκαλο.</div></div>";
                }
            } else {
                echo "<script>alert('Λάθος κωδικός! Προσπάθησε ξανά.'); window.history.back();</script>";
            }
            exit();
        }
        break;

    case 'requestExtension':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $studentId = $_POST['student_id'];
            $mezeId = $_POST['meze_id'];
            $hours = (int)$_POST['requested_hours'];
            $currentYear = $db->getCurrentTutorYear();

            // Server-side έλεγχος προϋποθέσεων πριν την αποδοχή του αιτήματος
            $overallAverage = $db->getStudentOverallAverage($studentId, $currentYear);
            $grades = $db->getStudentGradesForStudent($studentId, $currentYear);
            $tempOnTime = 0;
            if (is_array($grades)) {
                foreach ($grades as $g) {
                    if ($g['is_on_time']) $tempOnTime++;
                }
            }
            $delaysCount = (is_array($grades) ? count($grades) : 0) - $tempOnTime;

            if ($overallAverage > 15 && $delaysCount <= 5) {
                $db->submitExtensionRequest($studentId, $mezeId, $hours, $currentYear);
                $page->displayRequestSuccess();
            } else {
                echo "<div class='container mt-5'><div class='alert alert-danger'>Δεν πληροίτε τις προϋποθέσεις για αίτημα παράτασης (Μ.Ο. > 15 και έως 5 καθυστερήσεις).</div></div>";
            }
            exit();
        }
        break;

    case 'myGrades':
        unset($_SESSION['student_id']);
        unset($_SESSION['student_pass']);
        $currentYear = $db->getCurrentTutorYear();
        $students = $db->getTutorStudents($currentYear);
?>
        <div class="container mt-5">
            <div class="card shadow mx-auto" style="max-width: 500px; border-radius: 15px; border: none;">
                <div class="card-header bg-primary text-white text-center py-3" style="border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fa fa-graduation-cap"></i> Οι Βαθμολογίες μου</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted text-center mb-4">Επιλέξτε το όνομά σας και πληκτρολογήστε τον προσωπικό σας κωδικό.</p>
                    <form action="index.php?action=showMyGrades" method="POST" autocomplete="off" onsubmit="return true;">
                        <!-- Hidden fields to confuse browser autofill -->
                        <input type="text" style="display:none"><input type="password" style="display:none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ονοματεπώνυμο</label>
                            <select name="student_id" class="form-select form-select-lg shadow-sm" required style="display: block; width: 100%;">
                                <option value="">--- Επιλέξτε από τη λίστα ---</option>
                                <?php if (is_array($students)) foreach ($students as $s): ?>
                                    <option value="<?php echo $s['studentId']; ?>"><?php echo $s['lastName'] . " " . $s['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Προσωπικός Κωδικός</label>
                            <div class="position-relative">
                                <input type="text" name="st_access" id="st_access_login"
                                    class="form-control form-control-lg text-center mask-input"
                                    placeholder="••••••" maxlength="15" required
                                    autocomplete="off"
                                    inputmode="numeric" pattern="[0-9]*"
                                    style="background-color: white; padding-right: 45px;">
                                <i class="fa fa-eye position-absolute top-50 translate-middle-y end-0 me-3"
                                    id="eye_login" style="cursor: pointer; color: #6c757d; z-index: 10;"
                                    onclick="toggleMask('st_access_login', 'eye_login')"></i>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm"><i class="fa fa-search"></i> Εμφάνιση Αποτελεσμάτων</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'showMyGrades':
        $currentYear = $db->getCurrentTutorYear();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_SESSION['student_id']) && isset($_SESSION['student_pass']))) {
            $studentId = $_POST['student_id'] ?? $_SESSION['student_id'];
            $pass = $_POST['st_access'] ?? $_SESSION['student_pass'];

            if ($db->checkStudentPassword($studentId, $pass)) {
                $_SESSION['student_id'] = $studentId;
                $_SESSION['student_pass'] = $pass;

                $grades = $db->getStudentGradesForStudent($studentId, $currentYear);
                $students = $db->getTutorStudents($currentYear);
                $overallAverage = $db->getStudentOverallAverage($studentId, $currentYear); // Νέα κλήση για τον μέσο όρο
                $groupTasks = $db->getStudentGroupTasks($studentId); // Ανάκτηση εργασιών ομάδας
                $financials = $db->getStudentFinancials($studentId); // Ανάκτηση οικονομικών

                // Υπολογισμός στατιστικών για τον έλεγχο δικαιώματος παράτασης (Strict Rules)
                $tempOnTime = 0;
                if (is_array($grades)) {
                    foreach ($grades as $g) {
                        if ($g['is_on_time']) $tempOnTime++;
                    }
                }
                $delaysCount = (is_array($grades) ? count($grades) : 0) - $tempOnTime;
                $canRequestExtension = ($overallAverage > 15 && $delaysCount <= 5);

                $allMezedakia = $db->getAllMezedakia();
                $pendingRequestIds = is_object($db) ? $db->getStudentPendingRequests($studentId) : [];

                $fullName = "";
                $gradedMezeIds = is_array($grades) ? array_column($grades, 'mezeNumber') : [];
                if (is_array($students)) foreach ($students as $s) {
                    if ($s['studentId'] == $studentId) $fullName = $s['name'] . " " . $s['lastName'];
                }
        ?>
                <div class="container mt-4 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h3 class="text-primary mb-0"><i class="fa fa-user-circle"></i> <?php echo $fullName; ?></h3>
                        <a href="index.php?action=myGrades" class="btn btn-outline-secondary btn-sm"><i class="fa fa-sign-out"></i> Έξοδος</a>
                    </div>

                    <!-- Navigation tabs για τον μαθητή -->
                    <ul class="nav nav-pills mb-4 justify-content-center border-0" id="studentTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="grades-tab" data-bs-toggle="pill" data-bs-target="#grades" href="#grades" role="tab"><i class="fa fa-star"></i> Βαθμοί (Μεζεδάκια)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="task-grades-tab" data-bs-toggle="pill" data-bs-target="#task-grades" href="#task-grades" role="tab"><i class="fa fa-check-square-o"></i> Βαθμοί (Εργασίες)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tasks-tab" data-bs-toggle="pill" data-bs-target="#tasks" href="#tasks" role="tab"><i class="fa fa-tasks"></i> Οι Εργασίες μου</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="financials-tab" data-bs-toggle="pill" data-bs-target="#financials" href="#financials" role="tab"><i class="fa fa-calendar-check-o"></i> Τα Μαθήματά μου</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="studentTabsContent">
                        <!-- Tab Βαθμολογιών -->
                        <div class="tab-pane fade show active" id="grades" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover shadow-sm align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 150px;">Μεζεδάκι</th>
                                            <th>Συνέπεια</th>
                                            <th>Βαθμός</th>
                                            <th>Σχόλια Καθηγητή</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $onTimeCount = 0;
                                        $totalGrades = is_array($grades) ? count($grades) : 0;
                                        if (empty($grades) || !is_array($grades)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted italic">Δεν έχουν βρεθεί ακόμα βαθμολογίες.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($grades as $g):
                                                if ($g['is_on_time']) $onTimeCount++; ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">#<?php echo $g['mezeNumber']; ?></div>
                                                        <small class="text-muted"><?php echo $db->formatGreekDate($g['mezeDate']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($g['is_on_time']): ?>
                                                            <span class="badge bg-success" title="Εμπρόθεσμη υποβολή"><i class="fa fa-check-circle"></i> Εντός</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark" title="Εκπρόθεσμη υποβολή (με παράταση)"><i class="fa fa-clock-o"></i> Εκτός</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="badge rounded-pill bg-<?php echo $g['grade_value'] >= 10 ? 'success' : 'danger'; ?> px-3 py-2 fs-6">
                                                            <?php echo $g['grade_value']; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-wrap" style="max-width: 400px;"><?php echo !empty($g['teacher_comments']) ? $g['teacher_comments'] : '<span class="text-muted small">Χωρίς σχόλια.</span>'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="2" class="text-end py-2">ΣΥΝΟΛΙΚΗ ΣΥΝΕΠΕΙΑ:</th>
                                            <th class="text-center py-2 fs-5">
                                                <?php
                                                $perc = ($totalGrades > 0) ? ($onTimeCount / $totalGrades) * 100 : 0;
                                                $color = ($perc >= 80) ? 'text-success' : (($perc >= 50) ? 'text-warning' : 'text-danger');
                                                echo "<span class='$color'>" . number_format($perc, 0) . "%</span>";
                                                ?>
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-end py-2">Μ.Ο. ΜΕΖΕΔΑΚΙΩΝ:</th>
                                            <th class="text-center py-2 fs-5 text-danger"><?php echo number_format($overallAverage, 2); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Νέα Ενότητα: Αιτήματα Παράτασης -->
                            <div class="mt-5">
                                <h5 class="text-secondary border-bottom pb-2 mb-3"><i class="fa fa-clock-o"></i> Ζήτησε Παράταση για Υποβολή ή Βελτίωση</h5>

                                <?php if (!$canRequestExtension): ?>
                                    <div class="alert alert-light border text-muted small shadow-sm">
                                        <i class="fa fa-lock"></i> Η δυνατότητα αιτήματος παράτασης είναι διαθέσιμη μόνο για μαθητές με <b>Μ.Ο. > 15</b> και έως <b>5 καθυστερήσεις</b> στην υποβολή των μεζεδακίων. (Τρέχοντα: Μ.Ο. <?php echo number_format($overallAverage, 2); ?>, Καθυστερήσεις: <?php echo $delaysCount; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php
                                        $foundExpired = false;
                                        if ($allMezedakia && is_object($allMezedakia)) {
                                            $allMezedakia->data_seek(0);
                                            while ($m = $allMezedakia->fetch_assoc()):
                                                $mId = $m['mezeId'];
                                                $mNum = $m['mezeNumber'];

                                                $currentMezeGrade = null;
                                                $isGraded = false;
                                                foreach ($grades as $g) {
                                                    if ($g['mezeNumber'] == $mNum) {
                                                        $isGraded = true;
                                                        $currentMezeGrade = $g['grade_value'];
                                                        break;
                                                    }
                                                }

                                                $isExpired = (strtotime($m['solutionDate']) < time());
                                                $hasActiveExt = $db->isSubmissionAllowed($studentId, $mId, $currentYear);

                                                // Επιτρέπουμε αίτημα αν δεν έχει βαθμολογηθεί Ή αν ο βαθμός είναι < 15
                                                $isEligibleForImprovement = ($isGraded && $currentMezeGrade < 15);

                                                if ($isExpired && (!$isGraded || $isEligibleForImprovement) && !$hasActiveExt):
                                                    $foundExpired = true;
                                                    $isPending = in_array($mId, $pendingRequestIds);
                                        ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>Μεζεδάκι #<?php echo $mNum; ?></strong>
                                                            <?php if ($isGraded) echo "<span class='badge bg-info text-dark ms-1'>Για βελτίωση ($currentMezeGrade)</span>"; ?>
                                                            <div class="small text-muted">Προθεσμία: <?php echo $db->formatGreekDate($m['solutionDate']) . " " . date('H:i', strtotime($m['solutionDate'])); ?></div>
                                                        </div>
                                                        <?php if ($isPending): ?>
                                                            <span class="badge bg-warning text-dark pulse-pending"><i class="fa fa-hourglass-start"></i> Εκκρεμεί έγκριση</span>
                                                        <?php else: ?>
                                                            <form action="index.php?action=requestExtension" method="POST" class="d-flex align-items-center" autocomplete="off">
                                                                <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                                                                <input type="hidden" name="meze_id" value="<?php echo $mId; ?>">
                                                                <select name="requested_hours" class="form-select form-select-sm me-2" style="width: 100px;">
                                                                    <option value="12">12 ώρες</option>
                                                                    <option value="24" selected>24 ώρες</option>
                                                                    <option value="48">48 ώρες</option>
                                                                </select>
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="this.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i>'; this.classList.add('disabled');">Αίτημα</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                        <?php endif;
                                            endwhile;
                                        } ?>
                                        <?php if (!$foundExpired): ?>
                                            <div class="text-muted small italic">Δεν υπάρχουν ληγμένα μεζεδάκια χωρίς βαθμολογία.</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tab Βαθμολογιών Εργασιών -->
                        <div class="tab-pane fade" id="task-grades" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover shadow-sm align-middle">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Εργασία / Περιγραφή</th>
                                            <th style="width: 120px;">Βαθμός</th>
                                            <th>Σχόλια Καθηγητή</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $hasTaskGrades = false;
                                        foreach ($groupTasks as $task):
                                            if (isset($task['grade_value']) && $task['grade_value'] !== null):
                                                $hasTaskGrades = true;
                                        ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($task['task_text']); ?></div>
                                                        <small class="text-muted"><?php echo $db->formatGreekDate($task['date_added']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="badge rounded-pill bg-<?php echo $task['grade_value'] >= 10 ? 'success' : 'danger'; ?> px-3 py-2 fs-6">
                                                            <?php echo $task['grade_value']; ?> / 20
                                                        </div>
                                                    </td>
                                                    <td class="text-wrap" style="max-width: 400px;"><?php echo !empty($task['teacher_comments']) ? $task['teacher_comments'] : '<span class="text-muted small">Χωρίς σχόλια.</span>'; ?></td>
                                                </tr>
                                            <?php
                                            endif;
                                        endforeach;

                                        if (!$hasTaskGrades): ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted italic">Δεν έχουν βρεθεί ακόμα βαθμολογίες για εργασίες.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Εργασιών -->
                        <div class="tab-pane fade" id="tasks" role="tabpanel">
                            <?php if (!empty($groupTasks)): ?>
                                <div class="list-group shadow-sm">
                                    <div class="list-group-item bg-info text-white">
                                        <h5 class="mb-0 text-white"><i class="fa fa-book text-white"></i> Αναθέσεις για την ομάδα: <?php echo $groupTasks[0]['group_name']; ?></h5>
                                    </div>
                                    <?php foreach ($groupTasks as $task): ?>
                                        <div class="list-group-item">
                                            <?php if (!empty($task['book_title'])): ?>
                                                <span class="badge bg-dark mb-2"><?php echo $task['book_title']; ?></span>
                                            <?php endif; ?>

                                            <p class="mb-1 h5 text-dark"><?php echo nl2br($task['task_text']); ?></p>

                                            <?php if (isset($task['grade_value']) && $task['grade_value'] !== null): ?>
                                                <div class="mt-2 mb-2">
                                                    <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">Βαθμός: <?php echo $task['grade_value']; ?> / 20</span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($task['task_file'])): ?>
                                                <div class="mt-2">
                                                    <a href="uploads/tasks/<?php echo $task['task_file']; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa fa-file-pdf-o"></i> Προβολή Αρχείου / PDF
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <small class="text-muted d-block mt-2"><i class="fa fa-calendar"></i> Ημερομηνία ανάθεσης: <?php echo $db->formatGreekDate($task['date_added']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light border text-center py-4">
                                    <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
                                    <p class="mb-0">Δεν υπάρχουν καταχωρημένες εργασίες για την ομάδα σου αυτή τη στιγμή.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Οικονομικών -->
                        <div class="tab-pane fade" id="financials" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6 mx-auto text-center">
                                    <div class="alert <?php echo $financials['balance'] > 0 ? 'alert-danger' : 'alert-success'; ?> shadow-sm">
                                        <h6 class="mb-1">
                                            Μαθήματα μέχρι σήμερα
                                            <?php if (!empty($financials['items'])) echo " (" . count($financials['items']) . " μαθήματα)"; ?>
                                        </h6>
                                        <!-- <h4 class="mb-0 font-weight-bold"><?php echo number_format($financials['balance'], 2); ?> €</h4> -->
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover shadow-sm align-middle">
                                    <thead class="table-warning">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Ημερομηνία</th>
                                            <th>Περιγραφή</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($financials['items'])): ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-success italic">Δεν υπάρχουν εκκρεμή μαθήματα.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $idx = 1;
                                            foreach ($financials['items'] as $item): ?>
                                                <tr>
                                                    <td class="text-muted small"><?php echo $idx++; ?></td>
                                                    <td class="font-weight-bold"><?php echo $db->formatGreekDate($item['date']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary px-3 py-2">Μάθημα</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="small text-muted mt-3 italic text-center"><i class="fa fa-info-circle"></i> Εμφανίζονται μόνο οι ημερομηνίες των μαθημάτων που δεν έχουν εξοφληθεί ακόμα.</p>
                        </div>
                    </div>
                </div>
        <?php
            } else {
                unset($_SESSION['student_id']);
                unset($_SESSION['student_pass']);
                echo "<div class='container mt-5 text-center'><div class='alert alert-danger d-inline-block px-5 shadow'><h4><i class='fa fa-exclamation-triangle'></i> Λάθος Κωδικός</h4><p>Παρακαλώ προσπαθήστε ξανά.</p><hr><a href='index.php?action=myGrades' class='btn btn-danger'>Επιστροφή</a></div></div>";
            }
        } else {
            header("Location: index.php?action=myGrades");
            exit();
        }
        break;

    case 'home':
    default:
        ?>



        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="max">Μεγαλύτερος - Μικρότερος</h4>
                    <p>Για να βρούμε τον μεγαλύτερο (ή τον μικρότερο) από δύο η περισσότερους αριθμούς
                        βάζουμε τον πρώτο μέσα στο max (ή στο min) και συγκρίνουμε το max (ή το min) με όλους τους άλλους</p>
                    <p><b>ΠΡΟΣΟΧΗ: Χρησιμοποιούμε μόνο ΔΟΜΗ ΑΠΛΗΣ ΕΠΙΛΟΓΗΣ</b></p>
                    <p>Σε περίπτωση που γνωρίζουμε το διάστημα των τιμών μπορούμε να βάλουμε
                        στον max την μικρότερη πιθανή τιμή -1 και στο min την μεγαλύτερη πιθανή τιμή +1
                        (πχ αν οι τιμές είναι από 1 έως 20 τότε βάζουμε στο max το 0 και στο min το 21)</p>
                    <h5>Μεγαλύτερος από 3</h5>
                    <pre>
ΔΙΑΒΑΣΕ α,β,γ                            
μεγ <- α
ΑΝ β > μεγ ΤΟΤΕ
    μεγ <- β
ΤΕΛΟΣ_ΑΝ
ΑΝ γ > μεγ ΤΟΤΕ
    μεγ <- γ
ΤΕΛΟΣ_ΑΝ
            </pre>
                    <h5>Άγνωστο διάστημα τιμών</h5>
                    <pre>
ΔΙΑΒΑΣΕ χ
max <- χ
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 30
    ΔΙΑΒΑΣΕ χ
    ΑΝ χ > max ΤΟΤΕ
        max <- χ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
    ΔΙΑΒΑΣΕ χ
    ΑΝ i = 1 ΤΟΤΕ
        max <- χ
    ΤΕΛΟΣ_ΑΝ
    ΑΝ χ > max ΤΟΤΕ
        max <- χ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                    <p> Η καλύτερα</p>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
    ΔΙΑΒΑΣΕ χ
    ΑΝ i = 1 ΤΟΤΕ
        max <- χ
    ΑΛΛΙΩΣ
        ΑΝ χ > max ΤΟΤΕ
            max <- χ
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                    <h5>Τιμές [1,20]</h5>
                    <pre>
max <- 0
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
    ΔΙΑΒΑΣΕ χ
    ΑΝ χ > max ΤΟΤΕ
        max <- χ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ                            
            </pre>

                    <h5> Μεγαλύτερος άρτιος από 30 ακεραίους</h5>
                    <pre>
πρώτος <- ΑΛΗΘΗΣ
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
    ΔΙΑΒΑΣΕ χ
    ΑΝ χ mod 2 = 0  ΤΟΤΕ
        ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
            max <- χ
            πρώτος <- ΨΕΥΔΗΣ
        ΑΛΛΙΩΣ_ΑΝ χ > max ΤΟΤΕ
            max <- χ
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΑΝ πρώτος = ΨΕΥΔΗΣ ΤΟΤΕ
    ΓΡΑΨΕ 'Μεγαλύτερος άρτιος ο',max
ΑΛΛΙΩΣ
    ΓΡΑΨΕ 'Δεν δώθηκε κανένας άρτιος'
ΤΕΛΟΣ_ΑΝ                           
            </pre>

                    <h5> Μεγαλύτερος άρτιος μέχρι να δωθεί το 0</h5>
                    <pre>
πρώτος <- ΑΛΗΘΗΣ
ΔΙΑΒΑΣΕ χ
ΟΣΟ χ <> 0 ΕΠΑΝΑΛΑΒΕ    
    ΑΝ χ mod 2 = 0  ΤΟΤΕ
        ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
            max <- χ
            πρώτος <- ΨΕΥΔΗΣ
        ΑΛΛΙΩΣ_ΑΝ χ > max ΤΟΤΕ
            max <- χ
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΑΝ
    ΔΙΑΒΑΣΕ χ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΑΝ πρώτος = ΨΕΥΔΗΣ ΤΟΤΕ
    ΓΡΑΨΕ 'Μεγαλύτερος άρτιος ο',max
ΑΛΛΙΩΣ
    ΓΡΑΨΕ 'Δεν δώθηκε κανένας άρτιος'
ΤΕΛΟΣ_ΑΝ                           
            </pre>

                    <h5>Δύο μεγαλύτεροι από άγνωστο αριθμό <b>θετικών</b> ακεραίων</h5>
                    <pre>
ΠΡΟΓΡΑΜΜΑ μεγαλύτεροι
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: χ, μαξμαξ, μαξ
ΑΡΧΗ
  μαξμαξ <- -1
  μαξ <- -1
  ΓΡΑΨΕ 'Δώστε ακέραιο αριθμό'
  ΔΙΑΒΑΣΕ χ
  ΟΣΟ χ <> -1 ΕΠΑΝΑΛΑΒΕ
    ΑΝ χ > μαξμαξ ΤΟΤΕ
      μαξ <- μαξμαξ
      μαξμαξ <- χ
    ΑΛΛΙΩΣ_ΑΝ χ > μαξ ΚΑΙ χ <> μαξμαξ ΤΟΤΕ
      μαξ <- χ
    ΤΕΛΟΣ_ΑΝ
    ΓΡΑΨΕ 'Δώστε ακέραιο αριθμό'
    ΔΙΑΒΑΣΕ χ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΑΝ μαξμαξ <> -1 ΚΑΙ μαξ <> -1 ΤΟΤΕ
    ΓΡΑΨΕ 'Μεγαλύτερος είναι ο ', μαξμαξ
    ΓΡΑΨΕ 'Επόμενος είναι ο ', μαξ
  ΑΛΛΙΩΣ_ΑΝ μαξ = -1 ΤΟΤΕ
    ΓΡΑΨΕ 'Δώθηκε μόνο ένας αριθμός ο ', μαξ
  ΑΛΛΙΩΣ
    ΓΡΑΨΕ 'Δεν δώθηκε κανένας έγκυρος αριθμός'
  ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ                         </pre>

                    <h5>Δύο μεγαλύτεροι από άγνωστο αριθμό ακεραίων και <b>άγνωστο διάστημα τιμών</b></h5>
                    <pre>
ΠΡΟΓΡΑΜΜΑ δύοΜεγαλύτεροι
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: χ, μαξ, μαξμαξ
  ΛΟΓΙΚΕΣ: πρώτος, δεύτερος
ΑΡΧΗ
  μαξ <- -1
  μαξμαξ <- -1
  πρώτος <- ΑΛΗΘΗΣ
  δεύτερος <- ΨΕΥΔΗΣ
  ΓΡΑΨΕ 'Δώστε ακέραιο η -1 για τέλος'
  ΔΙΑΒΑΣΕ χ
  ΟΣΟ χ <> -1 ΕΠΑΝΑΛΑΒΕ
    ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
      μαξμαξ <- χ
      πρώτος <- ΨΕΥΔΗΣ
      δεύτερος <- ΑΛΗΘΗΣ
    ΑΛΛΙΩΣ_ΑΝ δεύτερος = ΑΛΗΘΗΣ ΤΟΤΕ
      ΑΝ χ > μαξμαξ ΤΟΤΕ
        μαξ <- μαξμαξ
        μαξμαξ <- χ
      ΑΛΛΙΩΣ
        μαξ <- χ
      ΤΕΛΟΣ_ΑΝ
      δεύτερος <- ΨΕΥΔΗΣ
    ΑΛΛΙΩΣ
      ΑΝ χ > μαξμαξ ΤΟΤΕ
        μαξ <- μαξμαξ
        μαξμαξ <- χ
      ΑΛΛΙΩΣ_ΑΝ χ > μαξ ΤΟΤΕ
        μαξ <- χ
      ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΑΝ
    ΓΡΑΨΕ 'Δώστε ακέραιο η -1 για τέλος'
    ΔΙΑΒΑΣΕ χ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΑΝ δεύτερος = ΑΛΗΘΗΣ ΤΟΤΕ
    ΓΡΑΨΕ 'δώθηκε μόνο ένας αριθμός ο ', μαξμαξ
  ΑΛΛΙΩΣ
    ΓΡΑΨΕ 'μεγαλύτερος ο ', μαξμαξ
    ΓΡΑΨΕ 'επόμενος ο ', μαξ
  ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ 
            </pre>
                    <h5>Πόσοι μαθητές είχαν τον μεγαλύτερο βαθμό</h5>
                    <pre>
ΠΡΟΓΡΑΜΜΑ πόσεςΦορέςΟΜεγαλύτεροςΒαθμός
ΣΤΑΘΕΡΕΣ
  ν = 5
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: βαθ, μαξ, πλήθος, ι
ΑΡΧΗ
  μαξ <- 0
  ΓΙΑ ι ΑΠΟ 1 ΜΕΧΡΙ ν
    ΓΡΑΨΕ 'Δώστε βαθμό'
    ΔΙΑΒΑΣΕ βαθ
    ΑΝ βαθ > μαξ ΤΟΤΕ
      μαξ <- βαθ
      πλήθος <- 1
    ΑΛΛΙΩΣ_ΑΝ βαθ = μαξ ΤΟΤΕ
      πλήθος <- πλήθος + 1
    ΤΕΛΟΣ_ΑΝ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΓΡΑΨΕ 'Τον μεγαλύτερο βαθμό είχαν ', πλήθος, ' μαθητές'
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ 
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="akatalili">Ακατάλληλη Τιμή</h4>
                    <p>Όταν ένας αλγόριθμος τελειώνει με "Ακατάλληλη Τιμή" (τιμή φρουρό)
                        τότε μπορούμε να χρησιμοποιήσουμε το ΟΣΟ (συνθήκη) ΕΠΑΝΑΛΑΒΕ και
                        διαβάζουμε τιμή: μια φορά πριν μπούμε και μια φορά πριν βγούμε</p>
                    <h6>Παράδειγμα</h6>
                    <pre>
ΠΡΟΓΡΑΜΜΑ ακατάλληλη_Τιμή
ΜΕΤΑΒΛΗΤΕΣ
ΑΚΕΡΑΙΕΣ:μ
ΠΡΑΓΜΑΤΙΚΕΣ:σ,μο,βαθμό
ΑΡΧΗ
σ <- 0
μ <- 0
ΓΡΑΨΕ 'δώσε βαθμό Η 0 για τέλος'
ΔΙΑΒΑΣΕ βαθμό
ΟΣΟ βαθμό > 0 ΕΠΑΝΑΛΑΒΕ
    σ <- σ + βαθμό
    μ <- μ + 1
    ΓΡΑΨΕ 'δώσε βαθμό Η 0 για τέλος'
    ΔΙΑΒΑΣΕ βαθμό
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΑΝ μ <> 0 ΤΟΤΕ
    μο <- σ/μ
    ΓΡΑΨΕ μο
ΑΛΛΙΩΣ 
    ΓΡΑΨΕ 'Δεν δώθηκε κανένας βαθμός'
ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
            </pre>
                    <h5>Διαθέσιμες Ποσότητες (όχι ακατάλληλη τιμή αλλά μοιάζει)</h5>
                    <pre>
ΠΡΟΓΡΑΜΜΑ άσκηση14_28Κοψίνης1
ΜΕΤΑΒΛΗΤΕΣ
  ΠΡΑΓΜΑΤΙΚΕΣ: τιμή, τσέπη, υπόλοιπο
  ΧΑΡΑΚΤΗΡΕΣ: προέλ
  ΑΚΕΡΑΙΕΣ: πλξ, πλε
ΑΡΧΗ
  τσέπη <- 1500
  πλξ <- 0
  πλε <- 0
  ΓΡΑΨΕ 'Δώστε τιμή   '
  ΔΙΑΒΑΣΕ τιμή
  ΟΣΟ τιμή <= τσέπη ΕΠΑΝΑΛΑΒΕ
        τσέπη <- τσέπη - τιμή
        ΓΡΑΨΕ 'Δώστε προέλευση "ξένο" η "ελληνικό"   '
        ΔΙΑΒΑΣΕ προέλ
        ΑΝ προέλ = 'ξένο' ΤΟΤΕ
            πλξ <- πλξ + 1
        ΑΛΛΙΩΣ
            πλε <- πλε + 1
        ΤΕΛΟΣ_ΑΝ
        ΓΡΑΨΕ 'Δώστε τιμή   '
        ΔΙΑΒΑΣΕ τιμή
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΓΡΑΨΕ 'τέλος αγορών'
  ΑΝ τσέπη > 0 ΤΟΤΕ
        ΓΡΑΨΕ 'Περίσσεψαν  ', τσέπη
        υπόλοιπο <- 1500 - τσέπη
        ΓΡΑΨΕ 'Ξόδεψα   ', 1500 - τσέπη
        ΓΡΑΨΕ 'Αγόρασα  ',πλξ,'  ξένα  και  ',πλε,'   ελληνικά'
  ΑΛΛΙΩΣ
        ΓΡΑΨΕ 'Τα ξόδεψα όλα '
  ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4 id="egirotita">Έλεγχος εγκυρότητας με ΟΣΟ</h4>
                    <pre>
ΓΡΑΨΕ 'Δώστε βαθμό'
ΔΙΑΒΑΣΕ βαθμό
ΟΣΟ βαθμό < 1 Η βαθμό > 20 ΕΠΑΝΑΛΑΒΕ
    ΓΡΑΨΕ 'Δώσατε λάθος, δώστε [1,20]
    ΔΙΑΒΑΣΕ βαθμό
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Έλεγχος εγκυρότητας με ΜΕΧΡΙΣ_ΟΤΟΥ</h4>
                    <pre>
ΑΡΧΗ_ΕΠΑΝΑΛΗΨΗΣ
    ΓΡΑΨΕ 'Δώσε βαθμό'
    ΔΙΑΒΑΣΕ βαθμό
ΜΕΧΡΙΣ_ΟΤΟΥ βαθμό >= 1 ΚΑΙ βαθμό <= 20
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Έλεγχος εγκυρότητας με ΜΕΧΡΙΣ_ΟΤΟΥ και μήνυμα λάθους 1</h4>
                    <pre>
ΓΡΑΨΕ 'Δώσε βαθμό'
ΔΙΑΒΑΣΕ βαθμό
ΑΝ βαθμό < 1 Η βαθμό > 20 ΤΟΤΕ
    ΑΡΧΗ_ΕΠΑΝΑΛΗΨΗΣ
        ΓΡΑΨΕ 'Δώσατε λάθος, δώστε [1,20]
        ΔΙΑΒΑΣΕ βαθμό
    ΜΕΧΡΙΣ_ΟΤΟΥ βαθμό >= 1 ΚΑΙ βαθμό <= 20
ΤΕΛΟΣ_ΑΝ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Έλεγχος εγκυρότητας με ΜΕΧΡΙΣ_ΟΤΟΥ και μήνυμα λάθους 2</h4>
                    <pre>
ΓΡΑΨΕ 'Δώσε βαθμό'
ΑΡΧΗ_ΕΠΑΝΑΛΗΨΗΣ
    ΔΙΑΒΑΣΕ βαθμό
    ΑΝ βαθμό < 1 Η βαθμό > 20 ΤΟΤΕ
        ΓΡΑΨΕ 'Δώσατε λάθος, δώστε [1,20]
    ΤΕΛΟΣ_ΑΝ    
ΜΕΧΡΙΣ_ΟΤΟΥ βαθμό >= 1 ΚΑΙ βαθμό <= 20
ΤΕΛΟΣ_ΑΝ
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="1d">Εισαγωγή στους πίνακες </h4>

                    <p>Εισαγωγή στους πίνακες <a href="https://youtu.be/vzbXXgeuh5U"><b>στο YouTube</b></a></p>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="1d">Διάβασμα πίνακα </h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 20
    ΔΙΑΒΑΣΕ Π[i]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ  
            </pre>
                    <p>Παρουσίαση του διαβάσματος μονοδιάστατου πίνακα <a href="https://youtu.be/bJ_fF1VM7YQ"><b>στο YouTube</b></a></p>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Άθροισμα πίνακα </h4>
                    <pre>
Σ <- 0
ΓΙΑ i ΑΠΌ 1 ΜΕΧΡΙ 20
    Σ <- Σ + Π[i]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ  
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Μέσος Όρος πίνακα </h4>
                    <pre>
Σ <- 0
ΓΙΑ i ΑΠΌ 1 ΜΕΧΡΙ 20
    Σ <- Σ + Π[i]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΜΟ <- Σ/20  
            </pre>
                    <p>Μέσος όρος μονοδιάστατου πίνακα <a href="https://youtu.be/rMOs-0Uep2Y"><b>στο YouTube</b></a></p>
                    <p>Πόσοι είναι πάνω από τον μέσο όρο <a href="https://youtu.be/cyAFX7s61Qw"><b>στο YouTube</b></a></p>
                </div>
            </div>
        </div>


        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Σειριακή Αναζήτηση </h4>
                    <pre>
ΔΙΑΒΑΣΕ χ
βρέθηκε <- ΨΕΥΔΗΣ
i <- 1
ΟΣΟ i <= 50 ΚΑΙ βρέθηκε = ΨΕΥΔΗΣ ΕΠΑΝΑΛΑΒΕ
    ΑΝ Π[i] = χ ΤΟΤΕ
        βρέθηκε <- ΑΛΗΘΗΣ
        θέση <- i
    ΑΛΛΙΩΣ
        i <- i + 1
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ     
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ταξινόμηση Ευθείας Ανταλλαγής</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 30
    ΓΙΑ j ΑΠΟ 30 ΜΕΧΡΙ i ME BHMA -1
        AN Π[j-1] > Π[j] ΤΟΤΕ
            temp <- Π[j-1]
            Π[j-1] <- Π[j]
            Π[j] <- temp
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ     
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Μεγαλύτερος Πίνακα</h4>
                    <pre>
ΠΡΟΓΡΑΜΜΑ μεγαλύτεροςΠίνακα
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: π[5], ι, μαξ, θμαξ
ΑΡΧΗ
  ΓΙΑ ι ΑΠΟ 1 ΜΕΧΡΙ 5
      ΔΙΑΒΑΣΕ π[ι] 
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ


  !βρίσκω τον μεγαλύτερο
  μαξ <- π[1] 
  ΓΙΑ ι ΑΠΟ 2 ΜΕΧΡΙ 5
      ΑΝ π[ι] > μαξ ΤΟΤΕ
         μαξ <- π[ι] 
      ΤΕΛΟΣ_ΑΝ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΓΡΑΨΕ 'Μεγαλύτερος είναι ο  ', μαξ



  !βρίσκω την θέση του μεγαλύτερου
  θμαξ <- 1
  ΓΙΑ ι ΑΠΟ 2 ΜΕΧΡΙ 5
      ΑΝ π[ι] > π[θμαξ] ΤΟΤΕ
         θμαξ <- ι
      ΤΕΛΟΣ_ΑΝ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΓΡΑΨΕ 'Μεγαλύτερος είναι ξανά ο  ', π[θμαξ]
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ      
            </pre>
                    <!-- <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>comment -->
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Το όνομα του καλύτερου μαθητή</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 20
    ΔΙΑΒΑΣΕ όνομα[i],βαθμός[i]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ  
max <- βαθμός[1]
όνομαMax <- όνομα[1]
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 30
    ΑΝ βαθμός[i] > max TOTE
        max <- βαθμός[i]
        όνομαMax <- όνομα[i]
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΓΡΑΨΕ 'Καλύτερος μαθητής ο',όνομαMax    
            </pre>
                    <!-- <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a> -->
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Τα ονόματα των μαθητών με τον μεγαλύτερο βαθμό</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 20
    ΔΙΑΒΑΣΕ όνομα[i],βαθμός[i]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ  
max <- βαθμός[1]
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 20
    ΑΝ βαθμός[i] > max TOTE
        max <- βαθμός[i]       
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 20
    ΑΝ βαθμός[i] = max TOTE
        ΓΡΑΨΕ όνομα[ι]
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ    
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="2d">Διάβασμα πίνακα δύο διαστάσεων μαζί με μονοδιάστατο</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    ΔΙΑΒΑΣΕ ΟΝ[i]
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        ΔΙΑΒΑΣΕ Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>


        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Άθροισμα και Μέσος Όρος Πίνακα</h4>
                    <pre>
Σ <- 0
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10        
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        Σ <- Σ + Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
μο <- Σ/80
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Άθροισμα γραμμών</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    Σ[i] <- 0
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        Σ[i] <- Σ[i] + Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μέσος όρος γραμμών</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    Σ <- 0
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        Σ <- Σ + Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    μο[i] <- Σ/8
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <img src="images/athrismaMoGrammon.webp" class="img-fluid" alt="...">
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Άθροισμα Στηλών</h4>
                    <pre>
ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8    
    Σ[j] <- 0
    ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
        Σ[j] <- Σ[j] + Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μέσος όρος στηλών</h4>
                    <pre>
ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8 
    Σ <- 0
    ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
        Σ <- Σ + Π[i,j]
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    μο[j] <- Σ/10
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <img src="images/mesosOrosStilon.webp" class="img-fluid" alt="...">
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος πίνακα</h4>
                    <pre>
max <- Π[1,1]
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        ΑΝ Π[i,j] > max TOTE
            max <- Π[i,j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <img src="images/maxPinaka.webp" class="img-fluid" alt="...">
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτεροι γραμμών</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    max[i] <- Π[i,1]
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
        ΑΝ Π[i,j] > max[i] ΤΟΤΕ
            max[i] <- Π[i,j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτεροι στηλών</h4>
                    <pre>
ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
    max[j] <- Π[1,j]
    ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
        ΑΝ Π[i,j] > max[j] ΤΟΤΕ
            max[j] <- Π[i,j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος 3ης γραμμής (Π[5,10])</h4>
                    <pre>
max <- Π[3,1]
ΓΙΑ j ΑΠΟ 2 ΜΕΧΡΙ 8
    ΑΝ Π[3,j] > max ΤΟΤΕ
        max[i] <- Π[3,j]
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος 3ης στήλης (Π[5,10])</h4>
                    <pre>
max <- Π[1,3]
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 10
    ΑΝ Π[i,3] > max ΤΟΤΕ
        max[i] <- Π[i,3]
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μέσος όρος 3ης γραμμής (Π[5,10])</h4>
                    <pre>
Σ <- 0
ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 8
    Σ <- Σ + Π[3,j]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
MO <- Σ/8
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μέσος όρος 3ης στήλης (Π[5,10])</h4>
                    <pre>
Σ <- 0
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 10
    Σ <- Σ + Π[i,3]
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
MO <- Σ/10
            </pre>
                </div>

            </div>
        </div>


        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος διαγωνίου i = j (Π[5,5]) Α Τρόπος</h4>
                    <pre>
max <- Π[1,1]
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 5
    ΑΝ Π[i,i] > max
        max <- Π[i,i]
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος διαγωνίου i = j (Π[5,5]) Β Τρόπος</h4>
                    <pre>
max <- Π[1,1]
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 5
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 5
        ΑΝ Π[i,j] > max ΚΑΙ i = j ΤΟΤΕ
            max <- Π[i,j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Μεγαλύτερος διαγωνίου i + j = 6 (Π[5,5])</h4>
                    <pre>
max <- Π[1,5]
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 5
    ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 5
        ΑΝ Π[i,j] > max ΚΑΙ i + j = 6 ΤΟΤΕ
            max <- Π[i,j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <h4>Σειριακή αναζήτηση του Χ σε πίνακα δύο διαστάσεων Π[20,30]</h4>
                    <pre>
βρέθηκε <- ΨΕΥΔΗΣ
i <- 1
ΟΣΟ i <= 20 και βρέθηκε = ΨΕΥΔΗΣ ΕΠΑΝΑΛΑΒΕ
    j <- 1
    ΟΣΟ j <= 30 ΚΑΙ βρέθηκε = ΨΕΥΔΗΣ ΕΠΑΝΑΛΑΒΕ
        ΑΝ Π[i,j] = Χ ΤΟΤΕ
            βρέθηκε <- ΑΛΗΘΗΣ
        ΑΛΛΙΩΣ
            j <- j + 1
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    i <- i + 1
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ταξινόμηση Ευθείας Ανταλλαγής όλων των γραμμών πίνακα (Π[20,30])</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 20
    ΓΙΑ k ΑΠΟ 2 ΜΕΧΡΙ 30
        ΓΙΑ j ΑΠΟ 30 ΜΕΧΡΙ k ME BHMA -1
            AN Π[i,j-1] > Π[i,j] ΤΟΤΕ
                temp <- Π[i,j-1]
                Π[i,j-1] <- Π[i,j]
                Π[i,j] <- temp
            ΤΕΛΟΣ_ΑΝ
        ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ    
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ταξινόμηση Ευθείας Ανταλλαγής όλων των στηλών πίνακα (Π[20,30])</h4>
                    <pre>
ΓΙΑ j ΑΠΟ 1 ΜΕΧΡΙ 30
    ΓΙΑ k ΑΠΟ 2 ΜΕΧΡΙ 20
        ΓΙΑ i ΑΠΟ 20 ΜΕΧΡΙ k ME BHMA -1
            AN Π[i-1,j] > Π[i,j] ΤΟΤΕ
                temp <- Π[i-1,j]
                Π[i-1,j] <- Π[i,j]
                Π[i,j] <- temp
            ΤΕΛΟΣ_ΑΝ
        ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ    
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ταξινόμηση Ευθείας Ανταλλαγής 3η γραμμή (Π[20,30])</h4>
                    <pre>
ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 30
    ΓΙΑ j ΑΠΟ 30 ΜΕΧΡΙ i ME BHMA -1
        AN Π[3,j-1] > Π[3,j] ΤΟΤΕ
            temp <- Π[3,j-1]
            Π[3,j-1] <- Π[3,j]
            Π[3,j] <- temp
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ     
            </pre>
                </div>

            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ταξινόμηση Ευθείας Ανταλλαγής 3η στήλη (Π[20,30])</h4>
                    <pre>
ΓΙΑ k ΑΠΟ 2 ΜΕΧΡΙ 20
    ΓΙΑ i ΑΠΟ 20 ΜΕΧΡΙ k ME BHMA -1
        AN Π[i-1,3] > Π[i,3] ΤΟΤΕ
            temp <- Π[i-1,3]
            Π[i-1,3] <- Π[i,3]
            Π[i,3] <- temp
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ     
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4>Ανταλλαγή στοιχείων της δεύτερης γραμμής με αυτά της πέμπτης γραμμής</h4>
                    <pre>
ΠΡΟΓΡΑΜΜΑ αντιμετάθεση_2_με_5_γραμμή
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: π[5, 6], ι, ξ, τεμπ
ΑΡΧΗ
  ΓΙΑ ι ΑΠΟ 1 ΜΕΧΡΙ 5
     ΓΙΑ ξ ΑΠΟ 1 ΜΕΧΡΙ 6
         ΔΙΑΒΑΣΕ π[ι, ξ] 
     ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
  ΓΙΑ ξ ΑΠΟ 1 ΜΕΧΡΙ 6
     τεμπ <- π[2, ξ] 
     π[2, ξ] <- π[5, ξ] 
     π[5, ξ] <- τεμπ
  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ      
            </pre>
                    <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
                </div>

            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm ">
                    <h4 id="diagrammata">Διαγράμματα Ροής</h4>
                    <p>Όταν σε ένα διάγραμμα ροής συναντήσουμε <b>ρόμβο</b> (συνθήκη) τότε
                        αυτό που ακολουθάει είναι <b>Δομή Επιλογής</b> Η <strong> Δομή Επανάληψης</strong> και:
                    </p>
                    <ol>
                        <li>Αν το <b>ΝΑΙ και το ΟΧΙ βρίσκονται</b>, τότε είναι <b>δομή επιλογής</b>
                            και στο σημείο που συναντόνται γράφουμε <b>ΤΕΛΟΣ_ΑΝ</b></li>
                        <li>Αν το <b>ΝΑΙ και το ΟΧΙ δεν βρίσκονται</b> ποτέ, τότε είναι
                            <b>δομή επανάληψης</b> και:
                        </li>
                        <ul>
                            <li>Αν η επανάληψη <b>τελειώνει με ΟΧΙ</b>, τότε είναι <b>ΟΣΟ</b></li>
                            <li>Αν η επανάληψη <b>τελειώνει με ΝΑΙ</b> τότε είναι <b>ΜΕΧΡΙΣ_ΟΤΟΥ</b> και
                                στο σημείο που επιστρέφει, γράφουμε <b>ΑΡΧΗ_ΕΠΑΝΑΛΗΨΗΣ</b></li>
                        </ul>
                    </ol>
                    <p><b>Προσοχή:</b> Η δομή επανάληψης <b>ΓΙΑ..ΑΠΟ..ΜΕΧΡΙ..</b> δεν έχει δικό της διάγραμμα ροής
                        πρέπει να μετατρέψουμε σε <b>ΟΣΟ..ΕΠΑΝΑΛΑΒΕ</b> και να κάνουμε το αντίστοιχο διάγραμμα ροής.</p>
                    <!--<a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>-->
                </div>

            </div>
        </div>
        <?php
        $theory->chapter01();
        $theory->chapter02();
        $theory->chapter03();
        $theory->enotita01();
        $theory->chapter06();
        $theory->chapter07();
        $theory->chapter08();
        $theory->chapter09();
        $theory->chapter10();
        $theory->chapter13();




        // --- ΔΥΝΑΜΙΚΟ ΥΛΙΚΟ ΟΜΑΔΟΠΟΙΗΜΕΝΟ ΑΝΑ ΚΕΦΑΛΑΙΟ ---
        include_once 'DbHandler.php';
        $dbDynamic = new DbHandler();
        $questions = $dbDynamic->getAllQuestionsOrdered();

        if ($questions && $questions->num_rows > 0) {
            $current_chapter = null;
            $chapter_data = [];

            // Ομαδοποίηση των ερωτήσεων ανά κεφάλαιο σε ένα array
            while ($row = $questions->fetch_assoc()) {
                $chapter_data[$row['chapter_num']][] = $row;
            }

            // Προβολή κάθε κεφαλαίου ξεχωριστά
            foreach ($chapter_data as $chapter_name => $items) {
                // Καλούμε τη μέθοδο για κάθε κεφάλαιο
                $theory->displayDynamicChapter($items, "Κεφάλαιο " . $chapter_name);
            }
        }


        $theory->enotita01();
        $theory->domi();
        ?>

        <div class="container-fluid">
            <h4 id="moreAlgorithms">Άλγόριθμοι Αναζήτησης - Ταξινόμησης - Συγχώνευσης</h4>
            <p><strong>Προσοχή:</strong> </p>
            <div id="accordionAlg">
                <div class="card">
                    <div class="card-header">
                        <a class="card-link" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            Δυαδική Αναζήτηση
                        </a>
                    </div>
                    <div id="collapseOne" class="collapse show">
                        <div class="card-body">
                            <pre>
ΓΡΑΨΕ 'Δωσε τιμή για αναζήτηση: '
ΔΙΑΒΑΣΕ S
Left <- 1
Right <- 20
k <- 0
f <- ΨΕΥΔΗΣ
ΟΣΟ (Left <= Right) ΚΑΙ (f = ΨΕΥΔΗΣ) ΕΠΑΝΑΛΑΒΕ
    M <- (Left + Right) DIV 2
    ΑΝ A[M] = S ΤΟΤΕ
        k <- M
        f <- ΑΛΗΘΗΣ
    ΑΛΛΙΩΣ
        ΑΝ A[M] < S ΤΟΤΕ
            Left <- M + 1
        ΑΛΛΙΩΣ
            Right <- M - 1
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΑΝ f = ΑΛΗΘΗΣ ΤΟΤΕ
    ΓΡΑΨΕ "Το στοιχείο,", S, "υπάρχει στη θέση:", M
ΑΛΛΙΩΣ
    ΓΡΑΨΕ "Το στοιχείο,", S, " δεν υπάρχει στον πίνακα"
ΤΕΛΟΣ_ΑΝ                                    
                    </pre>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            Έξυπνη ταξινόμηση ευθείας ανταλλαγής
                        </a>
                    </div>
                    <div id="collapseTwo" class="collapse">
                        <div class="card-body">
                            <P>Η ταξινόμηση ευθείας ανταλλαγής όπως την ξέρουμε δεν είναι και τόσο
                                έξυπνη αφού ακόμα και αν ο πίνακας είναι ήδη ταξινομημένος θα δουλέψει όσες
                                φορές ορίζουν τα δύο ΓΙΑ. Τον κάνουμε λίγο πιο έξυπνο μετατρέποντας την εξωτερική
                                ΓΙΑ σε ΟΣΟ και προσθέτοντας μια λογική μεταβλητή που τον σταματάει αν σε κάποιο πέρασμα του πίνακα
                                δεν γίνει αντιμετάθεση (ο πίνακας θα έχει ταξινομηθεί)</P>
                            <pre>
i <- 2
αντιμετάθεση <- ΑΛΗΘΗΣ
ΟΣΟ i <= 30 KAI αντιμετάθεση = ΑΛΗΘΗΣ ΕΠΑΝΑΛΑΒΕ
    αντιμετάθεση <- ΨΕΥΔΗΣ
    ΓΙΑ j ΑΠΟ 30 ΜΈΧΡΙ i ΜΕ ΒΗΜΑ -1
        ΑΝ Π[j-1] < Π[j] ΤΟΤΕ
            temp <- Π[j-1]
            Π[j-1]<- Π[j]
            Π[j] <- temp
            αντιμεταθεση <- ΑΛΗΘΗΣ
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    i <- i + 1
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                    </pre>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            Ταξινόμηση με επιλογή
                        </a>
                    </div>
                    <div id="collapseThree" class="collapse">
                        <div class="card-body">
                            <p>Πως κάνει την ταξινόμηση?</p>
                            <pre>
ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 19
    θ <- i
    x <- A[i]
    ΓΙΑ j ΑΠΟ i + 1 ΜΕΧΡΙ 20
        ΑΝ x > A[j] ΤΟΤΕ
            θ <- j
            x <- A[j]
        ΤΕΛΟΣ_ΑΝ
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
    A[θ] <- A[i]
    A[i] <- x
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ                                    
                    </pre>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            Συγχώνευση
                        </a>
                    </div>
                    <div id="collapseFour" class="collapse">
                        <div class="card-body">
                            <pre>
! Συγχώνευση πινάκων
! I είναι ο δείκτης για τον πίνακα Α
! J είναι ο δείκτης για τον πίνακα Β
! Κ είναι ο δείκτης για τον πίνακα Γ
I <- 1
J <- 1
Κ <- 1
ΟΣΟ I <= Ν ΚΑΙ J <= Μ ΕΠΑΝΑΛΑΒΕ
! Όσο και τα δύο έχουν στοιχεία
    ΑΝ Α[Ι] < B[J] ΤΟΤΕ
        Γ[Κ] <- Α[I]
        Κ <- Κ+1
        I <- Ι+1
    ΑΛΛΙΩΣ
        Γ[Κ] <- B[J]
        Κ <- Κ+1
        J <- J +1
    ΤΕΛΟΣ_ΑΝ
ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
! Μεταφορά των υπολοίπων στοιχείων του Α ή του Β
ΑΝ I > Ν ΤΟΤΕ
    ΓΙΑ Λ ΑΠΟ Κ ΜΕΧΡΙ Ν+Μ
        Γ[Λ] <- B[J]
        J <- J +1
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
ΑΛΛΙΩΣ
    ΓΙΑ Λ ΑΠΟ Κ ΜΕΧΡΙ Ν+Μ
        Γ[Λ] <- Α[I]
        I <- Ι+1
    ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
ΤΕΛΟΣ_ΑΝ                                   
                    </pre>
                        </div>
                    </div>
                </div>
                <!--                    <div class="card">
                                <div class="card-header">
                                    <a class="collapsed card-link" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        Ταξινόμηση με επιλογή
                                    </a>
                                </div>
                                <div id="collapseThree" class="collapse" data-bs-parent="#accordionAlg">
                                    <div class="card-body">
                                        <pre>
                                            
                                        </pre>
                                    </div>
                                </div>
                            </div>-->
            </div>
        </div>
<?php
        break; // Εδώ τελειώνει το default case
} // Εδώ κλείνει το switch

$page->displayEndMatter();
?>