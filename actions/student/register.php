<?php
if ($action === 'processRegistration') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $result = $db->registerStudent($_POST);

        if ($result === "email_exists") {
            echo "<div class='container mt-5 text-center'><div class='alert alert-warning shadow d-inline-block px-5'><h4><i class='fa fa-exclamation-triangle'></i> Το Email υπάρχει ήδη!</h4><p>Παρακαλώ χρησιμοποιήστε άλλο email ή συνδεθείτε στο λογαριασμό σας.</p><hr><a href='index.php?action=register' class='btn btn-warning'>Επιστροφή</a></div></div>";
        } elseif ($result) {
            $adminEmail  = defined('SMTP_USER') ? SMTP_USER : 'admin@jhouv.eu';
            $studentName = htmlspecialchars($_POST['name'] . ' ' . $_POST['lastName']);
            $studentEmail = htmlspecialchars($_POST['email']);
            $schoolYear  = date('Y') + 1;
            $phone       = htmlspecialchars($_POST['phone']);
            $birthdate   = htmlspecialchars(isset($_POST['birthdate']) ? $_POST['birthdate'] : '-');
            $school      = htmlspecialchars(isset($_POST['school']) ? $_POST['school'] : '-');

            $subject = "Νέα Εγγραφή Μαθητή: $studentName";
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #007bff;'>Νέα Εγγραφή στην ΑΕΠΠ</h2>
                    <p>Ένας νέος μαθητής μόλις γράφτηκε στην πλατφόρμα!</p>
                    <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>
                        <p><strong>Ονοματεπώνυμο:</strong> $studentName</p>
                        <p><strong>Email:</strong> $studentEmail</p>
                        <p><strong>Τηλέφωνο:</strong> $phone</p>
                        <p><strong>Ημ/νία Γέννησης:</strong> $birthdate</p>
                        <p><strong>Σχολή:</strong> $school</p>
                        <p><strong>Σχολικό Έτος:</strong> $schoolYear</p>
                    </div>
                    <p>Μπορείτε να διαχειριστείτε την καρτέλα του από το <a href='http://jhouv.eu/aepp/john/index.php' style='color:#007bff; text-decoration:none;'>Admin Panel</a>.</p>
                </div>
            ";
            $db->sendSystemEmail($adminEmail, $subject, $body);

            echo "<div class='container mt-5 text-center'><div class='alert alert-success shadow d-inline-block px-5'><h4><i class='fa fa-check-circle text-success' style='font-size:40px;'></i> Εγγραφή Επιτυχής!</h4><p>Μπορείτε πλέον να συνδεθείτε με το Email και τον 6-ψήφιο κωδικό σας.</p><hr><a href='index.php?action=myGrades' class='btn btn-success'><i class='fa fa-sign-in'></i> Σύνδεση στις Βαθμολογίες</a></div></div>";
        } else {
            echo "<div class='container mt-5 text-center'><div class='alert alert-danger shadow d-inline-block px-5'><h4><i class='fa fa-times-circle'></i> Σφάλμα!</h4><p>Κάτι πήγε στραβά με την αποθήκευση των στοιχείων.</p><hr><a href='index.php?action=register' class='btn btn-danger'>Επιστροφή</a></div></div>";
        }
    }
} else {
    $fm->studentRegistrationForm();
}
