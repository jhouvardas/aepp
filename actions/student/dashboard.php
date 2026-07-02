<?php
$studentName = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : '';
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="text-center mb-4">
                <i class="fa fa-user-circle text-primary" style="font-size: 60px;"></i>
                <h3 class="mt-2 fw-bold"><?php echo htmlspecialchars($studentName); ?></h3>
                <p class="text-muted">Καλώς ήρθες στο προσωπικό σου χώρο!</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <a href="index.php?action=viewMezedakia" class="text-decoration-none">
                        <div class="card shadow border-warning h-100 text-center p-4" style="border-radius: 15px; border-width: 2px !important; transition: transform 0.15s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body">
                                <i class="fa fa-star text-warning" style="font-size: 50px;"></i>
                                <h4 class="mt-3 fw-bold text-dark">Τα Μεζεδάκια μου</h4>
                                <p class="text-muted small">Δες τις ασκήσεις, υπόβαλε λύσεις και παρακολούθησε τις παρατάσεις σου.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="index.php?action=showMyGrades" class="text-decoration-none">
                        <div class="card shadow border-info h-100 text-center p-4" style="border-radius: 15px; border-width: 2px !important; transition: transform 0.15s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body">
                                <i class="fa fa-bar-chart text-info" style="font-size: 50px;"></i>
                                <h4 class="mt-3 fw-bold text-dark">Οι Βαθμοί μου</h4>
                                <p class="text-muted small">Δες τους βαθμούς σου, τις εργασίες ομάδας και τα οικονομικά σου.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <?php
            $db->ensureSchoolsTables();
            $schoolYears = $db->getSchoolYears();
            if (!empty($schoolYears)):
                $studentId = (int)$_SESSION['student_id'];
                $prefs = $db->getStudentPreferences($studentId);
            ?>
            <div class="mt-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa fa-university text-info"></i> Σχολές Προτίμησης
                        <span class="badge bg-secondary ms-1" style="font-size:0.7rem;"><?php echo $schoolYears[0]; ?></span>
                    </h5>
                    <a href="index.php?action=studentPreferences" class="btn btn-sm btn-outline-info">
                        <i class="fa fa-pencil"></i> <?php echo empty($prefs) ? 'Επιλογή Σχολών' : 'Επεξεργασία'; ?>
                    </a>
                </div>

                <?php if (empty($prefs)): ?>
                    <div class="alert alert-light border text-center text-muted">
                        <i class="fa fa-info-circle"></i> Δεν έχεις επιλέξει σχολές ακόμα.
                        <a href="index.php?action=studentPreferences" class="alert-link ms-1">Ξεκίνα εδώ →</a>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($prefs as $p): ?>
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info text-white fw-bold" style="min-width:28px; font-size:0.85rem;"><?php echo (int)$p['preference_order']; ?></span>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="fw-bold"><?php echo htmlspecialchars($p['department']); ?></div>
                                            <div class="text-muted small">
                                                <?php echo htmlspecialchars($p['university']); ?>
                                                <?php if ($p['city']): ?>
                                                    <span class="mx-1">&middot;</span><?php echo htmlspecialchars($p['city']); ?>
                                                <?php endif; ?>
                                                <?php if ($p['direction']): ?>
                                                    <span class="mx-1">&middot;</span><span class="badge bg-light text-dark border" style="font-size:0.65rem;"><?php echo htmlspecialchars($p['direction']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($p['base_points']): ?>
                                            <div class="text-end">
                                                <div class="fw-bold text-warning" style="font-size:1rem;"><?php echo htmlspecialchars($p['base_points']); ?></div>
                                                <div class="text-muted" style="font-size:0.65rem;">βαθμός τελ.</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="index.php?action=studentLogout" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-sign-out"></i> Αποσύνδεση
                </a>
            </div>

        </div>
    </div>
</div>
