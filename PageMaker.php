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
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <link rel="icon" href="images/favicon.jpg" sizes="16x16" type="image/jpg">
            <link rel="stylesheet" href="aepp.css?v=<?php echo @filemtime(__DIR__ . '/aepp.css'); ?>">
        </head>

        <body>
        <?php
    }

    public function displayMenu()
    {
        ?>
            <nav class="navbar navbar-dark bg-dark shadow">
                <a class="navbar-brand font-weight-bold" href="index.php" id="menu">
                    <i class="fa fa-code"></i> ΑΕΠΠ
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning fw-bold" href="#" id="navbardropStudents" data-bs-toggle="dropdown">
                                <i class="fa fa-users"></i> Μαθητές
                            </a>
                            <div class="dropdown-menu shadow border-warning">
                                <a class="dropdown-item fw-bold text-primary" href="index.php?action=register">
                                    <i class="fa fa-user-plus"></i> Εγγραφή
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item fw-bold text-warning" href="index.php?action=viewMezedakia">
                                    <i class="fa fa-star"></i> Μεζεδάκια
                                </a>
                                <a class="dropdown-item fw-bold text-info" href="index.php?action=myGrades">
                                    <i class="fa fa-bar-chart"></i> Βαθμοί & Εργασίες
                                </a>
                                <a class="dropdown-item fw-bold text-success" href="index.php?action=announcements">
                                    <i class="fa fa-bullhorn"></i> Ανακοινώσεις
                                </a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardropAlgos" data-bs-toggle="dropdown">
                                Αλγόριθμοι
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="index.php#max">Μεγαλύτερος - Μικρότερος</a>
                                <a class="dropdown-item" href="index.php#akatalili">Ακατάλληλη τιμή</a>
                                <a class="dropdown-item" href="index.php#egirotita">Έλεγχος εγκυρότητας</a>
                                <a class="dropdown-item" href="select.php">Δομή "ΕΠΙΛΕΞΕ"</a>
                                <a class="dropdown-item" href="index.php#moreAlgorithms">Αναζήτηση - Ταξινόμηση</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardropData" data-bs-toggle="dropdown">
                                Δομές Δεδομένων
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="index.php#1d">Πίνακες Μονοδιάστατοι</a>
                                <a class="dropdown-item" href="index.php#2d">Πίνακες 2 διαστάσεων</a>
                                <a class="dropdown-item" href="domesDedomenon.php">Στοίβα</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardrop2025" data-bs-toggle="dropdown">
                                Πανελλήνιες 2026
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="instructions.php">Οδηγίες</a>
                                <a class="dropdown-item" href="epanalipsi.php">Ασκήσεις Επανάληψης</a>
                                <a class="dropdown-item" href="askisi2025.php">Η τελευταία μας άσκηση</a>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="index.php#diagrammata">Διαγράμματα</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardropPanell" data-bs-toggle="dropdown">
                                Πανελλήνιες
                            </a>
                            <div class="dropdown-menu shadow">
                                <a class="dropdown-item" href="kena.php">Αλγόριθμοι (Κενά Α-Β)</a>
                                <a class="dropdown-item" href="c.php">Γ θέματα (2008-2022)</a>
                                <a class="dropdown-item" href="a_a.php">Σωστό - Λάθός</a>
                                <a class="dropdown-item" href="a_anaptyxis.php">Ερωτήσεις ανάπτυξης</a>
                                <a class="dropdown-item" href="index.php?action=listKenaDynamic">Συμπλήρωση κενών 2</a>
                                <a class="dropdown-item" href="index.php?action=showThemaGDForm">Θέματα Γ & Δ</a>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="index.php#theoria">Θεωρία</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbardropExercises" data-bs-toggle="dropdown">
                                Ασκήσεις
                            </a>
                            <div class="dropdown-menu shadow">
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

    public function displayMezeSuccess()
    {
        ?>
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-sm-12">
                        <div class="card shadow border-0 text-center" style="border-radius: 20px;">
                            <div class="card-body p-5">
                                <div class="mb-4">
                                    <i class="fa fa-check-circle text-success" style="font-size: 100px;"></i>
                                </div>
                                <h1 class="fw-bold">Έγινε!</h1>
                                <p class="lead text-muted">Η λύση σου στάλθηκε επιτυχώς στον δάσκαλο.</p>

                                <div class="alert alert-success bg-light border-success mt-4">
                                    <strong><i class="fa fa-info-circle"></i> Μπράβο!</strong>
                                    Τώρα μπορείς να κλείσεις αυτό το παράθυρο ή να επιστρέψεις στην αρχική.
                                </div>

                                <div class="mt-4">
                                    <a href="index.php" class="btn btn-primary btn-lg w-100 shadow d-grid">
                                        <i class="fa fa-home"></i> Επιστροφή στην Αρχική
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                body {
                    background: #f4f7f6;
                }

                .fa-check-circle {
                    animation: grow 0.6s ease-in-out;
                }

                @keyframes grow {
                    0% {
                        transform: scale(0);
                    }

                    80% {
                        transform: scale(1.2);
                    }

                    100% {
                        transform: scale(1);
                    }
                }
            </style>
        <?php
    }

    public function displayRequestSuccess()
    {
        ?>
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-sm-12">
                        <div class="card shadow border-0 text-center" style="border-radius: 20px; border-left: 8px solid #ffc107 !important;">
                            <div class="card-body p-5">
                                <div class="mb-4">
                                    <i class="fa fa-clock-o text-warning" style="font-size: 100px;"></i>
                                </div>
                                <h1 class="fw-bold">Το αίτημα εστάλη!</h1>
                                <p class="lead text-muted">Ειδοποιήσαμε τον δάσκαλο για την παράταση που ζήτησες.</p>

                                <div class="alert alert-warning bg-light border-warning mt-4 text-start">
                                    <strong><i class="fa fa-info-circle"></i> Τι γίνεται τώρα;</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Ο δάσκαλος θα δει το αίτημά σου σύντομα.</li>
                                        <li>Αν εγκριθεί, θα λάβεις email ειδοποίησης.</li>
                                        <li>Μόλις εγκριθεί, η φόρμα υποβολής θα ανοίξει αυτόματα.</li>
                                    </ul>
                                </div>

                                <div class="mt-4">
                                    <a href="index.php?action=showMyGrades" class="btn btn-warning btn-lg w-100 shadow fw-bold">
                                        <i class="fa fa-arrow-left"></i> Επιστροφή στις Βαθμολογίες
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }

    public function displayEndMatter()
    {
        ?>
            <footer>
                <div class="container">Αντώνης Χουβαρδάς 2021-26.

                </div>
            </footer>
            <!-- Script για την εμφάνιση/απόκρυψη κωδικού -->
            <script>
                function toggleMask(inputId, iconId) {
                    var input = document.getElementById(inputId);
                    var icon = document.getElementById(iconId);
                    if (input.classList.contains('mask-input')) {
                        input.classList.remove('mask-input');
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.classList.add('mask-input');
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }

                // Αυτόματη συλλογή των απαντήσεων από τα κενά (interactive blanks)
                document.addEventListener('DOMContentLoaded', function() {
                    // Φόρτωση αποθηκευμένης προόδου από το LocalStorage
                    var savedProgress = JSON.parse(localStorage.getItem('aepp_student_progress') || '{}');

                    document.querySelectorAll('form').forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            var card = form.closest('.meze-card');
                            if (card) {
                                var blanks = card.querySelectorAll('.aepp-interactive-blank');
                                if (blanks.length > 0) {
                                    var groupedAnswers = {};
                                    blanks.forEach(function(input) {
                                        var ex = input.getAttribute('data-ex') || '1';
                                        var num = input.getAttribute('data-blank') || '*';
                                        var val = input.value.trim();

                                        // 1. Κανονικοποίηση Unicode (NFC) για διόρθωση τόνων από iPhone/Samsung
                                        if (val.normalize) {
                                            val = val.normalize('NFC');
                                        }
                                        // 2. Μετατροπή Αγγλικών γραμμάτων που μοιάζουν οπτικά στα αντίστοιχα Ελληνικά
                                        var engToGr = {
                                            'A': 'Α',
                                            'B': 'Β',
                                            'E': 'Ε',
                                            'Z': 'Ζ',
                                            'H': 'Η',
                                            'I': 'Ι',
                                            'K': 'Κ',
                                            'M': 'Μ',
                                            'N': 'Ν',
                                            'O': 'Ο',
                                            'P': 'Ρ',
                                            'T': 'Τ',
                                            'X': 'Χ',
                                            'Y': 'Υ',
                                            'a': 'α',
                                            'e': 'ε',
                                            'i': 'ι',
                                            'o': 'ο',
                                            'p': 'ρ',
                                            'v': 'ν',
                                            'x': 'χ',
                                            'y': 'υ',
                                            'u': 'υ',
                                            'k': 'κ',
                                            't': 'τ',
                                            'n': 'ν'
                                        };
                                        val = val.replace(/[A-Za-z]/g, function(match) {
                                            return engToGr[match] || match;
                                        });
                                        // 3. Μετατροπή πολλαπλών κενών σε ένα
                                        val = val.replace(/\s+/g, ' ');

                                        if (val !== '') {
                                            if (!groupedAnswers[ex]) {
                                                groupedAnswers[ex] = [];
                                            }
                                            groupedAnswers[ex].push(num + ". " + val);
                                        }
                                    });
                                    var answersTxt = "";
                                    for (var ex in groupedAnswers) {
                                        answersTxt += "--- Αλγόριθμος " + ex + " ---\n";
                                        answersTxt += groupedAnswers[ex].join("\n") + "\n\n";
                                    }
                                    if (answersTxt !== "") {
                                        var hiddenInput = document.createElement('input');
                                        hiddenInput.type = 'hidden';
                                        hiddenInput.name = 'blanks_answers';
                                        hiddenInput.value = answersTxt.trim();
                                        form.appendChild(hiddenInput);
                                    }

                                    // Καθαρισμός προόδου από το LocalStorage μετά την υποβολή
                                    var currentProgress = JSON.parse(localStorage.getItem('aepp_student_progress') || '{}');
                                    blanks.forEach(function(input, index) {
                                        var formContext = form.id || form.getAttribute('action') || 'form';
                                        var key = 'blank_' + formContext + '_' + (input.getAttribute('data-ex') || '1') + '_' + (input.getAttribute('data-blank') || '*') + '_' + (input.name || 'noname') + '_' + index;
                                        delete currentProgress[key];
                                    });
                                    localStorage.setItem('aepp_student_progress', JSON.stringify(currentProgress));
                                }
                            }
                        });
                    });

                    // Ανάκτηση προόδου & δυναμική προσαρμογή του πλάτους των κενών
                    document.querySelectorAll('.aepp-interactive-blank').forEach(function(input, index) {
                        // Απενεργοποίηση της αυτόματης συμπλήρωσης (autofill) του browser 
                        // ώστε να μην προτείνονται οι παλιές υποβληθείσες απαντήσεις.
                        input.setAttribute('autocomplete', 'off');

                        var form = input.closest('form');
                        var formContext = form ? (form.id || form.getAttribute('action') || 'form') : 'noform';
                        var key = 'blank_' + formContext + '_' + (input.getAttribute('data-ex') || '1') + '_' + (input.getAttribute('data-blank') || '*') + '_' + (input.name || 'noname') + '_' + index;

                        // 1. Επαναφορά αποθηκευμένης τιμής αν υπάρχει
                        if (savedProgress[key] !== undefined) {
                            input.value = savedProgress[key];
                        }

                        function adjustWidth() {
                            // Ελάχιστο πλάτος 45px. ~9px ανά χαρακτήρα + 2 χαρακτήρες "αέρα"
                            var calculatedWidth = (input.value.length + 2) * 9;
                            input.style.width = Math.max(45, calculatedWidth) + 'px';
                        }
                        input.addEventListener('input', function() {
                            adjustWidth();
                            // 2. Αποθήκευση κατά την πληκτρολόγηση
                            var currentProgress = JSON.parse(localStorage.getItem('aepp_student_progress') || '{}');
                            currentProgress[key] = this.value;
                            localStorage.setItem('aepp_student_progress', JSON.stringify(currentProgress));
                        });
                        adjustWidth(); // Αρχική προσαρμογή για τυχόν προσυμπληρωμένα
                    });
                });
            </script>
            <!-- Bootstrap 5 JS Bundle για συμβατότητα με τα νέα Accordions -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                                    <span class="badge bg-warning text-dark ms-0 me-0"><?php echo $row['exerciseYear']; ?></span>
                                    <small class="fw-bold"><?php echo $row['examType']; ?></small>
                                </div>
                                <div class="card-body d-flex flex-column exercise-body">
                                    <div class="px-3 pt-2">
                                        <h6 class="card-title fw-bold text-primary mb-1">
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
                                        <div class="card-header p-0 border-0">
                                            <button class="btn btn-success w-100 btn-sm"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseLysi<?php echo $uniqueID; ?>">
                                                Εμφάνιση Λύσης
                                            </button>
                                        </div>
                                        <div id="collapseLysi<?php echo $uniqueID; ?>" class="collapse" data-bs-parent="#accordion<?php echo $uniqueID; ?>">
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

            $userYear = $db->getCurrentTutorYear();
            $studentsArray = $db->getTutorStudents($userYear);

            if (!$studentsArray) {
                $studentsArray = [];
            }

            echo '<div class="accordion" id="mezedakiaAccordion">';
            $isFirst = true; // Flag για να ανοίξουμε μόνο το πρώτο
            $mezeCount = 0; // Μετρητής για τα μεζεδάκια

            while ($row = $result->fetch_assoc()) {
                $mezeCount++;
                $hiddenClass = ($mezeCount > 10) ? 'd-none hidden-meze-item' : '';
                $mId = $row['mezeId'];
                $now = new DateTime();
                $solDate = new DateTime($row['solutionDate']);
                $isPastDeadline = ($now > $solDate);

                // 1. Έλεγχος για το ποιοι μαθητές έχουν παράταση
                $allowedStudents = [];
                $allowedNames = [];

                if ($isPastDeadline) {
                    foreach ($studentsArray as $st) {
                        if ($db->isSubmissionAllowed($st['studentId'], $mId, $userYear)) {
                            $allowedStudents[] = $st;
                        }
                    }
                } else {
                    $allowedStudents = $studentsArray;
                }

                $showButton = (!$isPastDeadline || !empty($allowedStudents));

                // Χρωματική σήμανση για το deadline
                $deadlineClass = $isPastDeadline ? 'bg-danger' : 'bg-success';
                $showClass = $isFirst ? 'show' : ''; // Το πρώτο είναι ανοιχτό
                $collapsedClass = $isFirst ? '' : 'collapsed'; // Το πρώτο δεν είναι collapsed
            ?>
                <div class="container-fluid p-0 mt-2 mb-2 <?php echo $hiddenClass; ?>">
                    <div class="card shadow-sm border-warning meze-card border-0">
                        <!-- Η κεφαλίδα λειτουργεί πλέον ως Toggle για το Accordion -->
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center meze-header <?php echo $collapsedClass; ?>"
                            style="cursor: pointer;"
                            data-bs-toggle="collapse"
                            data-bs-target="#mezeCollapse<?php echo $mId; ?>">
                            <div class="d-flex align-items-center">
                                <!-- Εικονίδιο ένδειξης accordion -->
                                <i class="fa fa-chevron-down me-3 accordion-icon" style="transition: transform 0.3s;"></i>
                                <h6 class="mb-0 me-3">
                                    <strong>Μεζεδάκι #<?php echo $row['mezeNumber']; ?></strong>
                                </h6>
                                <?php if (isset($row['isSos']) && $row['isSos'] == 1): ?>
                                    <span class="badge bg-danger pulse-sos shadow-sm"><i class="fa fa-fire"></i> SOS</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="badge <?php echo $deadlineClass; ?> py-2 px-3 shadow-sm border border-white">
                                    <i class="fa fa-clock-o"></i> <?php echo $db->formatGreekDate($row['solutionDate']) . " " . $solDate->format('H:i'); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Το σώμα του μεζεδακίου κρυμμένο αρχικά -->
                        <div id="mezeCollapse<?php echo $mId; ?>" class="collapse <?php echo $showClass; ?>" data-bs-parent="#mezedakiaAccordion">
                            <div class="card-body p-2">
                                <?php if (!empty($row['mezeImage'])): ?>
                                    <div class="text-center mb-3">
                                        <img src="images/mezedakia/<?php echo $row['mezeImage']; ?>" loading="lazy" class="img-fluid rounded border shadow-sm" style="max-width: 100%;">
                                    </div>
                                <?php endif; ?>

                                <div class="html-content-wrapper meze-text px-1">
                                    <?php echo str_replace('src="../images/', 'src="images/', $row['mezeText']); ?>
                                </div>

                                <?php if (!empty($row['mezeHints'])): ?>
                                    <div class="mt-3 px-1">
                                        <button class="btn btn-outline-info btn-sm w-100 shadow-sm font-weight-bold"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#hints<?php echo $mId; ?>"
                                            aria-expanded="false">
                                            <i class="fa fa-lightbulb-o"></i> Οδηγίες - Συμβουλές (Hints)
                                        </button>
                                        <div class="collapse mt-2 text-left" id="hints<?php echo $mId; ?>">
                                            <div class="alert alert-info border-info shadow-sm small mb-0">
                                                <strong><i class="fa fa-info-circle"></i> Οδηγίες / Υποδείξεις:</strong><br>
                                                <?php echo nl2br($row['mezeHints']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <hr>

                                <?php if ($showButton): ?>
                                    <div class="mt-2 text-center">
                                        <button class="btn btn-primary w-100 shadow-sm font-weight-bold"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#formMeze<?php echo $mId; ?>"
                                            aria-expanded="false">
                                            <i class="fa fa-paper-plane"></i> Υποβολή Λύσης
                                        </button>

                                        <div class="collapse mt-3 text-left" id="formMeze<?php echo $mId; ?>">
                                            <?php if ($isPastDeadline && !empty($allowedStudents)): ?>
                                                <div class="alert alert-danger py-2 mb-3 small shadow-sm border-danger">
                                                    <i class="fa fa-unlock-alt fa-lg mr-2"></i>
                                                    <strong>Ειδική Παράταση:</strong> Η υποβολή είναι ανοιχτή μόνο για όσους έχουν λάβει παράταση.
                                                </div>
                                            <?php endif; ?>

                                            <?php $fm->studentSubmissionForm($allowedStudents, $mId); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-secondary py-2 small text-center">
                                        <i class="fa fa-lock mr-1"></i> Η προθεσμία υποβολής έληξε.
                                    </div>
                                <?php endif; ?>

                                <?php
                                $hasSolution = (!empty(trim(strip_tags($row['mezeSolution'], '<img><iframe><br>'))) || !empty($row['mezeSolutionImage']));
                                $canShowSolution = $db->canShowMezeSolution($mId, $userYear);
                                if ($hasSolution && $canShowSolution): ?>
                                    <div id="accMeze<?php echo $mId; ?>" class="mt-3">
                                        <button class="btn btn-success w-100 btn-sm font-weight-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#sol<?php echo $mId; ?>">
                                            <i class="fa fa-key"></i> Εμφάνιση Λύσης
                                        </button>
                                        <div id="sol<?php echo $mId; ?>" class="collapse mt-2">
                                            <div class="p-2 bg-light border rounded shadow-sm text-left">
                                                <?php if (!empty($row['mezeSolutionImage'])): ?>
                                                    <div class="text-center mb-2">
                                                        <img src="images/mezedakia/<?php echo $row['mezeSolutionImage']; ?>" loading="lazy" class="img-fluid rounded border border-success">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="html-content-wrapper solution-text"><?php echo str_replace('src="../images/', 'src="images/', $row['mezeSolution']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
<?php
                $isFirst = false; // Μετά το πρώτο, όλα τα επόμενα θα είναι κλειστά
            }
            echo '</div>';

            // Αν υπάρχουν πάνω από 10 μεζεδάκια, εμφάνισε το κουμπί "Load More"
            if ($mezeCount > 10) {
                echo '
                <div class="text-center my-4" id="loadMoreMezeContainer">
                    <button class="btn btn-outline-primary btn-lg shadow-sm font-weight-bold px-4" onclick="loadMoreMezedakia()">
                        <i class="fa fa-arrow-down"></i> Εμφάνιση παλαιότερων (' . ($mezeCount - 10) . ')
                    </button>
                </div>
                <script>
                    function loadMoreMezedakia() {
                        document.querySelectorAll(".hidden-meze-item").forEach(function(el) {
                            el.classList.remove("d-none");
                        });
                        document.getElementById("loadMoreMezeContainer").style.display = "none";
                    }
                </script>
                ';
            }
        }
    }
}
