<?php

class PageMaker
{

    public function displayHeadMatter()
    {
?>
        <!DOCTYPE html>
        <html lang="el">

        <head>
            <title>ΑΕΠΠ</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.6/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
            <link rel="icon" href="images/favicon.jpg" sizes="16x16" type="image/jpg">
            <link rel="stylesheet" href="aepp.css">
        </head>

        <body>
        <?php
    }

    public function displayMenu()
    {
        ?>
            <nav class="navbar navbar-expand-md bg-dark navbar-dark">
                <a class="navbar-brand" href="#" id="menu">ΑΕΠΠ</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav">

                        <li class="nav-item">
                            <a class="nav-link text-warning font-weight-bold" href="index.php?action=viewMezedakia">
                                <i class="fa fa-star"></i> Μεζεδάκια
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#max">Μεγαλύτερος</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#akatalili">Ακατάλληλη τιμή</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#egirotita">Έλεγχος εγκυρότητας</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="index.php?action=debit" id="navbardrop" data-toggle="dropdown">
                                Δομές Δεδομένων
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="index.php#1d">Πίνακες Μονοδιάστατοι</a>
                                <a class="dropdown-item" href="index.php#2d">Πίνακες 2 διαστάσεων</a>
                                <a class="dropdown-item" href="domesDedomenon.php">Στοίβα</a>
                            </div>

                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="index.php?action=debit" id="navbardrop" data-toggle="dropdown">
                                Πανελλήνιες 2025
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="instructions.php">Οδηγίες</a>
                                <a class="dropdown-item" href="epanalipsi.php">Ασκήσεις Επανάληψης</a>
                                <a class="dropdown-item" href="askisi2025.php">Η τελευταία μας άσκηση</a>
                            </div>

                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="index.php#diagrammata">Διαγράμματα</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="index.php?action=debit" id="navbardrop" data-toggle="dropdown">
                                Πανελλήνιες
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="kena.php">Αλγόριθμοι για συμπλήρωση κενών από Α και Β θέματα</a>
                                <a class="dropdown-item" href="c.php">Γ θέματα από 2008 μέχρι 2022 </a>
                                <a class="dropdown-item" href="a_a.php">Σωστό - Λάθός από όλα τα θέματα από 2008 μέχρι 2022</a>
                                <a class="dropdown-item" href="a_anaptyxis.php">Όλες οι ερωτήσεις ανάπτυξης από 2008 μέχρι 2022</a>
                                <a class="dropdown-item" href="index.php?action=listKenaDynamic">Αλγόριθμοι για Συμπλήρωση κενών 2</a>
                                <a class="dropdown-item" href="index.php?action=showThemaGDForm">Θέματα Γ & Δ</a>
                            </div>

                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#theoria">Θεωρία</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="select.php">Επίλεξε</a>
                        </li>
                        <!--                            <li class="nav-item">
                                                            <a class="nav-link" href="https://docs.google.com/presentation/d/e/2PACX-1vTZ7yLiJJmucUygqXXydM9R2W5VqlM0gaUJdembo_04YAriQ3QYJW3Wo0Q1TanxZqDFN5xzA0ZYCIpO/pub?start=false&loop=false&delayms=5000">Εκσφαλμάτωση</a>
                                                        </li>   -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbardrop" data-toggle="dropdown">
                                Ασκήσεις
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="askisisKopsinis1.php">Κοψίνης Τεύχος 1</a>
                                <a class="dropdown-item" href="askisisKopsinis2.php">Κοψίνης Τεύχος 2</a>
                            </div>

                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books.php">Ύλη - Βιβλία</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="page.php">Links</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <br>

        <?php
    }

    public function displayEndMatter()
    {
        ?>
        </body>

        </html>
    <?php
    }

    public function displayLinks()
    {
    ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h4>Προγραμματιστικά περιβάλλοντα</h4>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a class="nav-link" href="https://spinet.gr/glossomatheia/download/" target="_blank">Γλωσσομάθεια</a>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a class="nav-link" href="https://alkisg.mysch.gr/downloads/" target="_blank">Ο διερμηνευτής της ΓΛΩΣΣΑΣ</a>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h4>Θέματα Πανελληνίων</h4>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a class="nav-link" href="https://www.panellinies.net/%CE%B1%CE%B5%CF%80%CF%80-%CE%B1%CE%BD%CE%AC%CF%80%CF%84%CF%85%CE%BE%CE%B7-%CE%B5%CF%86%CE%B1%CF%81%CE%BC%CE%BF%CE%B3%CF%8E%CE%BD-%CF%83%CE%B5-%CF%80%CF%81%CE%BF%CE%B3%CF%81%CE%B1%CE%BC%CE%BC%CE%B1/" target="_blank">Ημερήσια Λύκεια</a>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a class="nav-link" href="https://aepp.edu.gr/palaiotera-themata/esperina-lykeia/" target="_blank">Εσπερινά Λύκεια</a>
                </div>
            </div>
        </div>
    <?php
    }

    public function displayKenaGallery($result)
    {
    ?>
        <div class="container-fluid mt-4 px-2">
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h2 class="display-4">Αλγόριθμοι για Συμπλήρωση Κενών</h2>
                    <p class="lead text-muted">Θέματα από τις Πανελλήνιες Εξετάσεις</p>
                    <hr>
                </div>
            </div>

            <div class="row mx-n2"> <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4 px-2">
                            <div class="card h-100 shadow-sm border-0 exercise-card">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                                    <span class="badge badge-warning"><?php echo $row['exerciseYear']; ?></span>
                                    <small class="font-weight-bold"><?php echo $row['examType']; ?></small>
                                </div>
                                <div class="card-body d-flex flex-column exercise-body">
                                    <div class="px-3 pt-2">
                                        <h6 class="card-title font-weight-bold text-primary mb-1">
                                            <?php echo $row['schoolType']; ?>
                                        </h6>

                                        <?php if (!empty($row['exerciseDescription'])): ?>
                                            <p class="card-text small text-muted mb-2 italic">
                                                <i class="fa fa-info-circle"></i> <?php echo $row['exerciseDescription']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-auto">
                                        <?php if (!empty($row['exerciseHtml'])): ?>
                                            <div class="html-content-wrapper bg-white shadow-inner">
                                                <div class="table-responsive">
                                                    <?php echo $row['exerciseHtml']; ?>
                                                </div>
                                            </div>
                                        <?php elseif (!empty($row['imageName'])): ?>
                                            <div class="text-center p-2">
                                                <a target="_blank" href="images/themata/kenaNew/<?php echo $row['imageName']; ?>">
                                                    <img class="img-fluid rounded border hover-zoom"
                                                        src="images/themata/kenaNew/<?php echo $row['imageName']; ?>"
                                                        alt="Άσκηση <?php echo $row['exerciseYear']; ?>">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center shadow-sm mx-2">
                            <i class="fa fa-info-circle"></i> Δεν έχουν προστεθεί ακόμα ασκήσεις.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .exercise-card {
                transition: transform 0.2s ease-in-out;
            }

            .exercise-card:hover {
                transform: translateY(-5px);
            }

            .hover-zoom:hover {
                cursor: zoom-in;
                opacity: 0.9;
            }

            .italic {
                font-style: italic;
            }

            /* Default ρυθμίσεις */
            .exercise-body {
                padding: 0;
            }

            /* Μηδενίζουμε το padding της κάρτας */

            .html-content-wrapper {
                font-size: 0.85rem;
                max-width: 100%;
                overflow-x: hidden;
            }

            .html-content-wrapper pre {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                background-color: #f8f9fa !important;
                border-top: 1px solid #dee2e6 !important;
                border-bottom: 1px solid #dee2e6 !important;
                border-left: none !important;
                border-right: none !important;
                padding: 15px !important;
                margin: 0 !important;
                font-family: 'Courier New', Courier, monospace !important;
                width: 100% !important;
            }

            /* ΕΙΔΙΚΕΣ ΡΥΘΜΙΣΕΙΣ ΓΙΑ ΚΙΝΗΤΑ (max 576px) */
            @media (max-width: 576px) {
                .container-fluid {
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                }

                .row.mx-n2 {
                    margin-left: -5px !important;
                    margin-right: -5px !important;
                }

                .px-2 {
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                }

                .html-content-wrapper pre {
                    padding: 8px 5px !important;
                    /* Πολύ μικρό padding μέσα στον κώδικα */
                    font-size: 0.78rem !important;
                    border-radius: 0 !important;
                    /* Κατάργηση γωνιών για να πιάνει όλο το πλάτος */
                }

                .card-header {
                    padding: 8px 10px !important;
                }
            }

            /* Καθαρισμός εσωτερικών Bootstrap στοιχείων από το paste */
            .html-content-wrapper .container-fluid,
            .html-content-wrapper .row,
            .html-content-wrapper .col-sm {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
        </style>
        <?php
    }

    public function displayThemaGD($result)
    {
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Δημιουργούμε ένα μοναδικό ID για κάθε accordion
                $uniqueID = $row['id'];
        ?>
                <div class="container-fluid p-0 mt-4 mb-5">
                    <div class="card shadow-sm mobile-friendly-card border-0">
                        <div class="card-header bg-dark text-white p-2">
                            <h5 class="mb-0" style="font-size: 1.1rem;">
                                Θέμα <?php echo $row['thema_type']; ?> - <?php echo $row['etos']; ?>
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="html-content-wrapper mb-3">
                                <?php echo $row['ekfonisi']; ?>
                            </div>

                            <?php if (!empty($row['lysi'])): ?>
                                <div id="accordion<?php echo $uniqueID; ?>">
                                    <div class="card border-success">
                                        <div class="card-header p-0">
                                            <button class="btn btn-success btn-block btn-sm"
                                                data-toggle="collapse"
                                                data-target="#collapseLysi<?php echo $uniqueID; ?>">
                                                Εμφάνιση Λύσης
                                            </button>
                                        </div>
                                        <div id="collapseLysi<?php echo $uniqueID; ?>" class="collapse" data-parent="#accordion<?php echo $uniqueID; ?>">
                                            <div class="card-body p-1 bg-light">
                                                <pre class="aepp-code" style="white-space: pre-wrap; margin:0;"><?php echo htmlspecialchars($row['lysi']); ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else {
            echo "<div class='container mt-4'><div class='alert alert-info'>Δεν βρέθηκαν θέματα.</div></div>";
        }
    }

    public function displayMezedakiaList($result)
    {
        if ($result && $result->num_rows > 0) {
            $db = new DbHandler();
            $fm = new FormMaker();

            // 1. Δυναμική λήψη του τρέχοντος έτους από τη βάση (τελευταία εγγραφή στον user)
            $userYear = $db->getCurrentTutorYear();

            // 2. Φέρνουμε τους μαθητές (Η getTutorStudents σου επιστρέφει πλέον έτοιμο Array)
            $studentsArray = $db->getTutorStudents($userYear);

            // Ασφάλεια: Αν η μέθοδος επιστρέψει false ή null, ορίζουμε κενό array για να μην "σκάσει" η foreach
            if (!$studentsArray) {
                $studentsArray = [];
            }

            while ($row = $result->fetch_assoc()) {
                $mId = $row['mezeId'];
                $now = new DateTime();
                $solDate = new DateTime($row['solutionDate']);
                $isPastDeadline = ($now > $solDate);
            ?>
                <div class="container-fluid p-0 mt-4 mb-4">
                    <div class="card shadow-sm mobile-friendly-card border-warning">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><strong>Μεζεδάκι #<?php echo $row['mezeNumber']; ?></strong></h6>
                            <div class="text-right">
                                <small class="font-weight-bold d-block">
                                    <i class="fa fa-calendar"></i> <?php echo date('d/m/Y', strtotime($row['mezeDate'])); ?>
                                </small>
                                <small class="text-danger font-weight-bold">
                                    <i class="fa fa-hourglass-end"></i> Deadline: <?php echo $solDate->format('d/m/Y H:i'); ?>
                                </small>
                            </div>
                        </div>

                        <div class="card-body p-2">
                            <?php if (!empty($row['mezeImage'])): ?>
                                <div class="text-center mb-3">
                                    <img src="images/mezedakia/<?php echo $row['mezeImage']; ?>" class="img-fluid rounded border shadow-sm" style="max-width: 100%;">
                                </div>
                            <?php endif; ?>

                            <div class="meze-text px-1">
                                <?php echo $row['mezeText']; ?>
                            </div>

                            <hr>

                            <?php if (!$isPastDeadline): ?>
                                <div class="mt-2">
                                    <?php
                                    if (!empty($studentsArray)) {
                                        // Περνάμε το έτοιμο array των μαθητών στη φόρμα υποβολής
                                        $fm->studentSubmissionForm($studentsArray, $mId);
                                    } else {
                                        // Μήνυμα αν για το έτος που βρέθηκε δεν υπάρχουν μαθητές στη βάση tutor
                                        echo "<div class='alert alert-light border text-muted small shadow-sm'>
                                                <i class='fa fa-exclamation-triangle text-warning'></i> 
                                                Η φόρμα υποβολής δεν είναι διαθέσιμη (δεν βρέθηκαν ενεργοί μαθητές για το έτος: <b>$userYear</b>).
                                              </div>";
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            $hasSolution = (!empty($row['mezeSolution']) || !empty($row['mezeSolutionImage']));
                            if ($hasSolution): ?>
                                <div id="accMeze<?php echo $mId; ?>" class="mt-3">
                                    <?php if ($isPastDeadline): ?>
                                        <button class="btn btn-success btn-block btn-sm font-weight-bold shadow-sm" data-toggle="collapse" data-target="#sol<?php echo $mId; ?>">
                                            <i class="fa fa-key"></i> Εμφάνιση Λύσης
                                        </button>
                                        <div id="sol<?php echo $mId; ?>" class="collapse mt-2" data-parent="#accMeze<?php echo $mId; ?>">
                                            <div class="p-2 bg-light border rounded shadow-sm">
                                                <?php if (!empty($row['mezeSolutionImage'])): ?>
                                                    <div class="text-center mb-3">
                                                        <img src="images/mezedakia/<?php echo $row['mezeSolutionImage']; ?>" class="img-fluid rounded border shadow-sm border-success" style="max-width: 100%;">
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($row['mezeSolution'])): ?>
                                                    <div class="solution-text">
                                                        <?php echo $row['mezeSolution']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-secondary mb-0 p-2 text-center shadow-sm" style="font-size: 0.85rem; border-style: dashed;">
                                            <i class="fa fa-clock-o text-danger"></i>
                                            Η λύση ξεκλειδώνει αυτόματα μετά τη λήξη της προθεσμίας.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
<?php
            }
        } else {
            echo "<div class='container mt-4'><div class='alert alert-warning shadow-sm'><i class='fa fa-info-circle'></i> Δεν υπάρχουν ακόμη διαθέσιμα μεζεδάκια!</div></div>";
        }
    }
}
