<?php

class PageMaker {

    public function displayHeadMatter() {
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
            </head>
            <body>
                <?php
            }

            public function displayMenu() {
                ?>
                <nav class="navbar navbar-expand-md bg-dark navbar-dark">
                    <a class="navbar-brand" href="#" id="menu">ΑΕΠΠ</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="collapsibleNavbar">
                        <ul class="navbar-nav">
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

                            <li class="nav-item">
                                <a class="nav-link" href="index.php#diagrammata">Διαγράμματα</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php#theoria">Θεωρία</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="select.php">Επίλεξε</a>
                            </li>                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle"  id="navbardrop" data-toggle="dropdown">
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

            public function displayEndMatter() {
                ?>
            </body>
        </html>
        <?php
    }

    public function displayLinks() {
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
                    <a class="nav-link" href="https://aepp.edu.gr/palaiotera-themata/imerisia-lykeia/" target="_blank">Ημερήσια Λύκεια</a>
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

    public function megaliteros() {
        ?>

        <!--<div class="container-fluid">-->
        <div class="row justify-content-start">
            <div class="col-sm">
                <h4 id ="max">Μεγαλύτερος - Μικρότερος</h4>
                <p>Για να βρούμε τον μεγαλύτερο (ή τον μικρότερο) από δύο η περισσότερους αριθμούς
                    βάζουμε τον πρώτο μέσα στο max (ή στο min) και συγκρίνουμε το max (ή το min) με όλους τους άλλους</p> 
                <p><b>ΠΡΟΣΟΧΗ: Χρησιμοποιούμε μόνο ΔΟΜΗ ΑΠΛΗΣ ΕΠΙΛΟΓΗΣ</b></p>
                <p>Σε περίπτωση που γνωρίζουμε το διάστημα των τιμών μπορούμε να βάλουμε
                    στον max την μικρότερη πιθανή τιμή -1 και στο min την μεγαλύτερη πιθανή τιμή +1 
                    (πχ αν οι τιμές είναι από 1 έως 20 τότε βάζουμε στο max το 0 και στο min το 21)</p>
                <h5>Μεγαλύτερος από 3</h5>
                <pre>
                ΔΙΑΒΑΣΕ α,β,γ                            
                μεγ <-- α
                ΑΝ β > μεγ ΤΟΤΕ
                    μεγ <-- β
                ΤΕΛΟΣ_ΑΝ
                ΑΝ γ > μεγ ΤΟΤΕ
                    μεγ <-- γ
                ΤΕΛΟΣ_ΑΝ
                </pre>
                <h5>Άγνωστο διάστημα τιμών</h5>
                <pre>
                ΔΙΑΒΑΣΕ χ
                max <-- χ
                ΓΙΑ i ΑΠΟ 2 ΜΕΧΡΙ 30
                    ΔΙΑΒΑΣΕ χ
                    ΑΝ (χ > max) ΤΟΤΕ
                        max <-- χ
                    ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                </pre>
                <pre>
                ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
                    ΔΙΑΒΑΣΕ χ
                    ΑΝ (i = 1) ΤΟΤΕ
                        max <-- χ
                    ΤΕΛΟΣ_ΑΝ
                    ΑΝ (χ > max) ΤΟΤΕ
                        max <-- χ
                    ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                </pre>
                <p> Η καλύτερα</p>
                <pre>
                ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
                    ΔΙΑΒΑΣΕ χ
                    ΑΝ (i = 1) ΤΟΤΕ
                        max <-- χ
                    ΑΛΛΙΩΣ
                        ΑΝ (χ > max) ΤΟΤΕ
                            max <-- χ
                        ΤΕΛΟΣ_ΑΝ
                    ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                </pre>
                <h5>Τιμές [1,20]</h5>
                <pre>
                max <-- 0
                ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
                    ΔΙΑΒΑΣΕ χ
                    ΑΝ (χ > max) ΤΟΤΕ
                        max <-- χ
                    ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ                            
                </pre>

                <h5> Μεγαλύτερος άρτιος από 30 ακεραίους</h5>
                <pre>
                πρώτος <-- ΑΛΗΘΗΣ
                ΓΙΑ i ΑΠΟ 1 ΜΕΧΡΙ 30
                    ΔΙΑΒΑΣΕ χ
                    ΑΝ χ mod 2 = 0  ΤΟΤΕ
                        ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
                            max <-- χ
                            πρώτος <-- ΨΕΥΔΗΣ
                        ΑΛΛΙΩΣ_ΑΝ χ > max ΤΟΤΕ
                            max <-- χ
                        ΤΕΛΟΣ_ΑΝ
                    ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
                ΑΝ πρώτος = ΨΕΥΔΗΣ ΤΟΤΕ
                    ΓΡΑΨΕ 'Μεγαλύτερος άρτιος ο',max
                ΑΛΛΙΩΣ
                    ΓΡΑΨΕ 'Δεν δώθηκε κανένας άρτιος'
                ΤΕΛΟΣ_ΑΝ                           
                </pre>

                <h5> Μεγαλύτερος άρτιος μέχρι να δωθεί το 0</h5>
                <pre>
                πρώτος <-- ΑΛΗΘΗΣ
                ΔΙΑΒΑΣΕ χ
                ΟΣΟ χ <> 0 ΕΠΑΝΑΛΑΒΕ    
                    ΑΝ χ mod 2 = 0  ΤΟΤΕ
                        ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
                            max <-- χ
                            πρώτος <-- ΨΕΥΔΗΣ
                        ΑΛΛΙΩΣ_ΑΝ χ > max ΤΟΤΕ
                            max <-- χ
                        ΤΕΛΟΣ_ΑΝ
                    ΤΕΛΟΣ_ΑΝ
                    ΔΙΑΒΑΣΕ χ
                ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ 
                ΑΝ πρώτος = ΨΕΥΔΗΣ ΤΟΤΕ
                    ΓΡΑΨΕ 'Μεγαλύτερος άρτιος ο',max
                ΑΛΛΙΩΣ
                    ΓΡΑΨΕ 'Δεν δώθηκε κανένας άρτιος'
                ΤΕΛΟΣ_ΑΝ                           
                </pre>

                <h5>Δύο μεγαλύτεροι από άγνωστο αριθμό <b>θετικών</b> ακεραίων</h5>
                <pre>
                ΠΡΟΓΡΑΜΜΑ μεγαλύτεροι
                ΜΕΤΑΒΛΗΤΕΣ
                  ΑΚΕΡΑΙΕΣ: χ, μαξμαξ, μαξ
                ΑΡΧΗ
                  μαξμαξ <- -1
                  μαξ <- -1
                  ΓΡΑΨΕ 'Δώστε ακέραιο αριθμό'
                  ΔΙΑΒΑΣΕ χ
                  ΟΣΟ χ <> -1 ΕΠΑΝΑΛΑΒΕ
                    ΑΝ χ > μαξμαξ ΤΟΤΕ
                      μαξ <- μαξμαξ
                      μαξμαξ <- χ
                    ΑΛΛΙΩΣ_ΑΝ χ > μαξ ΚΑΙ χ <> μαξμαξ ΤΟΤΕ
                      μαξ <- χ
                    ΤΕΛΟΣ_ΑΝ
                    ΓΡΑΨΕ 'Δώστε ακέραιο αριθμό'
                    ΔΙΑΒΑΣΕ χ
                  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                  ΑΝ μαξμαξ <> -1 ΚΑΙ μαξ <> -1 ΤΟΤΕ
                    ΓΡΑΨΕ 'Μεγαλύτερος είναι ο ', μαξμαξ
                    ΓΡΑΨΕ 'Επόμενος είναι ο ', μαξ
                  ΑΛΛΙΩΣ_ΑΝ μαξ = -1 ΤΟΤΕ
                    ΓΡΑΨΕ 'Δώθηκε μόνο ένας αριθμός ο ', μαξ
                  ΑΛΛΙΩΣ
                    ΓΡΑΨΕ 'Δεν δώθηκε κανένας έγκυρος αριθμός'
                  ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ                         </pre>

                <h5>Δύο μεγαλύτεροι από άγνωστο αριθμό ακεραίων και <b>άγνωστο διάστημα τιμών</b></h5>
                <pre>
                ΠΡΟΓΡΑΜΜΑ δύοΜεγαλύτεροι
                ΜΕΤΑΒΛΗΤΕΣ
                  ΑΚΕΡΑΙΕΣ: χ, μαξ, μαξμαξ
                  ΛΟΓΙΚΕΣ: πρώτος, δεύτερος
                ΑΡΧΗ
                  μαξ <- -1
                  μαξμαξ <- -1
                  πρώτος <- ΑΛΗΘΗΣ
                  δεύτερος <- ΨΕΥΔΗΣ
                  ΓΡΑΨΕ 'Δώστε ακέραιο η -1 για τέλος'
                  ΔΙΑΒΑΣΕ χ
                  ΟΣΟ χ <> -1 ΕΠΑΝΑΛΑΒΕ
                    ΑΝ πρώτος = ΑΛΗΘΗΣ ΤΟΤΕ
                      μαξμαξ <- χ
                      πρώτος <- ΨΕΥΔΗΣ
                      δεύτερος <- ΑΛΗΘΗΣ
                    ΑΛΛΙΩΣ_ΑΝ δεύτερος = ΑΛΗΘΗΣ ΤΟΤΕ
                      ΑΝ χ > μαξμαξ ΤΟΤΕ
                        μαξ <- μαξμαξ
                        μαξμαξ <- χ
                      ΑΛΛΙΩΣ
                        μαξ <- χ
                      ΤΕΛΟΣ_ΑΝ
                      δεύτερος <- ΨΕΥΔΗΣ
                    ΑΛΛΙΩΣ
                      ΑΝ χ > μαξμαξ ΤΟΤΕ
                        μαξ <- μαξμαξ
                        μαξμαξ <- χ
                      ΑΛΛΙΩΣ_ΑΝ χ > μαξ ΤΟΤΕ
                        μαξ <- χ
                      ΤΕΛΟΣ_ΑΝ
                    ΤΕΛΟΣ_ΑΝ
                    ΓΡΑΨΕ 'Δώστε ακέραιο η -1 για τέλος'
                    ΔΙΑΒΑΣΕ χ
                  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                  ΑΝ δεύτερος = ΑΛΗΘΗΣ ΤΟΤΕ
                    ΓΡΑΨΕ 'δώθηκε μόνο ένας αριθμός ο ', μαξμαξ
                  ΑΛΛΙΩΣ
                    ΓΡΑΨΕ 'μεγαλύτερος ο ', μαξμαξ
                    ΓΡΑΨΕ 'επόμενος ο ', μαξ
                  ΤΕΛΟΣ_ΑΝ
                ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ 
                </pre>
                <h5>Πόσοι μαθητές είχαν τον μεγαλύτερο βαθμό</h5>
                <pre>
                ΠΡΟΓΡΑΜΜΑ πόσεςΦορέςΟΜεγαλύτεροςΒαθμός
                ΣΤΑΘΕΡΕΣ
                  ν = 5
                ΜΕΤΑΒΛΗΤΕΣ
                  ΑΚΕΡΑΙΕΣ: βαθ, μαξ, πλήθος, ι
                ΑΡΧΗ
                  μαξ <- 0
                  ΓΙΑ ι ΑΠΟ 1 ΜΕΧΡΙ ν
                    ΓΡΑΨΕ 'Δώστε βαθμό'
                    ΔΙΑΒΑΣΕ βαθ
                    ΑΝ βαθ > μαξ ΤΟΤΕ
                      μαξ <- βαθ
                      πλήθος <- 1
                    ΑΛΛΙΩΣ_ΑΝ βαθ = μαξ ΤΟΤΕ
                      πλήθος <- πλήθος + 1
                    ΤΕΛΟΣ_ΑΝ
                  ΤΕΛΟΣ_ΕΠΑΝΑΛΗΨΗΣ
                  ΓΡΑΨΕ 'Τον μεγαλύτερο βαθμό είχαν ', πλήθος, ' μαθητές'
                ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ 
                </pre>
                <a href="#menu" class="btn bg-dark btn-block" role="button">Μενού</a>
            </div>

        </div>  
        <!--</div>-->
        <?php
    }

}
