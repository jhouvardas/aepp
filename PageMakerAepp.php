<?php

class PageMakerAepp {

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
                                <a class="nav-link" href="#max">Μεγαλύτερος</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#akatalili">Ακατάλληλη τιμή</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#egirotita">Έλεγχος εγκυρότητας</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#1d">Πίνακες 1D</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#2d">Πίνακες 2D</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#diagrammata">Διαγράμματα</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#theoria">Θεωρία</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="askisisBoithima.html">Ασκήσεις που πρέπει να γίνουν από το Βοήθημα</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://markcalc.it.minedu.gov.gr/Home/Gel">Υπολογισμός μορίων</a>
                            </li>  
                        </ul>
                    </div>  
                </nav>
                <?php
            }

            public function displayEndMatter() {
                ?>
            </body>
        </html>

        <?php
    }

}
