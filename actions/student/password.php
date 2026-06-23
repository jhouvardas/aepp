<?php
if ($action === 'processForgotPassword') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        if ($db->resetStudentPassword($email)) {
            echo "<div class='container mt-5 text-center'><div class='alert alert-success shadow d-inline-block px-5'><h4><i class='fa fa-check-circle text-success' style='font-size:40px;'></i> Επιτυχία!</h4><p>Ο νέος κωδικός στάλθηκε στο email σας.</p><hr><a href='index.php?action=myGrades' class='btn btn-success'><i class='fa fa-sign-in'></i> Σύνδεση</a></div></div>";
        } else {
            echo "<div class='container mt-5 text-center'><div class='alert alert-danger shadow d-inline-block px-5'><h4><i class='fa fa-exclamation-triangle'></i> Σφάλμα</h4><p>Δεν βρέθηκε ενεργός μαθητής με αυτό το email.</p><hr><a href='index.php?action=forgotPassword' class='btn btn-danger'>Επιστροφή</a></div></div>";
        }
    }
} else {
?>
    <div class="container mt-5">
        <div class="card shadow mx-auto" style="max-width: 500px; border-radius: 15px; border: none;">
            <div class="card-header bg-warning text-dark text-center py-3" style="border-radius: 15px 15px 0 0;">
                <h4 class="mb-0"><i class="fa fa-unlock-alt"></i> Επαναφορά Κωδικού</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted text-center mb-4">Συμπληρώστε το email σας και θα σας στείλουμε έναν νέο κωδικό πρόσβασης.</p>
                <form action="index.php?action=processForgotPassword" method="POST" autocomplete="off">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg shadow-sm" placeholder="name@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg w-100 shadow-sm fw-bold"><i class="fa fa-envelope"></i> Αποστολή Νέου Κωδικού</button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php?action=myGrades" class="text-decoration-none text-secondary"><i class="fa fa-arrow-left"></i> Επιστροφή στη Σύνδεση</a>
                </div>
            </div>
        </div>
    </div>
<?php
}
