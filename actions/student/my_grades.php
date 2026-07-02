<?php
        $loginError = false;
        if (isset($_SESSION['login_error'])) {
            $loginError = true;
            unset($_SESSION['login_error']);
        }
        $returnTo = isset($_GET['returnTo']) ? htmlspecialchars($_GET['returnTo']) : '';
    ?>
        <div class="container mt-5">
            <div class="card shadow mx-auto" style="max-width: 500px; border-radius: 15px; border: none;">
                <div class="card-header bg-primary text-white text-center py-3" style="border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fa fa-graduation-cap"></i> Σύνδεση</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($loginError): ?>
                    <div class="alert alert-danger text-center"><i class="fa fa-times-circle"></i> Λάθος email ή κωδικός. Δοκίμασε ξανά.</div>
                    <?php endif; ?>
                    <p class="text-muted text-center mb-4">Πληκτρολογήστε το Email και τον προσωπικό σας κωδικό.</p>
                    <form action="index.php?action=showMyGrades<?php echo $returnTo ? '&returnTo=' . $returnTo : ''; ?>" method="POST" autocomplete="on" onsubmit="return true;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="student_email" class="form-control form-control-lg shadow-sm" placeholder="name@example.com" autocomplete="email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Προσωπικός Κωδικός</label>
                            <div class="position-relative">
                                <input type="password" name="st_access" id="st_access_login"
                                    class="form-control form-control-lg text-center"
                                    placeholder="••••••" maxlength="15" required
                                    autocomplete="current-password"
                                    inputmode="numeric" pattern="[0-9]*"
                                    style="background-color: white; padding-right: 45px;">
                                <i class="fa fa-eye position-absolute top-50 translate-middle-y end-0 me-3"
                                    id="eye_login" style="cursor: pointer; color: #6c757d; z-index: 10;"
                                    onclick="toggleMask('st_access_login', 'eye_login')"></i>
                            </div>
                            <div class="text-end mt-2">
                                <a href="index.php?action=forgotPassword" class="text-decoration-none small text-primary"><i class="fa fa-question-circle"></i> Ξέχασα τον κωδικό μου</a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm"><i class="fa fa-search"></i> Εμφάνιση Αποτελεσμάτων</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
