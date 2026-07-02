<?php
$result   = null;
$studentId = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $studentId) {
    $oldPass  = trim(isset($_POST['old_pass'])  ? $_POST['old_pass']  : '');
    $newPass  = trim(isset($_POST['new_pass'])  ? $_POST['new_pass']  : '');
    $newPass2 = trim(isset($_POST['new_pass2']) ? $_POST['new_pass2'] : '');

    if (!preg_match('/^\d{6}$/', $newPass)) {
        $result = 'not_6digits';
    } elseif ($newPass !== $newPass2) {
        $result = 'mismatch';
    } else {
        $result = $db->changeStudentPassword($studentId, $oldPass, $newPass);
    }
}
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-sm-12">
            <div class="card shadow" style="border-radius: 15px;">
                <div class="card-header bg-warning text-dark text-center py-3" style="border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fa fa-key"></i> Αλλαγή Κωδικού</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($result === 'ok'): ?>
                        <div class="text-center py-3">
                            <i class="fa fa-check-circle text-success" style="font-size: 70px;"></i>
                            <h5 class="mt-3 fw-bold">Ο κωδικός άλλαξε επιτυχώς!</h5>
                            <p class="text-muted">Χρησιμοποίησε τον νέο σου κωδικό την επόμενη φορά που θα συνδεθείς.</p>
                            <a href="index.php?action=studentDashboard" class="btn btn-success mt-2 px-4">
                                <i class="fa fa-th-large"></i> Επιστροφή στο Dashboard
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if ($result === 'wrong_old'): ?>
                            <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Ο τρέχων κωδικός δεν είναι σωστός.</div>
                        <?php elseif ($result === 'mismatch'): ?>
                            <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Οι δύο νέοι κωδικοί δεν ταιριάζουν.</div>
                        <?php elseif ($result === 'not_6digits'): ?>
                            <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Ο νέος κωδικός πρέπει να είναι ακριβώς 6 ψηφία.</div>
                        <?php elseif ($result === 'error'): ?>
                            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Κάτι πήγε στραβά. Δοκίμασε ξανά.</div>
                        <?php endif; ?>

                        <form method="POST" action="index.php?action=changePassword">
                            <div class="mb-3">
                                <label class="fw-bold">Τρέχων Κωδικός</label>
                                <input type="password" name="old_pass"
                                    class="form-control form-control-lg text-center"
                                    maxlength="15" inputmode="numeric"
                                    required placeholder="••••••">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Νέος Κωδικός <small class="text-muted">(ακριβώς 6 ψηφία)</small></label>
                                <input type="password" name="new_pass"
                                    class="form-control form-control-lg text-center"
                                    maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                    required placeholder="••••••">
                            </div>
                            <div class="mb-4">
                                <label class="fw-bold">Επιβεβαίωση Νέου Κωδικού</label>
                                <input type="password" name="new_pass2"
                                    class="form-control form-control-lg text-center"
                                    maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                    required placeholder="••••••">
                            </div>
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm">
                                <i class="fa fa-save"></i> Αποθήκευση Νέου Κωδικού
                            </button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php?action=studentDashboard" class="text-decoration-none text-secondary">
                                <i class="fa fa-arrow-left"></i> Επιστροφή στο Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
