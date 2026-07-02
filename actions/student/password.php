<?php
if ($action === 'processForgotPassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['reset_code'])) {
        // Βήμα 2: Επαλήθευση κωδικού + ορισμός νέου κωδικού
        $enteredCode = trim($_POST['reset_code']);
        $newPass     = trim(isset($_POST['new_pass'])  ? $_POST['new_pass']  : '');
        $newPass2    = trim(isset($_POST['new_pass2']) ? $_POST['new_pass2'] : '');
        $resetData   = isset($_SESSION['pwd_reset']) ? $_SESSION['pwd_reset'] : null;

        $error = null;
        if (!$resetData || time() > $resetData['expires']) {
            $error = 'expired';
        } elseif ($enteredCode !== $resetData['code']) {
            $error = 'wrong_code';
        } elseif (!preg_match('/^\d{6}$/', $newPass)) {
            $error = 'not_6digits';
        } elseif ($newPass !== $newPass2) {
            $error = 'mismatch';
        } else {
            $ok = $db->updateStudentPasswordByEmail($resetData['email'], $newPass);
            if ($ok) {
                unset($_SESSION['pwd_reset']);
                ?>
                <div class="container mt-5 text-center">
                    <div class="card shadow d-inline-block px-5 py-4" style="border-radius:15px; max-width:500px;">
                        <i class="fa fa-check-circle text-success" style="font-size:70px;"></i>
                        <h4 class="mt-3 fw-bold">Ο κωδικός άλλαξε!</h4>
                        <p class="text-muted">Μπορείς τώρα να συνδεθείς με τον νέο σου κωδικό.</p>
                        <a href="index.php?action=myGrades" class="btn btn-primary mt-2 px-4">
                            <i class="fa fa-sign-in"></i> Σύνδεση
                        </a>
                    </div>
                </div>
                <?php
                return;
            } else {
                $error = 'db_error';
            }
        }

        // Εμφάνιση φόρμας επαλήθευσης με σφάλμα
        $resetEmail = $resetData ? $resetData['email'] : '';
        ?>
        <div class="container mt-5">
            <div class="card shadow mx-auto" style="max-width:500px; border-radius:15px;">
                <div class="card-header bg-warning text-dark text-center py-3" style="border-radius:15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fa fa-unlock-alt"></i> Επαναφορά Κωδικού</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error === 'expired'): ?>
                        <div class="alert alert-danger"><i class="fa fa-clock-o"></i> Ο κωδικός επαλήθευσης έληξε. <a href="index.php?action=forgotPassword">Ξεκίνα ξανά</a>.</div>
                    <?php elseif ($error === 'wrong_code'): ?>
                        <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Λάθος κωδικός επαλήθευσης.</div>
                    <?php elseif ($error === 'not_6digits'): ?>
                        <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Ο νέος κωδικός πρέπει να είναι ακριβώς 6 ψηφία.</div>
                    <?php elseif ($error === 'mismatch'): ?>
                        <div class="alert alert-danger"><i class="fa fa-times-circle"></i> Οι δύο νέοι κωδικοί δεν ταιριάζουν.</div>
                    <?php else: ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Κάτι πήγε στραβά. Δοκίμασε ξανά.</div>
                    <?php endif; ?>
                    <p class="text-muted text-center small">Στάλθηκε κωδικός στο <b><?php echo htmlspecialchars($resetEmail); ?></b></p>
                    <form method="POST" action="index.php?action=processForgotPassword">
                        <div class="mb-3">
                            <label class="fw-bold">Κωδικός από Email</label>
                            <input type="text" name="reset_code" class="form-control form-control-lg text-center fw-bold"
                                maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="______" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Νέος Κωδικός <small class="text-muted">(6 ψηφία)</small></label>
                            <input type="password" name="new_pass" class="form-control form-control-lg text-center"
                                maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="••••••">
                        </div>
                        <div class="mb-4">
                            <label class="fw-bold">Επιβεβαίωση Νέου Κωδικού</label>
                            <input type="password" name="new_pass2" class="form-control form-control-lg text-center"
                                maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="••••••">
                        </div>
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="fa fa-save"></i> Αποθήκευση Νέου Κωδικού
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php

    } else {
        // Βήμα 1: Αποστολή κωδικού επαλήθευσης
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $code  = sprintf("%06d", mt_rand(100000, 999999));

        if ($db->sendPasswordResetCode($email, $code)) {
            $_SESSION['pwd_reset'] = [
                'email'   => $email,
                'code'    => $code,
                'expires' => time() + 3600,
            ];
            ?>
            <div class="container mt-5">
                <div class="card shadow mx-auto" style="max-width:500px; border-radius:15px;">
                    <div class="card-header bg-warning text-dark text-center py-3" style="border-radius:15px 15px 0 0;">
                        <h4 class="mb-0"><i class="fa fa-unlock-alt"></i> Επαναφορά Κωδικού</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-success text-center">
                            <i class="fa fa-envelope fa-2x mb-2"></i><br>
                            Στάλθηκε κωδικός 6 ψηφίων στο <b><?php echo htmlspecialchars($email); ?></b>.<br>
                            <small class="text-muted">Ισχύει για 1 ώρα.</small>
                        </div>
                        <form method="POST" action="index.php?action=processForgotPassword">
                            <div class="mb-3">
                                <label class="fw-bold">Κωδικός από Email</label>
                                <input type="text" name="reset_code" class="form-control form-control-lg text-center fw-bold"
                                    maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="______" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Νέος Κωδικός <small class="text-muted">(6 ψηφία)</small></label>
                                <input type="password" name="new_pass" class="form-control form-control-lg text-center"
                                    maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="••••••">
                            </div>
                            <div class="mb-4">
                                <label class="fw-bold">Επιβεβαίωση Νέου Κωδικού</label>
                                <input type="password" name="new_pass2" class="form-control form-control-lg text-center"
                                    maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="••••••">
                            </div>
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="fa fa-save"></i> Αποθήκευση Νέου Κωδικού
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        } else {
            echo "<div class='container mt-5 text-center'><div class='alert alert-danger shadow d-inline-block px-5'><h4><i class='fa fa-exclamation-triangle'></i> Σφάλμα</h4><p>Δεν βρέθηκε ενεργός μαθητής με αυτό το email.</p><hr><a href='index.php?action=forgotPassword' class='btn btn-danger'>Επιστροφή</a></div></div>";
        }
    }

} else {
    // Βήμα 0: Φόρμα email
?>
    <div class="container mt-5">
        <div class="card shadow mx-auto" style="max-width: 500px; border-radius: 15px; border: none;">
            <div class="card-header bg-warning text-dark text-center py-3" style="border-radius: 15px 15px 0 0;">
                <h4 class="mb-0"><i class="fa fa-unlock-alt"></i> Επαναφορά Κωδικού</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted text-center mb-4">Συμπλήρωσε το email σου και θα σου στείλουμε έναν κωδικό επαλήθευσης.</p>
                <form action="index.php?action=processForgotPassword" method="POST" autocomplete="off">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg shadow-sm" placeholder="name@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg w-100 shadow-sm fw-bold">
                        <i class="fa fa-envelope"></i> Αποστολή Κωδικού Επαλήθευσης
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php?action=myGrades" class="text-decoration-none text-secondary"><i class="fa fa-arrow-left"></i> Επιστροφή στη Σύνδεση</a>
                </div>
            </div>
        </div>
    </div>
<?php
}
