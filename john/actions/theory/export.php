<?php
switch ($action) {
        case 'export_word_exam':
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['selected_questions'])) {
                $selectedIds = $_POST['selected_questions'];
                $examQuestions = $db->getMultipleQuestionsByIds($selectedIds);

                // Ρυθμίσεις Header για να αναγνωριστεί ως έγγραφο Word
                header("Content-Type: application/vnd.ms-word; charset=UTF-8");
                header("Content-Disposition: attachment;Filename=Diagonisma_AEPP.doc");
                header("Pragma: no-cache");
                header("Expires: 0");

                echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
                echo "<head><meta charset='utf-8'><title>Διαγώνισμα ΑΕΠΠ</title></head><body style='font-family: Arial, sans-serif;'>";
                echo "<h2 style='text-align: center; text-decoration: underline;'>ΔΙΑΓΩΝΙΣΜΑ ΑΕΠΠ</h2><br><br>";
                $i = 1;

                // Δημιουργία δυναμικού απόλυτου URL για τις εικόνες (απαραίτητο για το Word)
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $baseUploadUrl = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/uploads/';

                while ($row = $examQuestions->fetch_assoc()) {
                    echo "<div style='margin-bottom: 24px;'>";
                    echo "<strong style='font-size: 1.1em;'>Θέμα " . $i++ . ":</strong><br>";
                    echo "<div style='margin-top: 10px;'>" . $row['question_text'] . "</div>";
                    if (!empty($row['question_image'])) {
                        echo "<div style='margin-top: 15px; text-align: center;'><img src='" . $baseUploadUrl . $row['question_image'] . "' alt='Εικόνα Θέματος' style='max-width: 100%; height: auto;' /></div>";
                    }
                    echo "</div><br>";
                }
                echo "</body></html>";
                exit();
            }
            break;
}
