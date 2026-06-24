<?php
switch ($action) {
        case 'print_group':
            if (isset($_GET['group_id'])) {
                $groupId = $_GET['group_id'];
                $group = $db->getGroupById($groupId);
                $students = $db->getStudentsByGroupId($groupId, $userYear);
                $reportFm->printGroupStudents($group, $students);
            }
            break;

        case 'assign_tasks':
            $groups = $db->getGroups($userYear);
            $books = $db->getTheoryBooks();
            $exFm->assignTasksForm($groups, $db, $books);
            break;

        case 'list_all_tasks':
            $tasks = $db->getAllGroupTasks($userYear);
            $exFm->listAllTasks($tasks);
            break;

        case 'save_group_task':
            $db->saveGroupTask($_POST['group_id'], $_POST['task_text'], $_POST['book_id'] ?? null, $_FILES['task_file'] ?? null);
            echo "<script>window.location.href='index.php?action=assign_tasks';</script>";
            break;

}
