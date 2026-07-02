<?php
switch ($action) {

    case 'importSchools':
        $db->ensureSchoolsTables();
        $importYear  = (int)(date('Y') - 1);
        $importResult = null;
        $step        = 'upload'; // 'upload' | 'map' | 'done'
        $headers     = [];
        $preview     = [];
        $tmpFile     = '';

        // ---- ΒΗΜΑ 3: Κάνε import με το mapping ----
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_import'])) {
            $importYear = (int)($_POST['import_year'] ?? $importYear);
            $tmpFile    = $_POST['tmp_file'] ?? '';
            $mapping    = $_POST['col_map'] ?? [];

            // Ασφάλεια: το tmp_file πρέπει να είναι μέσα στο uploads/schools_tmp/
            $allowedDir = realpath(__DIR__ . '/../../../uploads/schools_tmp');
            $realTmp    = $tmpFile ? realpath($tmpFile) : false;

            if (!$realTmp || !$allowedDir || strpos($realTmp, $allowedDir) !== 0 || !file_exists($realTmp)) {
                $importResult = ['error' => 'Μη έγκυρο αρχείο. Ξαναδοκίμασε από την αρχή.'];
                $step = 'upload';
            } else {
                $rows = $db->parseSchoolsCsvWithMapping($realTmp, $mapping);
                @unlink($realTmp);
                if (empty($rows)) {
                    $importResult = ['error' => 'Δεν βρέθηκαν δεδομένα με αυτό το mapping.'];
                    $step = 'upload';
                } else {
                    $count = $db->importSchools($rows, $importYear);
                    $importResult = ['success' => true, 'count' => $count, 'year' => $importYear];
                    $step = 'done';
                }
            }
        }

        // ---- ΒΗΜΑ 2: Ανάλυση headers → εμφάνιση mapping form ----
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $importYear = (int)($_POST['import_year'] ?? $importYear);
            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $importResult = ['error' => 'Σφάλμα upload: ' . $file['error']];
            } elseif (!in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['csv', 'txt', 'xls', 'xlsx'])) {
                $importResult = ['error' => 'Ανέβασε αρχείο CSV ή TXT. Αν έχεις Excel (.xlsx), άνοιξέ το και αποθήκευσέ το ως CSV πρώτα.'];
            } else {
                // Αποθήκευση σε uploads/schools_tmp/
                $tmpDir = __DIR__ . '/../../../uploads/schools_tmp';
                if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
                $tmpFile = $tmpDir . '/schools_' . session_id() . '_' . time() . '.csv';
                move_uploaded_file($file['tmp_name'], $tmpFile);

                // Ανάλυση headers + 5 πρώτες γραμμές
                $parsed = $db->parseCsvHeadersAndPreview($tmpFile);
                $headers = $parsed['headers'];
                $preview = $parsed['preview'];
                $step    = 'map';
            }
        }

        $years      = $db->getSchoolYears();
        $allSchools = (!empty($years) && $step !== 'map') ? $db->getSchoolsAdmin($years[0]) : [];
        ?>
        <div class="container mt-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="mb-0"><i class="fa fa-university text-info"></i> Διαχείριση Σχολών & Μορίων</h4>
            </div>

            <?php if ($importResult): ?>
                <?php if (isset($importResult['error'])): ?>
                    <div class="alert alert-danger"><i class="fa fa-times"></i> <?php echo $importResult['error']; ?></div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check"></i> Εισήχθησαν <strong><?php echo $importResult['count']; ?></strong> σχολές για το έτος <strong><?php echo $importResult['year']; ?></strong>.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($step === 'map'): ?>
            <!-- ===== ΒΗΜΑ 2: Mapping Στηλών ===== -->
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="fa fa-columns"></i> Βήμα 2: Επίλεξε ποια στήλη αντιστοιχεί σε κάθε πεδίο
                </div>
                <div class="card-body">

                    <?php if (!empty($preview)): ?>
                    <p class="small text-muted mb-2">Προεπισκόπηση πρώτων γραμμών:</p>
                    <div style="overflow-x:auto; max-height:180px;" class="mb-3">
                        <table class="table table-sm table-bordered" style="font-size:0.75rem; white-space:nowrap;">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <?php foreach ($headers as $i => $h): ?>
                                        <th><?php echo chr(65 + $i); ?><br><span class="fw-normal text-muted"><?php echo htmlspecialchars(mb_substr($h, 0, 18, 'UTF-8')); ?></span></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preview as $row): ?>
                                <tr>
                                    <td class="text-muted"></td>
                                    <?php foreach ($headers as $i => $h): ?>
                                        <td><?php echo htmlspecialchars(mb_substr($row[$i] ?? '', 0, 20, 'UTF-8')); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="tmp_file" value="<?php echo htmlspecialchars($tmpFile); ?>">
                        <input type="hidden" name="import_year" value="<?php echo $importYear; ?>">
                        <input type="hidden" name="do_import" value="1">

                        <?php
                        $fieldLabels = [
                            'sci_field'   => ['Επιστημονικό Πεδίο (φίλτρο: μόνο "4")', true],
                            'department'  => ['Τμήμα / Σχολή', true],
                            'university'  => ['Ίδρυμα', false],
                            'city'        => ['Πόλη', false],
                            'direction'   => ['Κατεύθυνση', false],
                            'base_points' => ['Βαθμός Τελευταίου', false],
                            'code'        => ['Κωδικός', false],
                        ];
                        // Auto-detect defaults using normalized headers
                        $autoMap = $db->autoDetectColumnMapping($headers);
                        ?>
                        <div class="row g-3">
                            <?php foreach ($fieldLabels as $field => [$label, $required]): ?>
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label fw-bold small">
                                    <?php echo $label; ?>
                                    <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
                                </label>
                                <select name="col_map[<?php echo $field; ?>]" class="form-select form-select-sm" <?php if ($required) echo 'required'; ?>>
                                    <option value="">— Αγνόησε —</option>
                                    <?php foreach ($headers as $i => $h): ?>
                                        <option value="<?php echo $i; ?>"
                                            <?php echo (isset($autoMap[$field]) && $autoMap[$field] == $i) ? 'selected' : ''; ?>>
                                            <?php echo chr(65 + $i); ?>: <?php echo htmlspecialchars(mb_substr($h, 0, 40, 'UTF-8')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-warning fw-bold">
                                <i class="fa fa-upload"></i> Εισαγωγή
                            </button>
                            <a href="index.php?action=importSchools" class="btn btn-outline-secondary">
                                <i class="fa fa-times"></i> Ακύρωση
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php else: ?>
            <!-- ===== ΒΗΜΑ 1: Upload ===== -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fa fa-upload"></i> Εισαγωγή από CSV
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Αποθήκευσε το Excel ως <strong>CSV</strong> (Αρχείο → Αποθήκευση ως → CSV UTF-8)
                        και ανέβασέ το εδώ.<br>
                        Στο επόμενο βήμα θα επιλέξεις ποια στήλη αντιστοιχεί σε κάθε πεδίο.
                    </p>
                    <form method="post" enctype="multipart/form-data">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Αρχείο CSV</label>
                                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Έτος εξετάσεων</label>
                                <input type="number" name="import_year" class="form-control"
                                       value="<?php echo $importYear; ?>" min="2020" max="2030" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-info w-100 text-white">
                                    <i class="fa fa-arrow-right"></i> Επόμενο
                                </button>
                            </div>
                        </div>
                        <p class="text-warning small mt-2 mb-0">
                            <i class="fa fa-exclamation-triangle"></i>
                            Η εισαγωγή <strong>διαγράφει</strong> τις παλιές σχολές για το επιλεγμένο έτος.
                        </p>
                    </form>
                </div>
            </div>

            <?php if (!empty($years)): ?>
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-list"></i> Σχολές <?php echo $years[0]; ?> (<?php echo count($allSchools); ?> εγγραφές)</span>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <?php foreach ($years as $y): ?>
                                <a href="index.php?action=listSchools&year=<?php echo $y; ?>" class="btn btn-sm btn-outline-secondary"><?php echo $y; ?></a>
                            <?php endforeach; ?>
                            <a href="index.php?action=deleteSchoolYear&year=<?php echo $years[0]; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Διαγραφή όλων των σχολών για <?php echo $years[0]; ?>;')">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div style="max-height:500px; overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Κωδικός</th>
                                        <th>Ίδρυμα</th>
                                        <th>Τμήμα/Σχολή</th>
                                        <th>Πόλη</th>
                                        <th>Κατεύθυνση</th>
                                        <th>Βαθμός Τελευταίου</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allSchools as $i => $s): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($s['code']); ?></td>
                                            <td><?php echo htmlspecialchars($s['university']); ?></td>
                                            <td><?php echo htmlspecialchars($s['department']); ?></td>
                                            <td><?php echo htmlspecialchars($s['city']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['direction']); ?></span></td>
                                            <td class="fw-bold text-warning"><?php echo htmlspecialchars($s['base_points']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info"><i class="fa fa-info-circle"></i> Δεν έχουν εισαχθεί σχολές ακόμα.</div>
            <?php endif; ?>
            <?php endif; // end step upload ?>
        </div>
        <?php
        break;

    case 'listSchools':
        $db->ensureSchoolsTables();
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $years = $db->getSchoolYears();
        if (!$year && !empty($years)) $year = $years[0];
        $allSchools = $year ? $db->getSchoolsAdmin($year) : [];
        ?>
        <div class="container mt-4">
            <div class="d-flex align-items-center mb-3 gap-3">
                <h4 class="mb-0"><i class="fa fa-university text-info"></i> Λίστα Σχολών <?php echo $year; ?></h4>
                <a href="index.php?action=importSchools" class="btn btn-sm btn-outline-info ms-auto">
                    <i class="fa fa-upload"></i> Import CSV
                </a>
            </div>
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><?php echo count($allSchools); ?> σχολές</span>
                    <div class="d-flex gap-2">
                        <?php foreach ($years as $y): ?>
                            <a href="index.php?action=listSchools&year=<?php echo $y; ?>"
                               class="btn btn-sm <?php echo ($y == $year) ? 'btn-info text-white' : 'btn-outline-secondary'; ?>">
                                <?php echo $y; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="max-height:600px; overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Κωδικός</th>
                                    <th>Ίδρυμα</th>
                                    <th>Τμήμα/Σχολή</th>
                                    <th>Πόλη</th>
                                    <th>Κατεύθυνση</th>
                                    <th>Βαθμός Τελευταίου</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allSchools as $i => $s): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($s['code']); ?></td>
                                        <td><?php echo htmlspecialchars($s['university']); ?></td>
                                        <td><?php echo htmlspecialchars($s['department']); ?></td>
                                        <td><?php echo htmlspecialchars($s['city']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['direction']); ?></span></td>
                                        <td class="fw-bold text-warning"><?php echo htmlspecialchars($s['base_points']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'allSchoolPreferences':
        $db->ensureSchoolsTables();
        $students = $db->getTutorStudents($userYear);
        $schoolYears = $db->getSchoolYears();
        ?>
        <div class="container mt-4">
            <div class="d-flex align-items-center mb-3 gap-3">
                <h4 class="mb-0"><i class="fa fa-university text-info"></i> Σχολές Προτίμησης Μαθητών</h4>
                <?php if (!empty($schoolYears)): ?>
                    <span class="badge bg-secondary">Βάσεις <?php echo $schoolYears[0]; ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($schoolYears)): ?>
                <div class="alert alert-info"><i class="fa fa-info-circle"></i> Δεν έχουν εισαχθεί σχολές ακόμα. <a href="index.php?action=importSchools">Import CSV</a></div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($students as $st):
                    if ($st['studentId'] == 999999) continue;
                    $prefs = $db->getStudentPreferences((int)$st['studentId']);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 <?php echo empty($prefs) ? 'border-secondary opacity-75' : 'border-info'; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center py-2
                            <?php echo empty($prefs) ? 'bg-secondary text-white' : 'bg-info text-white'; ?>">
                            <span class="fw-bold small">
                                <i class="fa fa-user"></i>
                                <?php echo htmlspecialchars($st['name'] . ' ' . $st['lastName']); ?>
                            </span>
                            <span class="badge bg-white <?php echo empty($prefs) ? 'text-secondary' : 'text-info'; ?>">
                                <?php echo count($prefs); ?>/10
                            </span>
                        </div>
                        <?php if (empty($prefs)): ?>
                            <div class="card-body py-2 text-muted small"><i class="fa fa-minus-circle"></i> Καμία επιλογή</div>
                        <?php else: ?>
                            <ol class="list-group list-group-flush" style="font-size:0.8rem;">
                                <?php foreach ($prefs as $p): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start py-1 px-2">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($p['department']); ?></div>
                                            <div class="text-muted" style="font-size:0.72rem;"><?php echo htmlspecialchars($p['city']); ?></div>
                                        </div>
                                        <span class="badge bg-warning text-dark ms-1"><?php echo htmlspecialchars($p['base_points']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                        <div class="card-footer py-1 text-end">
                            <a href="index.php?action=viewStudentProfile&studentId=<?php echo $st['studentId']; ?>"
                               class="btn btn-xs btn-outline-secondary" style="font-size:0.75rem; padding:2px 8px;">
                                <i class="fa fa-user"></i> Καρτέλα
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        break;

    case 'deleteSchoolYear':
        if (isset($_GET['year'])) {
            $db->deleteSchoolsByYear((int)$_GET['year']);
        }
        echo "<script>window.location.href='index.php?action=importSchools&status=schools_deleted';</script>";
        exit();
}
