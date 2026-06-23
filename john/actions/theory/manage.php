<?php
switch ($action) {
        case 'save_theory':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $success = $db->insertTheoryItem(
                    $_POST['book_id'],
                    $_POST['chapter_num'],
                    $_POST['question_text'],
                    $_POST['answer_text'],
                    $_POST['page_number'] ?? 0,
                    $_FILES['q_file'] ?? null, // Προσθήκη αρχείου ερώτησης
                    $_FILES['a_file'] ?? null  // Προσθήκη αρχείου απάντησης
                );
                if ($success) {
                    echo "<div class='container mt-2'><div class='alert alert-success'>Η ερώτηση προστέθηκε επιτυχώς!</div></div>";
                }
            }
            // No break - για να ξαναδείξει τη φόρμα
            // No break - θέλουμε να ξαναδείξει τη φόρμα
        case 'add_theory':
            $books = $db->getTheoryBooks();
            $theoryFm->addTheoryForm($books);
            break;

        // Πρόσθεσε αυτά τα cases στο switch σου
        case 'manage_books':
            $books = $db->getTheoryBooks();
            $theoryFm->manageBooksForm($books);
            break;

        case 'save_book':
            if (isset($_POST['book_title'])) {
                $db->insertBook($_POST['book_title']);
                echo "<script>window.location.href='index.php?action=manage_books';</script>";
                exit();
            }
            break;

        case 'delete_book':
            if (isset($_GET['id'])) {
                $db->deleteBook($_GET['id']);
                echo "<script>window.location.href='index.php?action=manage_books';</script>";
                exit();
            }
            break;




        case 'edit_theory':
            if (isset($_GET['id'])) {
                $questionData = $db->getQuestionById($_GET['id']);
                $books = $db->getTheoryBooks();
                $theoryFm->editTheoryForm($questionData, $books);
            }
            break;

        case 'update_theory':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $db->updateTheoryItem(
                    $_POST['id'],
                    $_POST['book_id'],
                    $_POST['chapter_num'],
                    $_POST['question_text'],
                    $_POST['answer_text'],
                    $_POST['page_number'] ?? 0,
                    $_FILES['q_file'] ?? null, // Προσθήκη αρχείου ερώτησης
                    $_FILES['a_file'] ?? null  // Προσθήκη αρχείου απάντησης
                );
                echo "<script>window.location.href='index.php?action=list_theory';</script>";
                exit();
            }
            break;

        case 'delete_theory':
            if (isset($_GET['id'])) {
                $db->deleteTheoryQuestion($_GET['id']);
                echo "<script>window.location.href='index.php?action=list_theory';</script>";
                exit();
            }
            break;

        case 'list_theory':
            // 1. Παίρνουμε τα δεδομένα από την AdminDbHandler
            $questions = $db->getAllTheoryQuestions();
            // 2. Τα στέλνουμε στην AdminFormMaker για να φτιάξει τον πίνακα
            $theoryFm->listTheoryQuestions($questions);
            break;

        case 'list_for_test':
            $questions = $db->getAllQuestionsOrdered(); // Χρησιμοποιούμε την ήδη υπάρχουσα μέθοδο
            $theoryFm->listTheoryQuestionsForTests($questions);
            break;

        case 'create_exam':
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['selected_questions'])) {
                $selectedIds = $_POST['selected_questions'];
                $examQuestions = $db->getMultipleQuestionsByIds($selectedIds);
                $theoryFm->previewExam($examQuestions);
            } else {
                echo "<div class='container mt-5'><div class='alert alert-warning'>Δεν επιλέξατε ερωτήσεις!</div></div>";
            }
            break;
}
