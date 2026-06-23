<?php
$exercises = $db->getAllKenaExercises();
$page->displayKenaGallery($exercises);
