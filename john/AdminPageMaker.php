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

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.6/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

            <style>
                /* Ρύθμιση ύψους για τον editor */
                .ck-editor__editable {
                    min-height: 250px;
                }

                /* Στυλ για την εκτύπωση */
                @media print {
                    .d-print-none {
                        display: none !important;
                    }

                    .container,
                    .container-fluid {
                        width: 100%;
                        margin: 0;
                        padding: 0;
                    }
                }

                /* Μικρή διόρθωση για την ευθυγράμμιση των εικονιδίων */
                .fa-external-link {
                    vertical-align: middle;
                    cursor: pointer;
                }
            </style>
        </head>

        <body>
        <?php
    }

    public function displayMenu()
    {
        ?>
            <nav class="navbar navbar-expand-md bg-danger navbar-dark shadow">
                <a class="navbar-brand" href="index.php">ADMIN PANEL</a>
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

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" id="mezedakiaDrop" data-toggle="dropdown">
                                <i class="fa fa-coffee"></i> Μεζεδάκια
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="index.php?action=addMezedaki">
                                    <i class="fa fa-plus text-primary"></i> 1. Νέα Εισαγωγή
                                </a>
                                <a class="dropdown-item" href="index.php?action=listMezedakia">
                                    <i class="fa fa-tasks text-secondary"></i> 2. Λίστα / Διαγραφή
                                </a>
                                <a class="dropdown-item" href="index.php?action=fullReport">3. Συγκεντρωτικό Βαθμολόγιο</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
                                Πανελλήνιες
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="index.php?action=addKena">Αλγόριθμοι για συμπλήρωση κενών</a>
                                <a class="dropdown-item" href="index.php?action=listKena">Λίστα Ασκήσεων (Κενά)</a>
                                <a class="dropdown-item" href="index.php?action=addThemaG">Θέμα Γ (Προγράμματα)</a>
                                <a class="dropdown-item" href="index.php?action=listThemaG">Λίστα Θεμάτων Γ</a>
                            </div>
                        </li>

                        <li class="nav-item ml-md-5">
                            <a class="nav-link text-warning" href="../index.php" target="_blank border">Προβολή Site Μαθητών</a>
                        </li>
                    </ul>

                    <form class="form-inline ml-auto" action="index.php?action=setYear" method="post">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-dark text-white border-0"><i class="fa fa-user"></i></span>
                            </div>
                            <input type="text" name="tutor_user" class="form-control border-0"
                                placeholder="Username Tutor"
                                value="<?php echo isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : ''; ?>"
                                style="width: 120px;">
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
