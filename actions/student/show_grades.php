<?php
        $currentYear = $db->getCurrentTutorYear();
        $studentId = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;
        if ($studentId) {

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

                $classStats   = $db->getClassGradeStats($currentYear);
                $allMezedakia = $db->getAllMezedakia();
                $pendingRequestIds = is_object($db) ? $db->getStudentPendingRequests($studentId) : [];

                $fullName = "";
                if (is_array($students)) foreach ($students as $s) {
                    if ($s['studentId'] == $studentId) $fullName = $s['name'] . " " . $s['lastName'];
                }
        ?>
                <div class="container mt-4 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h3 class="text-primary mb-0"><i class="fa fa-user-circle"></i> <?php echo $fullName; ?></h3>
                        <a href="index.php?action=studentDashboard" class="btn btn-outline-primary btn-sm me-2"><i class="fa fa-th-large"></i> Dashboard</a>
                        <a href="index.php?action=studentLogout" class="btn btn-outline-secondary btn-sm"><i class="fa fa-sign-out"></i> Αποσύνδεση</a>
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
                                                    <td class="text-wrap" style="max-width: 400px; white-space: pre-wrap;"><?php echo !empty($g['teacher_comments']) ? $g['teacher_comments'] : '<span class="text-muted small">Χωρίς σχόλια.</span>'; ?></td>
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

                            <!-- Σύγκριση με τάξη -->
                            <?php if ($classStats['max_avg'] !== null && $overallAverage > 0):
                                $scale    = $classStats['max_avg'];
                                $myPct    = min(100, round($overallAverage / $scale * 100));
                                $clsPct   = min(100, round($classStats['class_avg'] / $scale * 100));
                                $myColor  = $overallAverage >= $classStats['class_avg'] ? '#10b981' : '#f59e0b';
                            ?>
                            <div class="card border-0 shadow-sm mt-4 mb-2" style="border-radius:14px; overflow:hidden;">
                                <div style="height:4px; background: linear-gradient(90deg, #6366f1, #0ea5e9);"></div>
                                <div class="card-body px-4 py-3">
                                    <div class="text-muted small fw-bold mb-3" style="letter-spacing:.6px; text-transform:uppercase;">Πορεία σε σχέση με την τάξη</div>

                                    <!-- Ο Μ.Ο. σου -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold" style="color:<?php echo $myColor; ?>;">Ο Μ.Ο. σου</span>
                                            <span class="small fw-bold" style="color:<?php echo $myColor; ?>;"><?php echo number_format($overallAverage, 1); ?></span>
                                        </div>
                                        <div class="rounded-pill overflow-hidden" style="height:10px; background:#f1f5f9;">
                                            <div class="rounded-pill h-100" style="width:<?php echo $myPct; ?>%; background:<?php echo $myColor; ?>; transition:width .8s ease;"></div>
                                        </div>
                                    </div>

                                    <!-- Μ.Ο. τάξης -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small text-muted">Μ.Ο. τάξης</span>
                                            <span class="small text-muted"><?php echo number_format($classStats['class_avg'], 1); ?></span>
                                        </div>
                                        <div class="rounded-pill overflow-hidden" style="height:10px; background:#f1f5f9;">
                                            <div class="rounded-pill h-100" style="width:<?php echo $clsPct; ?>%; background:#94a3b8; transition:width .8s ease;"></div>
                                        </div>
                                    </div>

                                    <!-- Υψηλότερος -->
                                    <div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small text-muted">Υψηλότερος στην τάξη</span>
                                            <span class="small text-muted"><?php echo number_format($classStats['max_avg'], 1); ?></span>
                                        </div>
                                        <div class="rounded-pill overflow-hidden" style="height:10px; background:#f1f5f9;">
                                            <div class="rounded-pill h-100" style="width:100%; background:#e2e8f0; transition:width .8s ease;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

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
                                                    <td class="text-wrap" style="max-width: 400px; white-space: pre-wrap;"><?php echo !empty($task['teacher_comments']) ? $task['teacher_comments'] : '<span class="text-muted small">Χωρίς σχόλια.</span>'; ?></td>
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
                echo "<script>window.location.href='index.php?action=myGrades';</script>";
            }
