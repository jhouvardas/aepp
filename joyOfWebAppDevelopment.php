<?php
include_once 'PageMaker.php';
$page = new PageMaker();
$page->displayHeadMatter();
?>
<div class="container bg-light">
    <div class="row border border-dark">
        <div class="col-sm-4">
            <h1>Ετοιμασία του Netbeans</h1>
        </div>
        <div class="col-sm-8"><iframe width="560" height="315" src="https://www.youtube.com/embed/-Dq4dEVx4lk" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
    </div>
    <div class="row border border-dark">
        <div class="col-sm-4"><h1>Γνωριμία με το Plesk</h1>
            <p><a href="https://sso-01.sch.gr/login?service=http%3A%2F%2Fwebhost.sch.gr%2Flogin.php target="_blank">Plesk login form</a> </p>
            <p>username και password σου ήρθαν με μήνυμα στο κινητό</p>
        </div>
        <div class="col-sm-8"><iframe width="560" height="315" src="https://www.youtube.com/embed/xBzwX0GwF3I" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
    </div>
    <div class="row border border-dark">
        <div class="col-sm-4"><h1>Δημιουργία του πρώτου Project</h1>
            <p>Σύνδεση με τον server και ανέβασμα της πρώτης μας σελίδας</p>
            <p>Τελικά δεν χρειάζεται να δημιουργήσεις φάκελο bikesharing με το plesk στο httpdocs τον φτιάχνει το netbeans μόνο του</p>
        </div>
        <div class="col-sm-8"><iframe width="560" height="315" src="https://www.youtube.com/embed/AjvHX-vowXg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
    </div>
</div>
<?php
$page->displayEndMatter();
?>