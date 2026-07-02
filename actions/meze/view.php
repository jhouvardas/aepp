<?php
$studentId = isset($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : null;
$result    = $db->getAllMezedakia($studentId);
$page->displayMezedakiaList($result, $studentId);
