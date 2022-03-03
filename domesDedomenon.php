


<?php

//function __autoload($name) {
    include_once 'PageMaker.php';
//}

$page = new PageMaker();
$page->displayHeadMatter();
$page->displayMenu();
?>
<div class="container-fluid">   
    <h2>Στοίβα</h2>
  <p>   YouTube video για στοίβα ώθηση και απώθηση</p>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link" href="https://youtu.be/OYTYZDyEclw">Στοίβα Ώθηση</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="https://youtu.be/O9Wmhyk_rTg">Στοίβα Απώθηση</a>
    </li>    
  </ul>
</div>
<?php
$page->displayEndMatter();
