<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'TheoryMaker.php';
require_once 'john/AdminDbHandler.php';

$db = new AdminDbHandler();
$theory = new TheoryMaker();

// 1. Εύρεση ή δημιουργία του "Βιβλίο Μαθητή"
$book_title = "Βιβλίο Μαθητή";
$book_id = null;

$books = $db->getTheoryBooks();
if ($books) {
    while ($b = $books->fetch_assoc()) {
        if ($b['title'] === $book_title) {
            $book_id = $b['id'];
            break;
        }
    }
}

// Αν δεν βρέθηκε, το δημιουργούμε και ξαναπαίρνουμε το ID του
if (!$book_id) {
    $db->insertBook($book_title);
    $books = $db->getTheoryBooks();
    while ($b = $books->fetch_assoc()) {
        if ($b['title'] === $book_title) {
            $book_id = $b['id'];
            break;
        }
    }
}

if (!$book_id) {
    die("Σφάλμα: Δεν μπόρεσε να βρεθεί ή να δημιουργηθεί το 'Βιβλίο Μαθητή'.");
}

// 2. Αντιστοίχιση των μεθόδων στα κεφάλαια
$chapters = [
    'chapter01' => '1',
    'chapter02' => '2',
    'chapter03' => '3',
    'chapter06' => '6',
    'chapter07' => '7',
    'chapter08' => '8',
    'chapter09' => '9',
    'chapter10' => '10',
    'chapter13' => '13',
    'enotita01' => 'Ενότητα 1',
    'domi'      => 'Δομή'
];

echo "<!DOCTYPE html><html lang='el'><head><meta charset='UTF-8'><title>Εισαγωγή Θεωρίας</title></head><body style='font-family: Arial; padding: 20px;'>";
echo "<h2>Αυτόματη Εισαγωγή Ερωτήσεων Θεωρίας στη Βάση</h2>";
echo "<p>Βιβλίο: <b>$book_title</b> (ID: $book_id)</p>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>Κεφάλαιο</th><th>Αρχική Ερώτηση</th><th>Μορφοποιημένη Ερώτηση</th><th>Κατάσταση</th></tr>";

foreach ($chapters as $method => $chapter_num) {
    // Αποθηκεύουμε το HTML που παράγει η μέθοδος
    ob_start();
    $theory->$method();
    $html = ob_get_clean();

    // Parse HTML
    $dom = new DOMDocument();
    $html_encoded = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    @$dom->loadHTML($html_encoded);

    $xpath = new DOMXPath($dom);
    // Ψάχνουμε τα links που έχουν data-bs-target (αυτά είναι οι ερωτήσεις)
    $links = $xpath->query("//a[@data-bs-toggle='collapse']");

    foreach ($links as $link) {
        // Επιβεβαιώνουμε ότι ο κόμβος είναι στοιχείο (DOMElement) για να καλέσουμε την getAttribute
        if (!($link instanceof DOMElement)) {
            continue;
        }

        $raw_question = trim($link->nodeValue);
        $targetId = ltrim($link->getAttribute('data-bs-target'), '#');

        // --- 3. ΜΟΡΦΟΠΟΙΗΣΗ ΤΗΣ ΕΡΩΤΗΣΗΣ ---
        $question = $raw_question;
        $q_lower = mb_strtolower($question, 'UTF-8');

        // Ελέγχουμε αν είναι ήδη ερώτηση (ξεκινάει από συγκεκριμένες λέξεις)
        $question_words = ['τι ', 'ποια ', 'ποιο ', 'ποιες ', 'ποιοι ', 'πως ', 'πώς ', 'πότε ', 'γιατί ', 'αναφέρετε ', 'περιγράψτε ', 'από τι '];
        $is_question = false;

        foreach ($question_words as $qw) {
            if (mb_strpos($q_lower, $qw, 0, 'UTF-8') === 0) {
                $is_question = true;
                break;
            }
        }

        // Αν δεν είναι ερώτηση, προσθέτουμε το "Τι είναι "
        if (!$is_question) {
            $question = "Τι είναι " . $question;
        }

        // Εξασφαλίζουμε ότι καταλήγει σε ερωτηματικό (ελληνικό ;)
        if (mb_substr($question, -1, 1, 'UTF-8') !== ';') {
            $question .= ";";
        }

        // --- 4. ΕΥΡΕΣΗ ΤΗΣ ΑΠΑΝΤΗΣΗΣ ---
        $answerDivs = $xpath->query("//div[@id='$targetId']");
        if ($answerDivs->length > 0) {
            $answerDiv = $answerDivs->item(0);

            $answerHtml = '';
            foreach ($answerDiv->childNodes as $child) {
                $answerHtml .= $dom->saveHTML($child);
            }
            $answerHtml = trim($answerHtml);

            // --- 5. ΕΙΣΑΓΩΓΗ ΣΤΗ ΒΑΣΗ ---
            // book_id, chapter, question, answer, page (βάζουμε 0 προεπιλογή), q_image, a_image
            $success = $db->insertTheoryItem($book_id, $chapter_num, $question, $answerHtml, 0, null, null);

            $status = $success ? "<span style='color:green;'>Προστέθηκε</span>" : "<span style='color:red;'>Αποτυχία</span>";

            echo "<tr>
                    <td>$chapter_num</td>
                    <td>$raw_question</td>
                    <td>$question</td>
                    <td>$status</td>
                  </tr>";
        }
    }
}

echo "</table>";
echo "<h3 style='color: green; margin-top: 20px;'>Η εισαγωγή ολοκληρώθηκε επιτυχώς! Μπορείτε να διαγράψετε αυτό το αρχείο.</h3>";
echo "</body></html>";
