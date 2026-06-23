<?php
switch ($action) {
        case 'manage_groups':
            $groups = $db->getGroups($userYear);
            $students = $db->getTutorStudents($userYear);
            $assignments = $db->getAssignedStudents();
            $reportFm->manageGroupsForm($groups, $students, $db, $assignments);
            break;

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

        case 'save_group':
            $db->createGroup($_POST['group_name'], $userYear);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'rename_group':
            if (isset($_POST['group_id']) && isset($_POST['new_group_name'])) {
                $db->renameGroup($_POST['group_id'], $_POST['new_group_name'], $userYear);
            }
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'add_student_to_group':
            $db->addStudentToGroup($_POST['student_id'], $_POST['group_id']);
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;

        case 'save_group_task':
            $db->saveGroupTask($_POST['group_id'], $_POST['task_text'], $_POST['book_id'] ?? null, $_FILES['task_file'] ?? null);
            echo "<script>window.location.href='index.php?action=assign_tasks';</script>";
            break;

        case 'remove_student_from_group':
            if (isset($_GET['student_id'])) {
                $db->removeStudentFromGroup($_GET['student_id']);
            }
            echo "<script>window.location.href='index.php?action=manage_groups';</script>";
            break;
}
