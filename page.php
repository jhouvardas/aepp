<?php
function __autoload($name) {
    include_once $name . '.php';
}

$page = new PageMaker();
$page->displayHeadMatter();
$page->displayMenu();
$page->displayLinks();
$page->displayEndMatter();
?>