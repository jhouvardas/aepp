<?php
include_once 'PageMaker.php';

$page = new PageMaker();
$page->displayHeadMatter();
$page->displayMenu();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <h3 id="askisisBoithima">Ασκήσεις για μία γρήγορη επανάληψη</h3>
            <p>Πριν από κάθε ομάδα ασκήσεων στο βοήθημα υπάρχουν λυμένες ασκήσεις. Τις κοιτάζουμε με προσοχή.</p>
            <h4>Τεύχος 1</h4>
            <ul>
                <li> 8.35</li>
                <li> 8.41</li>
                <li>11.29</li>
                <li>12.20</li>
                <li>12.25</li>
                <li>14.22</li>
                <li>14.28</li>
                <li>14.36</li>
                <li>15.18</li>
                <li>16.15</li>
                <li>16.22</li>
                <li>17.10</li>
                <li>17.25</li>                
            </ul>                   
        </div>
        <div class="col-lg-6">
            <h4>Τεύχος 2</h4>
            <ul>
                <li> 1.48</li>
                <li> 1.55</li>
                <li> 2.35</li>
                <li> 3.38</li>
                <li> 3.44</li>
                <li> 4.24</li>
                <li> 4.25</li>
                <li> 4.36</li>
                <li> 4.37</li>
                <li> 5.26</li>
                <li> 6.31</li>
                <li> 8.24</li>
                <li>14.28</li>
                <li>15.34</li>
                <li>17.24</li>
            </ul>
        </div>
    </div>  
</div>
<?php
$page->displayEndMatter();
