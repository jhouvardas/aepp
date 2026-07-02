<?php
include_once '../PageMaker.php';

class AdminPageMaker extends PageMaker
{
    public function displayHeadMatter()
    {
?>
        <!DOCTYPE html>
        <html lang="el">

        <head>
            <title>ΑΕΠΠ - Admin</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="../aepp.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

            <!-- Ενσωμάτωση CodeMirror για Syntax Highlighting HTML -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>

            <!-- Ενσωμάτωση js-beautify για αυτόματη μορφοποίηση HTML -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.7/beautify-html.min.js"></script>
            <style>
                .CodeMirror {
                    border: 1px solid #ced4da;
                    border-radius: 0.375rem;
                    height: auto;
                    min-height: 250px;
                    font-family: Consolas, monospace;
                    font-size: 14px;
                }

                /* Desktop nav — single row, no wrapping */
                @media (min-width: 1200px) {
                    #adminNavbar .nav-link {
                        white-space: nowrap;
                        padding-left: 0.4rem;
                        padding-right: 0.4rem;
                        font-size: 0.82rem;
                    }
                    .navbar-brand { font-size: 0.9rem; margin-right: 0.3rem; }
                }
                @media (min-width: 1400px) {
                    #adminNavbar .nav-link {
                        padding-left: 0.6rem;
                        padding-right: 0.6rem;
                        font-size: 0.9rem;
                    }
                }
            </style>

        </head>

        <body class="admin-body">
        <?php
    }

    // Προσθέτουμε παραμέτρους για το userYear και το dbHandler
    public function displayMenu($userYear = '', $dbHandler = null)
    {
        $pendingRequestsCount = 0;
        if ($dbHandler && !empty($userYear)) {
            $pendingRequestsCount = $dbHandler->getPendingExtensionRequestsCount($userYear);
        }
        ?>
            <nav class="navbar navbar-expand-xl navbar-dark bg-dark shadow mb-4">
                <div class="container-fluid">

                    <a class="navbar-brand fw-bold" href="index.php">
                        <i class="fa fa-cog text-warning"></i> ADMIN
                    </a>

                    <div class="collapse navbar-collapse" id="adminNavbar">
                        <ul class="navbar-nav">

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                    <i class="fa fa-users text-success"></i> Μαθητές
                                </a>
                                <ul class="dropdown-menu shadow border-success">
                                    <li><a class="dropdown-item text-success fw-bold" href="index.php?action=viewStudentProfile"><i class="fa fa-address-card"></i> Καρτέλες Μαθητών</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-warning fw-bold" href="index.php?action=group_email_form"><i class="fa fa-envelope"></i> Email Ομάδων</a></li>
                                    <li><a class="dropdown-item text-secondary fw-bold" href="index.php?action=mass_sms_form"><i class="fa fa-mobile-phone"></i> Μαζικό SMS</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                    <i class="fa fa-tasks text-info"></i> Εργασίες
                                </a>
                                <ul class="dropdown-menu shadow border-info">
                                    <li><a class="dropdown-item" href="index.php?action=assign_tasks"><i class="fa fa-plus"></i> Ανάθεση Εργασιών</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=list_all_tasks"><i class="fa fa-history"></i> Ιστορικό Εργασιών</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="fa fa-book text-light"></i> Θεωρία
                                </a>
                                <ul class="dropdown-menu shadow">
                                    <li><a class="dropdown-item" href="index.php?action=add_theory"><i class="fa fa-plus-circle"></i> Νέα Ερώτηση</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=list_theory"><i class="fa fa-list"></i> Λίστα Θεωρίας</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=manage_books"><i class="fa fa-book"></i> Διαχείριση Βιβλίων</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?action=list_for_test"><i class="fa fa-file-text-o"></i> Δημιουργία Τεστ</a></li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="index.php?action=manage_announcements">
                                    <i class="fa fa-bullhorn text-primary"></i> Ανακοινώσεις
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                    <i class="fa fa-coffee text-warning"></i> Μεζεδάκια
                                </a>
                                <ul class="dropdown-menu shadow border-danger">
                                    <li><a class="dropdown-item" href="index.php?action=addMezedaki"><i class="fa fa-plus-circle text-primary"></i> Νέα Εισαγωγή</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=listMezedakia"><i class="fa fa-list text-secondary"></i> Λίστα / Διαχείριση</a></li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?action=view_extension_requests">
                                            <i class="fa fa-clock-o text-danger"></i> Αιτήματα Παράτασης
                                            <?php if ($pendingRequestsCount > 0): ?>
                                                <span class="badge bg-danger rounded-pill ms-1"><?php echo $pendingRequestsCount; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li><a class="dropdown-item" href="index.php?action=manage_exercise_types"><i class="fa fa-tags text-info"></i> Τύποι/Τεχνικές</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=mezeBank"><i class="fa fa-archive text-warning"></i> Τράπεζα Μεζεδακίων</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?action=fullReport"><i class="fa fa-file-text-o text-success"></i> Συγκεντρωτικό Βαθμολόγιο</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                    <i class="fa fa-university text-info"></i> Σχολές
                                </a>
                                <ul class="dropdown-menu shadow border-info">
                                    <li><a class="dropdown-item" href="index.php?action=allSchoolPreferences"><i class="fa fa-list text-info"></i> Προτιμήσεις Μαθητών</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?action=importSchools"><i class="fa fa-upload text-secondary"></i> Import / Διαχείριση</a></li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="../index.php" target="_blank" title="Site Μαθητών">
                                    <i class="fa fa-external-link"></i> Site
                                </a>
                            </li>

                        </ul>
                    </div>

                    <!-- Persistent controls: outside collapse → never wrap on desktop -->
                    <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
                        <form action="index.php?action=setYear" method="post">
                            <div class="input-group input-group-sm">
                                <input type="number" name="exam_year"
                                       class="form-control border-0 text-center fw-bold"
                                       placeholder="<?php echo (date('m') >= 6 ? date('Y') + 1 : date('Y')); ?>"
                                       value="<?php echo htmlspecialchars($userYear); ?>"
                                       style="width:65px;" autocomplete="off" title="Σχολικό Έτος">
                                <button class="btn btn-secondary btn-sm border-0 fw-bold text-success" type="submit">OK</button>
                            </div>
                        </form>
                        <a class="btn btn-danger btn-sm" href="index.php?action=logout" title="Αποσύνδεση">
                            <i class="fa fa-sign-out"></i>
                        </a>
                    </div>

                    <button class="navbar-toggler ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"
                            aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                </div>
            </nav>
        <?php
    }

    public function displayLoginForm($error = '')
    {
        $this->displayHeadMatter();
        ?>
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-5">
                        <div class="card shadow-lg border-0 rounded-lg mt-5">
                            <div class="card-header bg-dark text-white text-center py-4">
                                <h3 class="font-weight-light my-2"><i class="fa fa-lock text-warning"></i> Admin Panel</h3>
                            </div>
                            <div class="card-body p-4">
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger shadow-sm"><i class="fa fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                                <?php endif; ?>
                                <form action="index.php?action=process_login" method="POST">
                                    <div class="form-group mb-3">
                                        <label class="fw-bold mb-1">Όνομα Χρήστη</label>
                                        <input class="form-control py-2" name="username" type="text" placeholder="Username" required autofocus />
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="fw-bold mb-1">Κωδικός</label>
                                        <input class="form-control py-2" name="password" type="password" placeholder="Password" required />
                                    </div>
                                    <div class="form-check mb-4 text-start">
                                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" value="1">
                                        <label class="form-check-label user-select-none" for="rememberMe" style="cursor: pointer;">
                                            Να με θυμάσαι σε αυτόν τον υπολογιστή
                                        </label>
                                    </div>
                                    <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm" type="submit"><i class="fa fa-sign-in"></i> Σύνδεση</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>

        </html>
    <?php
    }

    /**
     * Εμφανίζει μια ειδοποίηση (Toast) βασισμένη σε ένα status code.
     */
    public function showToast($status)
    {
        $message = "";
        $bgClass = "bg-primary";
        $icon = "fa-info-circle";

        switch ($status) {
            case 'ext_approved':
                $message = "Το αίτημα εγκρίθηκε και στάλθηκε email ενημέρωσης!";
                $bgClass = "bg-success";
                $icon = "fa-check-circle";
                break;
            case 'ext_rejected':
                $message = "Το αίτημα απορρίφθηκε και στάλθηκε email ενημέρωσης.";
                $bgClass = "bg-danger";
                $icon = "fa-times-circle";
                break;
            case 'meze_set_today':
                $message = "Ενημερώθηκε! Εμφανίζεται από σήμερα και λήγει αύριο στις 03:00.";
                $bgClass = "bg-success";
                $icon = "fa-check-circle";
                break;
            case 'update_success':
                $message = "Το μεζεδάκι ενημερώθηκε επιτυχώς!";
                $bgClass = "bg-success";
                $icon = "fa-save";
                break;
            case 'group_deadline_toggled':
                $message = "Η προθεσμία της ομάδας ενημερώθηκε!";
                $bgClass = "bg-info";
                $icon = "fa-users";
                break;
        }

        if (empty($message)) return;
    ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
            <div id="liveToast" class="toast align-items-center text-white <?php echo $bgClass; ?> border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa <?php echo $icon; ?> me-2"></i> <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('liveToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, {
                        delay: 5000
                    });
                    toast.show();
                    // Καθαρισμός του URL από το status χωρίς reload
                    if (window.history.replaceState) {
                        var url = new URL(window.location.href);
                        url.searchParams.delete('status');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                }
            });
        </script>
<?php
    }
}
