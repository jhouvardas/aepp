

<?php

function __autoload($name) {
    include_once $name . '.php';
}

$page = new PageMaker();
$form = new FormMaker();
$handler = new FileHandler();
$page->displayHeadMatter();
$page->displayMenu();
$db= new DbHandler();
?>
<div class="container-fluid">  
    <?php
    $form->addPanelliniesForm();
    if(isset($_POST['submit'])){
        $handler->upload();
        $db->addThema();
    }
   ?>
</div>
<?php
$page->displayEndMatter();
