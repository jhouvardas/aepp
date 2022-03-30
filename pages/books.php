<?php
include_once 'PageMaker.php';

$page = new PageMaker();
$page->displayHeadMatter();
$page->displayMenu();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2>Εξεταστέα ύλη 2020 - 2021</h2>
            <p>Αν η φετινή χρονιά είναι φυσιολογική το πιθανότερο είναι ότι στην ύλη 2021 - 2022
                θα υπάρχουν και αυτά που αφαιρέθηκαν.</p>
            <p>Είναι μόνο θεωρία και αρκετά ενοχλητική.</p>
            <ul>
                <li><a class="nav-item" target="_blank" href="resources/yli.pdf">Ύλη</a></li>
            </ul>        
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>Σχολικά βιβλία</h2>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="../resources/book.pdf">
                <img  src="../images/book.png" class="img-fluid" >
            </a>
        </div>
        <div class="col">
            <p>Σχολικό Βιβλίο</p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="resources/book2.pdf">
                <img  src="images/complementaryBookAepp.png" class="img-fluid">
            </a>
        </div>
        <div class="col">
            <p>Σχολικό Βιβλίο - Συμπληρωματικό Υλικό</p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="resources/odigies.pdf">
                <img  src="images/odigiesMeletis.png" class="img-fluid">
            </a>
        </div>
        <div class="col">
            <p>Σχολικό Βιβλίο - Οδηγίες Μελέτης</p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>Βοηθήματα</h2>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <!--<a href="resources/book.pdf">-->
            <img  src="images/kopsinis01.png" class="img-fluid" >
            <!--</a>-->
        </div>
        <div class="col">
            <p>Τεύχος 1</p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <!--<a href="resources/book2.pdf">-->
            <img  src="images/kopsinis02.png" class="img-fluid">
            <!--</a>-->
        </div>
        <div class="col">
            <p>Τεύχος 2</p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <!--<a href="resources/odigies.pdf">-->
            <img  src="images/kopsinis03.png" class="img-fluid">
            <!--</a>-->
        </div>
        <div class="col">
            <p>Τεύχος 3</p>
        </div>
    </div>
</div>
<?php
$page->displayEndMatter();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

