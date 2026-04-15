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

            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

            <link rel="stylesheet" href="../aepp.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.6/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

        </head>

        <body class="admin-body">
        <?php
    }

    public function displayMenu()
    {
        ?>
            <nav class="navbar navbar-dark bg-danger shadow mb-4">
                <a class="navbar-brand font-weight-bold" href="index.php">ADMIN PANEL</a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=add_theory">Νέα Ερώτηση</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=list_theory">Λίστα Θεωρίας</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=manage_books">Διαχείριση Βιβλίων</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=list_for_test">Δημιουργία Τεστ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-info" href="index.php?action=assign_tasks">
                                <i class="fa fa-tasks"></i> Ανάθεση Εργασιών
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=list_all_tasks">
                                <i class="fa fa-history"></i> Ιστορικό Εργασιών
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-success font-weight-bold" href="index.php?action=viewStudentProfile">
                                <i class="fa fa-address-card"></i> Καρτέλες Μαθητών
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=manage_groups">
                                <i class="fa fa-users"></i> Διαχείριση Ομάδων
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" id="mezedakiaDrop" data-toggle="dropdown">
                                <i class="fa fa-coffee"></i> Μεζεδάκια
                            </a>
                            <div class="dropdown-menu shadow border-danger">
                                <a class="dropdown-item" href="index.php?action=addMezedaki">
                                    <i class="fa fa-plus-circle text-primary"></i> 1. Νέα Εισαγωγή
                                </a>
                                <a class="dropdown-item" href="index.php?action=listMezedakia">
                                    <i class="fa fa-list text-secondary"></i> 2. Λίστα / Διαχείριση
                                </a>
                                <a class="dropdown-item" href="index.php?action=manage_exercise_types">
                                    <i class="fa fa-tags text-info"></i> 3. Διαχείριση Τύπων/Τεχνικών
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="index.php?action=fullReport">
                                    <i class="fa fa-file-text-o text-success"></i> 3. Συγκεντρωτικό Βαθμολόγιο
                                </a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
                                Πανελλήνιες
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="index.php?action=addKena">Αλγόριθμοι για συμπλήρωση κενών</a>
                                <a class="dropdown-item" href="index.php?action=listKena">Λίστα Ασκήσεων (Κενά)</a>
                                <a class="dropdown-item" href="index.php?action=addThemaG">Θέμα Γ (Προγράμματα)</a>
                                <a class="dropdown-item" href="index.php?action=listThemaG">Λίστα Θεμάτων Γ</a>
                            </div>
                        </li>

                        <li class="nav-item ml-md-4">
                            <a class="nav-link text-white border border-white rounded px-2" href="../index.php" target="_blank">
                                <i class="fa fa-external-link"></i> Site Μαθητών
                            </a>
                        </li>
                    </ul>

                    <hr class="border-secondary">

                    <form class="form-inline pb-3" action="index.php?action=setYear" method="post">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-dark text-white border-0"><i class="fa fa-user"></i></span>
                            </div>
                            <input type="text" name="tutor_user" class="form-control border-0"
                                placeholder="Username"
                                value="<?php echo isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : ''; ?>"
                                style="width: 120px;"
                                autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-dark border-0 text-success font-weight-bold" type="submit">Ορισμός</button>
                            </div>
                        </div>
                    </form>
                </div>
            </nav>
    <?php
    }
}
