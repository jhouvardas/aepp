<?php
include_once 'AdminFormMaker.php';

class ReportAdminFormMaker extends AdminFormMaker
{
    public function showFullGradesTable($students, $gradesReport)
    {
?>
        <div class="container-fluid mt-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4>Συγκεντρωτικό Βαθμολόγιο</h4>
                </div>
                <div class="card-body overflow-auto">
                    <table class="table table-sm table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Ονοματεπώνυμο</th>
                                <th>Μ.Ο.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student):
                                $stId = $student['studentId'];
                                $sum = 0;
                                $count = 0;
                                if (isset($gradesReport[$stId])) {
                                    foreach ($gradesReport[$stId] as $g) {
                                        $sum += $g['val'];
                                        $count++;
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="text-start fw-bold"><?php echo $student['name'] . " " . $student['lastName']; ?></td>
                                    <td class="text-danger fw-bold"><?php echo $count > 0 ? number_format($sum / $count, 2) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php
    }

    public function showStudentSelectionList($students)
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fa fa-users"></i> Επιλογή Μαθητή (360°)</h3>
                </div>
                <div class="card-body">
                    <div class="list-group shadow-sm">
                        <?php if (empty($students)): ?>
                            <div class="alert alert-warning text-center">Δεν βρέθηκαν μαθητές για το τρέχον έτος.</div>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <a href="index.php?action=viewStudentProfile&studentId=<?php echo $s['studentId']; ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-user-circle text-success me-2"></i> <strong><?php echo $s['name'] . " " . $s['lastName']; ?></strong></span>
                                    <span class="badge badge-light border text-dark">Άνοιγμα Καρτέλας <i class="fa fa-chevron-right"></i></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function showFullStudentProfile($student, $grades, $tasks, $financials, $average, $trend = [])
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fa fa-user-circle"></i> Καρτέλα: <?php echo $student['name'] . " " . $student['lastName']; ?></h3>
                    <div class="d-print-none">
                        <button onclick="window.print();" class="btn btn-light btn-sm"><i class="fa fa-print"></i> Εκτύπωση</button>
                        <a href="index.php?action=viewStudentProfile" class="btn btn-dark btn-sm">Επιστροφή</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Στατιστικά και Επικοινωνία -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center h-100">
                                <h6 class="text-muted mb-1 small text-uppercase">Μέσος Όρος</h6>
                                <h2 class="text-danger font-weight-bold mb-0"><?php echo number_format($average, 2); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center h-100">
                                <h6 class="text-muted mb-1 small text-uppercase">Εκκρεμή Μαθήματα</h6>
                                <h2 class="<?php echo $financials['balance'] > 0 ? 'text-warning' : 'text-success'; ?> fw-bold mb-0"><?php echo count($financials['items']); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center h-100">
                                <h6 class="text-muted mb-1 small text-uppercase">Επικοινωνία</h6>
                                <div class="small">
                                    <i class="fa fa-envelope"></i> <?php echo $student['email']; ?><br>
                                    <i class="fa fa-phone"></i> <?php echo $student['phone']; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Trend Graph -->
                    <?php if (!empty($trend)): ?>
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-3"><i class="fa fa-line-chart"></i> Τάση Απόδοσης (Τελευταία 10)</h6>
                                <div class="d-flex align-items-end justify-content-around bg-white border rounded" style="height: 150px; padding: 10px; position: relative;">
                                    <?php foreach ($trend as $t):
                                        $val = floatval($t['grade_value']);
                                        $height = max(($val / 20) * 100, 5);
                                        $barColor = ($val >= 18) ? 'bg-success' : (($val >= 13) ? 'bg-primary' : (($val >= 10) ? 'bg-warning' : 'bg-danger'));
                                    ?>
                                        <div class="d-flex flex-column align-items-center" style="width: 8%; height: 100%; justify-content: flex-end;">
                                            <div class="<?php echo $barColor; ?> rounded-top w-100"
                                                style="height: <?php echo $height; ?>%;"
                                                title="Μ<?php echo $t['mezeNumber']; ?>: <?php echo $val; ?>/20"></div>
                                            <div class="text-muted mt-1 fw-bold" style="font-size: 0.6rem;">Μ<?php echo $t['mezeNumber']; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs d-print-none" id="profileTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mezeTab" type="button">Μεζεδάκια</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tasksTab" type="button">Εργασίες</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#financeTab" type="button">Οικονομικά</button></li>
                    </ul>

                    <div class="tab-content mt-3">
                        <!-- Tab Μεζεδάκια -->
                        <div class="tab-pane fade show active" id="mezeTab">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Ημερομηνία</th>
                                        <th>Βαθμός</th>
                                        <th>Σχόλια</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $g): ?>
                                        <tr>
                                            <td class="fw-bold">#<?php echo $g['mezeNumber']; ?></td>
                                            <td class="small"><?php echo date('d/m/Y', strtotime($g['mezeDate'])); ?></td>
                                            <td class="fw-bold text-<?php echo $g['grade_value'] >= 10 ? 'success' : 'danger'; ?>"><?php echo $g['grade_value']; ?></td>
                                            <td class="small text-muted italic"><?php echo $g['teacher_comments']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tab Εργασίες -->
                        <div class="tab-pane fade" id="tasksTab">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Εργασία</th>
                                        <th>Βαθμός</th>
                                        <th>Σχόλια</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $t): ?>
                                        <tr>
                                            <td class="small"><?php echo date('d/m/Y', strtotime($t['date_added'])); ?></td>
                                            <td><?php echo htmlspecialchars($t['task_text']); ?></td>
                                            <td class="fw-bold"><?php echo $t['grade_value'] ?? '-'; ?></td>
                                            <td class="small"><?php echo $t['teacher_comments']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tab Οικονομικά (FIFO) -->
                        <div class="tab-pane fade" id="financeTab">
                            <div class="alert alert-info py-2 small"><i class="fa fa-info-circle"></i> Εμφανίζονται τα μαθήματα που εκκρεμούν προς εξόφληση.</div>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Περιγραφή</th>
                                        <th class="text-end">Υπόλοιπο</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($financials['items'])): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-success">Όλα εξοφλημένα!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($financials['items'] as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($item['date'])); ?></td>
                                                <td>Μάθημα / Απουσία</td>
                                                <td class="text-end fw-bold text-danger"><?php echo number_format($item['cost'], 2); ?> €</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="bg-dark text-white">
                                    <tr>
                                        <th colspan="2" class="text-end">Συνολικό Υπόλοιπο:</th>
                                        <th class="text-end"><?php echo number_format($financials['balance'], 2); ?> €</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function showStudentReport($studentName, $grades)
    {
    ?>
        <div class="container mt-5 p-4 bg-white shadow border">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-dark">Ατομική Καρτέλα Μαθητή</h2>
                    <h4 class="text-secondary"><?php echo $studentName; ?></h4>
                </div>
                <button onclick="window.print();" class="btn btn-outline-dark d-print-none">
                    <i class="fa fa-print"></i> Εκτύπωση Καρτέλας
                </button>
            </div>
            <hr>
            <table class="table table-bordered table-striped mt-3 text-center">
                <thead class="thead-light">
                    <tr>
                        <th># Μεζεδάκι</th>
                        <th>Ημερομηνία</th>
                        <th>Βαθμός (0-20)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum = 0;
                    $count = 0;
                    foreach ($grades as $row):
                        $sum += $row['grade_value'];
                        $count++;
                    ?>
                        <tr>
                            <td>Μεζεδάκι #<?php echo $row['mezeNumber']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['mezeDate'])); ?></td>
                            <td class="font-weight-bold"><?php echo number_format($row['grade_value'], 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="2" class="text-right">Γενικός Μέσος Όρος:</th>
                        <th class="text-danger h5">
                            <?php echo ($count > 0) ? number_format($sum / $count, 2) : "-"; ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php
    }

    public function manageGroupsForm($groups, $students, $db, $assignments = [])
    {
    ?>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">Δημιουργία Ομάδας</div>
                        <form action="index.php?action=save_group" method="POST" class="card-body d-grid">
                            <input type="text" name="group_name" class="form-control mb-2" placeholder="Όνομα" required>
                            <button type="submit" class="btn btn-primary w-100">Δημιουργία</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">Ανάθεση σε Ομάδα</div>
                        <form action="index.php?action=add_student_to_group" method="POST" class="card-body d-grid">
                            <select name="student_id" class="form-control mb-2" required>
                                <option value="">Επίλεξε Μαθητή</option>
                                <?php foreach ($students as $s):
                                    if (array_key_exists($s['studentId'], $assignments)) continue;
                                ?>
                                    <option value="<?php echo $s['studentId']; ?>"><?php echo "{$s['name']} {$s['lastName']}"; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="group_id" class="form-control mb-2" required>
                                <option value="">Επίλεξε Ομάδα</option>
                                <?php foreach ($groups as $g) echo "<option value='{$g['id']}'>{$g['group_name']}</option>"; ?>
                            </select>
                            <button type="submit" class="btn btn-info w-100">Ανάθεση</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h5>Υπάρχουσες Ομάδες & Μέλη</h5>
                <table class="table table-sm table-bordered bg-white shadow-sm">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 30%;">Ομάδα</th>
                            <th>Μέλη</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g):
                            $members = [];
                            foreach ($students as $s) {
                                if (isset($assignments[$s['studentId']]) && $assignments[$s['studentId']] == $g['id']) {
                                    $members[] = $s;
                                }
                            }
                        ?>
                            <tr>
                                <td class="align-middle"><strong><?php echo $g['group_name']; ?></strong></td>
                                <td>
                                    <?php if (empty($members)): ?>
                                        <span class="text-muted small italic">Κενή ομάδα</span>
                                    <?php else: ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($members as $m): ?>
                                                <li class="mb-1">
                                                    <?php echo "{$m['name']} {$m['lastName']}"; ?>
                                                    <a href="index.php?action=remove_student_from_group&student_id=<?php echo $m['studentId']; ?>"
                                                        class="text-danger ms-1"
                                                        onclick="return confirm('Αφαίρεση από την ομάδα;')">
                                                        <i class="fa fa-times-circle"></i>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }

    public function showTaskGradesForm($task, $students, $existingGrades)
    {
    ?>
        <div class="container mt-4">
            <form action="index.php?action=save_task_grades" method="post" class="bg-white p-4 shadow rounded">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fa fa-pencil"></i> Βαθμολόγηση: <?php echo $task['group_name']; ?></h3>
                    <button type="submit" class="btn btn-success px-4 shadow-sm"><i class="fa fa-save"></i> Αποθήκευση Όλων</button>
                </div>

                <div class="alert alert-info mb-4"><strong>Εργασία:</strong> <?php echo nl2br(htmlspecialchars($task['task_text'])); ?></div>

                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 25%;">Μαθητής</th>
                            <th style="width: 15%;">Βαθμός</th>
                            <th>Σχόλια (HTML)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s):
                            $stId = $s['studentId'];
                            $grade = $existingGrades[$stId]['grade_value'] ?? "";
                            $comments = $existingGrades[$stId]['teacher_comments'] ?? "";
                        ?>
                            <tr>
                                <td><strong><?php echo $s['name'] . " " . $s['lastName']; ?></strong></td>
                                <td><input type="number" name="grades[<?php echo $stId; ?>]" step="0.5" min="0" max="20" class="form-control text-center" value="<?php echo $grade; ?>" placeholder="-"></td>
                                <td>
                                    <textarea name="comments[<?php echo $stId; ?>]" class="form-control task-comment-editor"><?php echo htmlspecialchars($comments); ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            document.querySelectorAll('.task-comment-editor').forEach((element) => {
                ClassicEditor.create(element, {
                    toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', 'undo', 'redo']
                }).catch(error => console.error(error));
            });
        </script>
<?php
    }
}
