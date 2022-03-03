


<?php

//function __autoload($name) {
    include_once 'PageMaker.php';
//}

$page = new PageMaker();
$page->displayHeadMatter();
$page->displayMenu();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm">
            <h1>Η δομή πολλαπλής επιλογής ΕΠΙΛΕΞΕ</h1>
            <img src="images/switch.png" class="img-fluid" alt="Η δομή επιλογής ΕΠΙΛΕΞΕ">
            <p>Όπου:</p>
            <dl>
                <dt class="col-sm-3"><έκφραση></dt>
                <dd class="col-sm-9">είναι μια μεταβλητή, η τιμή της οποίας θα ελεγχθεί 
                    με τις τιμές που δίνονται στις ΠΕΡΙΠΤΩΣΕΙΣ και ανάλογα σε ποια ΠΕΡΙΠΤΩΣΗ 
                    ανήκει θα εκτελεστούν οι αντίστοιχες εντολές ή η πράξη, που υπολογίζει την τιμή της. 
                    <p>Δηλαδή η έκφραση μπορεί να είναι:</p>
                    <ul>
                        <li>Μεταβλητή</li>
                        <li>Αριθμητική Πράξη</li>
                        <li>Συγκριτική πράξη</li>
                    </ul>
                </dd>
                <dt class="col-sm-3"><λίστα_τιμών_Ν>:</dt>
                <dl class="col-sm-9">οι τιμές που μπορεί να πάρει μια έκφραση.
                    <p>Οι τιμές μπορεί να είναι:</p>
                    <ul>
                        <li>διακριτές τιμές</li>
                        <li>περιοχή τιμών από...έως</li>
                        <li>να υπακούουν σε μια συνθήκη</li>
                    </ul>
                </dl>
            </dl>
        </div>
    </div>

    <div class="row">
        <div class="col-sm">
            <h2><έκφραση> = μεταβλητή</h2>
        </div>
    </div>


    <div class="row">
        <div class="col-sm"> 
            <h3><λίστα_τιμών_1> Μια τιμή</h3>
            <pre>
ΠΡΟΓΡΑΜΜΑ επίλεξεΜεταβλητή
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: μέρα
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώστε ένα ακέραιο αριθμό από 1 μέχρι 7'
  ΔΙΑΒΑΣΕ μέρα
  ΕΠΙΛΕΞΕ μέρα
    ΠΕΡΙΠΤΩΣΗ 1
      ΓΡΑΨΕ 'Κυριακή'
    ΠΕΡΙΠΤΩΣΗ 2
      ΓΡΑΨΕ 'Δευτέρα'
    ΠΕΡΙΠΤΩΣΗ 3
      ΓΡΑΨΕ 'Τρίτη'
    ΠΕΡΙΠΤΩΣΗ 4
      ΓΡΑΨΕ 'Τετάρτη'
    ΠΕΡΙΠΤΩΣΗ 5
      ΓΡΑΨΕ 'Πέμπτη'
    ΠΕΡΙΠΤΩΣΗ 6
      ΓΡΑΨΕ 'Παρασκευή'
    ΠΕΡΙΠΤΩΣΗ 7
      ΓΡΑΨΕ 'Σάββατο'
    ΠΕΡΙΠΤΩΣΗ αλλιώς
      ΓΡΑΨΕ 'Λάθος αριθμός'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
            </pre>
        </div>
    </div>


    <div class="row">
        <div class="col-sm">  
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/23MpIYcBnLg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <h3><λίστα_τιμών_Ν> Διακριτές τιμές</h3>
            <pre>
 ΠΡΟΓΡΑΜΜΑ επίλεξεΜεταβλητή
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: μήνα
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώστε αριθμό μήνα για αριθμό ημερών'
  ΔΙΑΒΑΣΕ μήνα
  ΕΠΙΛΕΞΕ μήνα
    ΠΕΡΙΠΤΩΣΗ 1,3,5,7,8,10,12
      ΓΡΑΨΕ 'Ο',μήνα,'ος μήνας έχει 31 ημέρες'
    ΠΕΡΙΠΤΩΣΗ 4,6,8,11
      ΓΡΑΨΕ 'Ο',μήνα,'ος μήνας έχει 30 ημέρες'
    ΠΕΡΙΠΤΩΣΗ 2
      ΓΡΑΨΕ 'Ο',μήνα,'ος μήνας έχει 28 ημέρες και τα δίσεκτα έτη 29'
    ΠΕΡΙΠΤΩΣΗ αλλιώς
      ΓΡΑΨΕ 'Λάθος αριθμός'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
               
            </pre>
        </div>
    </div>
    <div class="row">
        <div class="col-sm embed-responsive embed-responsive-16by9"">            
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/zdbM923pow4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>

    <div class="row">
        <div class="col-sm">
            <h3><λίστα_τιμών_Ν> περιοχή τιμών από...έως</h3>
            <pre>
ΠΡΟΓΡΑΜΜΑ επίλεξεΜεταβλητή
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: κ
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώσε ακέραιο αριθμό'
  ΔΙΑΒΑΣΕ κ
  ΕΠΙΛΕΞΕ κ
    ΠΕΡΙΠΤΩΣΗ 0..9
      ΓΡΑΨΕ 'Μονοψήφιος'
    ΠΕΡΙΠΤΩΣΗ 10..99
      ΓΡΑΨΕ 'Διψήφιος'
    ΠΕΡΙΠΤΩΣΗ 100..999
      ΓΡΑΨΕ 'Τριψήφιος'
    ΠΕΡΙΠΤΩΣΗ ΑΛΛΙΩΣ
      ΓΡΑΨΕ 'Δεν μας νοιάζει'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ                
            </pre>
        </div>
    </div>
    <div class="row">
        <div class="col-sm embed-responsive embed-responsive-16by9">            
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/QRL6plGRoOA" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>           
        </div>
    </div>

    <div class="row">
        <div class="col-sm">
            <h3><λίστα_τιμών_Ν> υπακούν σε μια συνθήκη</h3>
            <pre>
ΠΡΟΓΡΑΜΜΑ επίλεξεΜεταβλητή
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: κ
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώσε κυβισμό αυτοκινήτου:.'
  ΔΙΑΒΑΣΕ κ
  ΕΠΙΛΕΞΕ κ
    ΠΕΡΙΠΤΩΣΗ <= 1000
      ΓΡΑΨΕ 'ΤΕΛΗ = 100€'
    ΠΕΡΙΠΤΩΣΗ <= 1299
      ΓΡΑΨΕ 'ΤΕΛΗ = 120€'
    ΠΕΡΙΠΤΩΣΗ <= 1800
      ΓΡΑΨΕ 'ΤΕΛΗ = 250€'
    ΠΕΡΙΠΤΩΣΗ ΑΛΛΙΩΣ
      ΓΡΑΨΕ 'ΤΕΛΗ = 600€'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ               
            </pre>
        </div>
    </div>
    <!--    <div class="row">
            <div class="col-sm embed-responsive embed-responsive-16by9">            
                   <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/sWyqJzhvaFc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>           
            </div>
        </div>-->\

    <div class="row">
        <div class="col-sm">
            <h2><έκφραση> = αριθμητική πράξη</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-sm">
            <pre>
ΠΡΟΓΡΑΜΜΑ επίλεξεΠράξη
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: χ
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώσε ένα αριθμό από το 1 μέχρι το 1000: '
  ΔΙΑΒΑΣΕ χ
  ΕΠΙΛΕΞΕ χ MOD 2
    ΠΕΡΙΠΤΩΣΗ 0
      ΓΡΑΨΕ 'Άρτιος'
    ΠΕΡΙΠΤΩΣΗ 1
      ΓΡΑΨΕ 'Περιττός'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
                
            </pre>
        </div>
    </div>
    <!--    <div class="row">
            <div class="col-sm embed-responsive embed-responsive-16by9">            
                   <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/sWyqJzhvaFc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>             
            </div>
        </div>-->
    <div class="row">
        <div class="col-sm">
            <h2><έκφραση> = λογική πράξη</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <pre>
ΠΡΟΓΡΑΜΜΑ επίλεξεΣυνθήκη
ΜΕΤΑΒΛΗΤΕΣ
  ΑΚΕΡΑΙΕΣ: χ
ΑΡΧΗ
  ΓΡΑΨΕ 'Δώσε ένα αριθμό από το 1 μέχρι το 1000: '
  ΔΙΑΒΑΣΕ χ
  ΕΠΙΛΕΞΕ χ MOD 2 = 0
    ΠΕΡΙΠΤΩΣΗ αληθής
      ΓΡΑΨΕ 'Άρτιος'
    ΠΕΡΙΠΤΩΣΗ ψευδής
      ΓΡΑΨΕ 'Περιττός'
  ΤΕΛΟΣ_ΕΠΙΛΟΓΩΝ
ΤΕΛΟΣ_ΠΡΟΓΡΑΜΜΑΤΟΣ
                
            </pre>
        </div>
    </div>
    <!--    <div class="row">
            <div class="col-sm embed-responsive embed-responsive-16by9">            
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/Lb9Z__eJDFo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> 
            </div>
        </div>-->
    <div class="row">
        <div class="col-sm">
            <pre>
                
            </pre>
        </div>
    </div>
</div>
<?php
$page->displayEndMatter();
