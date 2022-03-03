
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
        <div class="col-sm-8"></div>
    </div>
    <div class="row border border-dark">
        <div class="col-sm-4"><h1>Γνωριμία με το Plesk</h1> </div>
        <div class="col-sm-8"></div>
    <div class="row border border-dark">
        <div class="col-sm-4"><h1>Δημιουργία του πρώτου Project</h1></div>
        <div class="col-sm-8"></div>
    </div>
</div>
<?php
$page->displayEndMatter();
?>
