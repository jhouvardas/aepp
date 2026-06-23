<?php
$result    = $db->getAllMezedakia();
$studentId = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;
$page->displayMezedakiaList($result, $studentId);
