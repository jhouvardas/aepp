<?php
include_once 'AdminFormMaker.php';

class MezeAdminFormMaker extends AdminFormMaker
{
    /**
     * @param string $str
     * @return string
     */
    private function normalizeString($str)
    {
        $str = (string)$str;
        // Πολλαπλή αποκωδικοποίηση για να καθαρίσει τυχόν διπλές κωδικοποιήσεις (π.χ. &amp;gt;)
        for ($i = 0; $i < 3; $i++) {
            $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $str = preg_replace('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/i', '', $str);
        $str = preg_replace('/^(?:\(\d+\)|\d+[\.\)])\s*/u', '', trim($str));

        // Αντικατάσταση κόμματος με τελεία (για δεκαδικούς αριθμούς π.χ. 2,5 -> 2.5)
        $str = str_replace(',', '.', $str);

        // ΑΠΟΛΥΤΟΣ ΚΑΘΑΡΙΣΜΟΣ: Κρατάμε ΜΟΝΟ γράμματα, αριθμούς, σημεία στίξης και σύμβολα.
        // Αφαιρεί ΑΥΤΟΜΑΤΑ όλα τα κενά (spaces) και όλους τους κρυφούς χαρακτήρες.
        $clean = preg_replace('/[^\p{L}\p{N}\p{P}\p{S}]/u', '', $str);
        if ($clean !== null) {
            $str = $clean;
        } else {
            // Εναλλακτική σε περίπτωση που η έκδοση PHP δεν υποστηρίζει τα παραπάνω flag
            $str = preg_replace('/[\s]+/u', '', $str);
            $str = preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200F}\x{FEFF}]/u', '', $str);
        }

        // Ομαλοποίηση συγκριτικών και εκχωρητικών τελεστών (εφαρμόζεται αφού έχουν αφαιρεθεί τα κενά, π.χ. το "= <" έγινε "=<")
        $str = str_replace(['=<', '≤'], '<=', $str);
        $str = str_replace(['=>', '≥'], '>=', $str);
        $str = str_replace('≠', '<>', $str);
        $str = str_replace('←', '<-', $str);

        // Ομαλοποίηση μαθηματικών τελεστών (από κινητά, Word ή άλλες γλώσσες προγραμματισμού)
        $str = str_replace(['×', '·', '•', '∗'], '*', $str); // Εναλλακτικά σύμβολα πολλαπλασιασμού
        $str = str_replace('÷', '/', $str);                  // Εναλλακτικό σύμβολο διαίρεσης
        $str = str_replace('−', '-', $str);                  // Ειδικό μαθηματικό σύμβολο μείον (U+2212)
        $str = str_replace(['ˆ', '**'], '^', $str);          // Εναλλακτικά σύμβολα δύναμης
        $str = str_replace('!=', '<>', $str);                // Διάφορο (C/Python style)
        $str = str_replace('==', '=', $str);                 // Ίσον (C/Python style)

        $str = mb_strtolower(trim($str), 'UTF-8');

        $homoglyphs = [
            'ά' => 'α',
            'έ' => 'ε',
            'ή' => 'η',
            'ί' => 'ι',
            'ό' => 'ο',
            'ύ' => 'υ',
            'ώ' => 'ω',
            'ϊ' => 'ι',
            'ϋ' => 'υ',
            'ΰ' => 'υ',
            'ς' => 'σ',
            'a' => 'α',
            'b' => 'β',
            'e' => 'ε',
            'z' => 'ζ',
            'h' => 'η',
            'i' => 'ι',
            'k' => 'κ',
            'm' => 'μ',
            'n' => 'ν',
            'o' => 'ο',
            'p' => 'ρ',
            't' => 'τ',
            'x' => 'χ',
            'y' => 'υ',
            'u' => 'υ',
            'v' => 'ν'
        ];

        return strtr($str, $homoglyphs);
    }

    /**
     * @param string $studentText
     * @param string $solutionHtml
     * @return array
     */
    private function getHighlightedStudentAnswer($studentText, $solutionHtml)
    {
        // 1. Extract correct answers from solution HTML
        $correctAnswers = [];
        $totalExpected = 0;
        if (!empty($solutionHtml)) {
            // Προστασία τελεστών για να μην τους "καταπιεί" το DOMDocument θεωρώντας το < ως αρχή HTML tag
            $solutionHtml = str_replace(['<=', '<>', '< ', '=>', ' >', '=<', '<-'], ['&lt;=', '&lt;&gt;', '&lt; ', '=&gt;', ' &gt;', '=&lt;', '&lt;-'], $solutionHtml);

            $dom = new DOMDocument();
            // Use error suppression and mb_convert_encoding for robustness with HTML fragments
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $solutionHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $xpath = new DOMXPath($dom);

            $grids = $xpath->query("//div[contains(@class, 'answer-grid')]");

            if ($grids->length > 0) {
                $algoCounter = 1;
                foreach ($grids as $grid) {
                    $algoNum = (string)$algoCounter;

                    // Βρίσκουμε το αμέσως προηγούμενο group-header για να πάρουμε το νούμερο του Αλγορίθμου
                    $header = $xpath->query("preceding::div[contains(@class, 'group-header')][1]", $grid)->item(0);
                    if ($header && preg_match('/(\d+)/', $header->nodeValue, $matches)) {
                        $algoNum = $matches[1];
                    }

                    $items = $xpath->query(".//div[contains(@class, 'answer-item')]", $grid);
                    $ansCounter = 1;
                    foreach ($items as $item) {
                        // Παίρνουμε όλο το κείμενο του κελιού για να μην χάσουμε σύμβολα (π.χ. >=) 
                        // αν ο καθηγητής εφάρμοσε bold/χρώμα μόνο σε ένα μέρος της απάντησης.
                        $textVal = $item->nodeValue;
                        $textVal = preg_replace('/^(?:\(\d+\)|\d+[\.\)])\s*/u', '', trim($textVal));
                        $correctAnswers[$algoNum][$ansCounter] = trim($textVal);
                        $ansCounter++;
                        $totalExpected++;
                    }
                    $algoCounter++;
                }
            } else {
                // Fallback για παλιά μορφή HTML
                $headers = $xpath->query("//h5[contains(text(), 'Αλγόριθμος')] | //div[contains(@style, 'background-color')]//h5");
                if ($headers->length > 0) {
                    foreach ($headers as $header) {
                        preg_match('/(\d+)/u', $header->nodeValue, $matches);
                        $algoNum = $matches[1] ?? '1';

                        $ansCounter = 1;
                        $parent = $header->parentNode;

                        $ol = $xpath->query(".//ol", $parent)->item(0);
                        if (!$ol) {
                            $ol = $xpath->query("following-sibling::ol[1]", $header)->item(0);
                        }

                        if ($ol) {
                            $lis = $xpath->query(".//li", $ol);
                            foreach ($lis as $li) {
                                // Παίρνουμε όλο το κείμενο του <li> για να μην χάνονται σύμβολα
                                $textVal = $li->nodeValue;
                                $textVal = preg_replace('/^(?:\(\d+\)|\d+[\.\)])\s*/u', '', trim($textVal));
                                $correctAnswers[$algoNum][$ansCounter] = trim($textVal);
                                $ansCounter++;
                                $totalExpected++;
                            }
                        }
                    }
                } else {
                    $ol = $xpath->query("//ol")->item(0);
                    if ($ol) {
                        $lis = $xpath->query(".//li", $ol);
                        $ansCounter = 1;
                        foreach ($lis as $li) {
                            // Παίρνουμε όλο το κείμενο του <li> για να μην χάνονται σύμβολα
                            $textVal = $li->nodeValue;
                            $correctAnswers['1'][$ansCounter] = trim($textVal);
                            $ansCounter++;
                            $totalExpected++;
                        }
                    }
                }
            }
        }

        $isOldFormat = (strpos($studentText, '(*) ➔') !== false);
        $isAutoGradable = ($totalExpected > 0 && !$isOldFormat);
        $totalCorrect = 0;
        $highlightedStudentText = '';

        // 2. Parse student's answers and compare
        $studentAnswers = [];
        $mainComment = '';
        $answersHtml = '';

        $answersPartPos = stripos($studentText, "<div style='background:#f8f9fa;");
        if ($answersPartPos === false) {
            $answersPartPos = stripos($studentText, "Απαντήσεις στα κενά:");
        }
        if ($answersPartPos === false) {
            $answersPartPos = stripos($studentText, "--- Αλγόριθμος");
        }

        if ($answersPartPos !== false) {
            $mainComment = substr($studentText, 0, $answersPartPos);
            if (substr($mainComment, -8) === "<br><br>") {
                $mainComment = substr($mainComment, 0, -8);
            }

            $answersHtml = substr($studentText, $answersPartPos);

            // ΜΑΓΙΚΗ ΛΥΣΗ: Μετατροπή των <br> σε πραγματικές αλλαγές γραμμής ΠΡΙΝ διαγραφούν τα HTML tags!
            $answersHtml = preg_replace('/<br\s*\/?>/i', "\n", $answersHtml);

            // Προστασία μαθηματικών/συγκριτικών τελεστών που ίσως πληκτρολόγησε ο μαθητής 
            // ώστε να μην διαγραφούν από το strip_tags (π.χ. το <= θεωρείται λάθος HTML tag).
            $answersHtml = str_replace(['<=', '<>', '< ', '=>', ' >', '=<'], ['&lt;=', '&lt;&gt;', '&lt; ', '=&gt;', ' &gt;', '=&lt;'], $answersHtml);

            $answersOnlyText = trim(str_ireplace("Απαντήσεις στα κενά:", "", strip_tags($answersHtml)));
            $answersOnlyText = html_entity_decode($answersOnlyText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($isAutoGradable) {
                // Χρήση preg_match_all αντί για preg_split για 100% ασφαλή απομόνωση των απαντήσεων
                if (preg_match_all('/---\s*Αλγόριθμος\s*(\d+)\s*---(.*?)(?=(?:---\s*Αλγόριθμος|$))/su', $answersOnlyText, $algoMatches, PREG_SET_ORDER)) {
                    foreach ($algoMatches as $match) {
                        $algoNum = trim($match[1]);
                        $lines = explode("\n", trim($match[2]));
                        foreach ($lines as $line) {
                            if (preg_match('/^(\d+)\.\s*(.*)$/u', trim($line), $matches)) {
                                $studentAnswers[$algoNum][(int)$matches[1]] = trim($matches[2]);
                            }
                        }
                    }
                } else {
                    // Fallback αν λείπει το tag --- Αλγόριθμος X --- (όλα πάνε στον αλγόριθμο 1)
                    $lines = explode("\n", trim($answersOnlyText));
                    foreach ($lines as $line) {
                        if (preg_match('/^(\d+)\.\s*(.*)$/u', trim($line), $matches)) {
                            $studentAnswers['1'][(int)$matches[1]] = trim($matches[2]);
                        }
                    }
                }
            }
        } else {
            $mainComment = $studentText;
        }

        if (trim($mainComment) !== '') {
            $highlightedStudentText .= nl2br(htmlspecialchars(trim($mainComment))) . "<br><br>";
        } elseif (!$isAutoGradable && empty(trim($studentText))) {
            return ['html' => "<i>Χωρίς σχόλια.</i>", 'autoGrade' => ''];
        }

        if ($isAutoGradable) {
            $highlightedStudentText .= "<div style='background:#f8f9fa; padding:15px; border-radius:8px; border:1px solid #dee2e6; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);'>";
            $highlightedStudentText .= "<h6 class='fw-bold mb-3 text-secondary'><i class='fa fa-list-ol'></i> Αναλυτικές Απαντήσεις:</h6>";
            foreach ($correctAnswers as $algoNum => $blanks) {
                if (count($correctAnswers) > 1 || isset($studentAnswers[$algoNum])) {
                    $highlightedStudentText .= "<div class='fw-bold text-primary mt-3 mb-2 border-bottom pb-1'>Αλγόριθμος $algoNum</div>";
                }
                foreach ($blanks as $ansNum => $correctAnswer) {
                    $studentAns = isset($studentAnswers[$algoNum][$ansNum]) ? $studentAnswers[$algoNum][$ansNum] : '';
                    $pointValue = ($totalExpected > 0) ? (20 / $totalExpected) : 0;

                    $btnHtml = "<button type='button' class='btn btn-outline-success btn-sm shadow-sm px-2 py-1 ms-1 btn-mark-correct' onclick='markBlankCorrect(this, " . number_format($pointValue, 4, '.', '') . ")' title='Μαρκάρισμα ως σωστό (Παράκαμψη)'><i class='fa fa-check'></i></button>";
                    $btnHtml .= "<button type='button' class='btn btn-outline-danger btn-sm shadow-sm px-2 py-1 ms-1 btn-undo-correct d-none' onclick='undoBlankCorrect(this, " . number_format($pointValue, 4, '.', '') . ")' title='Αναίρεση (Επαναφορά)'><i class='fa fa-undo'></i></button>";

                    // Απόλυτη αποκωδικοποίηση (loop) για προστασία από πολλαπλές κωδικοποιήσεις (π.χ. αν αποθηκεύτηκε ως &amp;gt;)
                    $rawStudentAns = (string)$studentAns;
                    $rawCorrectAns = (string)$correctAnswer;
                    for ($i = 0; $i < 3; $i++) {
                        $rawStudentAns = html_entity_decode($rawStudentAns, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $rawCorrectAns = html_entity_decode($rawCorrectAns, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }

                    // Ασφαλής αφαίρεση τυχόν "ορφανών" HTML ετικετών (π.χ. </code>) για καθαρή εμφάνιση
                    $rawStudentAns = preg_replace('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/i', '', $rawStudentAns);
                    $rawCorrectAns = preg_replace('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/i', '', $rawCorrectAns);

                    // Αφαίρεση αρίθμησης (π.χ. "(2)") από την τελική εμφάνιση
                    $rawStudentAns = preg_replace('/^(?:\(\d+\)|\d+[\.\)])\s*/u', '', trim($rawStudentAns));
                    $rawCorrectAns = preg_replace('/^(?:\(\d+\)|\d+[\.\)])\s*/u', '', trim($rawCorrectAns));

                    $dispStudentAns = htmlspecialchars($rawStudentAns, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    // Υποστήριξη πολλαπλών σωστών απαντήσεων (διαχωρισμένες με |)
                    $correctAlts = array_map('trim', explode('|', $rawCorrectAns));
                    $dispCorrectAns = implode(' <span class="text-muted small">ή</span> ', array_map(
                        fn($p) => '<span>' . htmlspecialchars($p, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</span>',
                        $correctAlts
                    ));

                    $correctAltsRaw = array_map('trim', explode('|', $correctAnswer));
                    $isBlankCorrect = false;
                    foreach ($correctAltsRaw as $alt) {
                        if ($this->normalizeString($studentAns) === $this->normalizeString($alt)) {
                            $isBlankCorrect = true;
                            break;
                        }
                    }

                    if ($studentAns !== '') {
                        if ($isBlankCorrect) {
                            $totalCorrect++;
                            $highlightedStudentText .= "
                            <div class='d-flex align-items-stretch mb-2 p-2 bg-white border border-success rounded shadow-sm'>
                                <div class='d-flex align-items-center'>
                                    <span class='badge bg-success rounded-pill me-3 px-3 py-2'><i class='fa fa-check'></i> $ansNum</span>
                                </div>
                                <div class='d-flex flex-grow-1 align-items-center border-start ps-3'>
                                    <div>
                                        <span class='text-success small text-uppercase d-block fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Απάντηση Μαθητή (Σωστή):</span>
                                        <span class='fw-bold text-success' style='font-size:1.1em;'>" . $dispStudentAns . "</span>
                                    </div>
                                </div>
                            </div>";
                        } else {
                            $highlightedStudentText .= "
                            <div class='wrong-answer-row d-flex justify-content-between align-items-stretch mb-2 p-2 bg-white border border-danger rounded shadow-sm'>
                                <div class='d-flex align-items-center'>
                                    <span class='wrong-icon badge bg-danger rounded-pill me-3 px-3 py-2'><i class='fa fa-times'></i> $ansNum</span>
                                </div>
                                <div class='d-flex flex-grow-1 align-items-center border-start ps-3 me-3'>
                                    <div class='me-4' style='flex: 1;'>
                                        <span class='text-danger small text-uppercase d-block fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Απάντηση Μαθητή:</span>
                                        <span class='text-danger fw-bold' style='font-size:1.1em;'>" . $dispStudentAns . "</span>
                                    </div>
                                    <div style='flex: 1;'>
                                        <span class='text-success small text-uppercase d-block fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Σωστή Λύση:</span>
                                        <span class='correct-hint text-success fw-bold' style='font-size:1.1em;'>" . $dispCorrectAns . "</span>
                                    </div>
                                </div>
                                <div class='d-flex align-items-center border-start ps-2'>$btnHtml</div>
                            </div>";
                        }
                    } else {
                        $highlightedStudentText .= "
                        <div class='wrong-answer-row d-flex justify-content-between align-items-stretch mb-2 p-2 bg-white border border-warning rounded shadow-sm'>
                            <div class='d-flex align-items-center'>
                                <span class='wrong-icon badge bg-warning text-dark rounded-pill me-3 px-3 py-2'><i class='fa fa-minus'></i> $ansNum</span>
                            </div>
                                <div class='d-flex flex-grow-1 align-items-center border-start ps-3 me-3'>
                                    <div class='me-4' style='flex: 1;'>
                                        <span class='text-muted small text-uppercase d-block fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Απάντηση Μαθητή:</span>
                                        <span class='text-muted fst-italic' style='font-size:1em;'>Δεν απαντήθηκε</span>
                                    </div>
                                    <div style='flex: 1;'>
                                        <span class='text-success small text-uppercase d-block fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Σωστή Λύση:</span>
                                        <span class='correct-hint text-success fw-bold' style='font-size:1.1em;'>" . $dispCorrectAns . "</span>
                                    </div>
                                </div>
                                <div class='d-flex align-items-center border-start ps-2'>$btnHtml</div>
                        </div>";
                    }
                }
            }
            $highlightedStudentText .= "</div>";
        } elseif ($answersPartPos !== false) {
            $highlightedStudentText .= $answersHtml;
        }

        $autoGrade = '';
        if ($isAutoGradable && $totalExpected > 0) {
            $rawGrade = ($totalCorrect / $totalExpected) * 20;
            $autoGrade = round($rawGrade * 2) / 2; // Στρογγυλοποίηση στο πλησιέστερο 0.5
        }

        return [
            'html' => $highlightedStudentText,
            'autoGrade' => (string)$autoGrade
        ];
    }

    /**
     * @param mysqli_result|bool|array $result
     * @param AdminDbHandler $dbHandler
     */
    public function listMezedakia($result, $dbHandler, $totalCount = null)
    {
        $allGroups = $dbHandler->getGroups(isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "");
        $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

        // Υπολογισμός Στατιστικών
        $totalMezedakia = $totalCount !== null ? $totalCount : ($result ? $result->num_rows : 0);
        $allStudents = $dbHandler->getTutorStudents($userYear);
        $realStudentsCount = 0;
        foreach ($allStudents as $s) {
            if ($s['studentId'] != 999999) $realStudentsCount++;
        }
        $totalGroups = count($allGroups);
        $pendingRequests = $dbHandler->getPendingExtensionRequestsCount($userYear);
?>
        <div class="container mt-4">
            <!-- Κάρτες Στατιστικών Dashboard -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                    <div class="card bg-primary text-white shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase fw-bold small mb-1">Μαθητές</div>
                                <h2 class="mb-0 fw-bold"><?php echo $realStudentsCount; ?></h2>
                            </div>
                            <i class="fa fa-users fa-3x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                    <div class="card bg-warning text-dark shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase fw-bold small mb-1">Μεζεδάκια</div>
                                <h2 class="mb-0 fw-bold"><?php echo $totalMezedakia; ?></h2>
                            </div>
                            <i class="fa fa-coffee fa-3x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
                    <div class="card bg-success text-white shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase fw-bold small mb-1">Ομάδες</div>
                                <h2 class="mb-0 fw-bold"><?php echo $totalGroups; ?></h2>
                            </div>
                            <i class="fa fa-sitemap fa-3x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card <?php echo $pendingRequests > 0 ? 'bg-danger' : 'bg-secondary'; ?> text-white shadow-sm h-100 border-0" style="<?php echo $pendingRequests > 0 ? 'cursor: pointer; transition: transform 0.2s;' : ''; ?>" <?php echo $pendingRequests > 0 ? 'onclick="window.location.href=\'index.php?action=view_extension_requests\'" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'translateY(0)\'"' : ''; ?>>
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase fw-bold small mb-1">Εκκρεμή Αιτήματα</div>
                                <h2 class="mb-0 fw-bold"><?php echo $pendingRequests; ?></h2>
                            </div>
                            <i class="fa fa-clock-o fa-3x" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
                <h3 class="mb-0 text-center text-xl-start"><i class="fa fa-list text-primary"></i> Διαχείριση Μεζεδακίων</h3>
                <div class="d-flex flex-wrap justify-content-center justify-content-xl-end align-items-center gap-2 w-100" style="max-width: 950px;">
                    <div class="form-check form-switch d-flex align-items-center justify-content-center mx-md-2 mb-0 flex-grow-1 flex-md-grow-0" title="Εμφάνιση/Απόκρυψη προγραμματισμένων (μελλοντικών) μεζεδακίων">
                        <input class="form-check-input mt-0 me-2" type="checkbox" id="toggleFutureMeze" style="cursor: pointer; transform: scale(1.2);">
                        <label class="form-check-label fw-bold text-primary mb-0" for="toggleFutureMeze" style="cursor: pointer;">Προγραμματισμένα</label>
                    </div>
                    <button type="button" id="togglePrevYearMeze" class="btn btn-outline-warning shadow-sm fw-bold text-nowrap flex-grow-1 flex-md-grow-0">
                        <i class="fa fa-history"></i> <span class="d-none d-sm-inline">Περσινά</span>
                    </button>
                    <button type="button" id="toggleArchivedMeze" class="btn btn-outline-secondary shadow-sm fw-bold text-nowrap flex-grow-1 flex-md-grow-0">
                        <i class="fa fa-archive"></i> <span class="d-none d-sm-inline">Εμφάνιση Αρχείου</span>
                    </button>
                    <a href="index.php?action=massDeleteSubmissions" class="btn btn-danger shadow-sm fw-bold text-nowrap flex-grow-1 flex-md-grow-0" onclick="return confirm('ΠΡΟΣΟΧΗ! Αυτό θα διαγράψει ΟΛΕΣ τις υποβολές των μαθητών και τις φωτογραφίες τους από τον server για να ελαφρύνει το σύστημα. Είστε απόλυτα σίγουροι;')">
                        <i class="fa fa-trash"></i> <span class="d-none d-sm-inline">Καθαρισμός Υποβολών</span><span class="d-inline d-sm-none">Καθ. Υποβολών</span>
                    </a>
                    <a href="index.php?action=massHideMezedakia" class="btn btn-warning shadow-sm text-dark fw-bold text-nowrap flex-grow-1 flex-md-grow-0" onclick="return confirm('Αυτό θα μεταφέρει όλα τα ήδη ορατά μεζεδάκια στο 2030, μαζί με τις προθεσμίες τους, για να κρυφτούν από τους μαθητές. Είστε σίγουροι;')">
                        <i class="fa fa-eye-slash"></i> <span class="d-none d-sm-inline">Μαζική Απόκρυψη Παλιών</span><span class="d-inline d-sm-none">Απόκρυψη Παλιών</span>
                    </a>
                    <input type="text" id="mezeFilter" class="form-control shadow-sm flex-grow-1" style="min-width: 150px; max-width: 250px;" placeholder="Αναζήτηση...">
                </div>
            </div>
            <style>
                .hide-future .future-meze-row:not(.archived-meze-row) {
                    display: none !important;
                }

                .hide-archive .archived-meze-row {
                    display: none !important;
                }

                .hide-prev-year .prev-year-meze-row {
                    display: none !important;
                }

                #toggleArchivedMeze.active, #togglePrevYearMeze.active {
                    background-color: #6c757d;
                    color: white;
                }
            </style>

            <?php
            // Προετοιμασία των Τεχνικών (Tags) για να μπορούμε να τα εμφανίζουμε στη λίστα
            $allTypes = $dbHandler->getExerciseTypes();
            $typeMap = [];
            foreach ($allTypes as $t) {
                $typeMap[$t['id']] = $t['name'];
            }
            ?>
            <div class="table-responsive shadow-sm border rounded" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-bordered table-striped align-middle mb-0 hide-future hide-archive hide-prev-year" id="mezeTable">
                    <thead class="table-dark text-center" style="position: sticky; top: 0; z-index: 2;">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 10%">Ημερομηνία</th>
                            <th style="width: 12%">Λήξη</th>
                            <th style="width: 35%">Εκφώνηση (Preview)</th>
                            <th style="width: 38%">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $result->data_seek(0);
                            $visibleCount = 0;
                            $futureCount = 0;

                            while ($row = $result->fetch_assoc()):
                                $mezeId = $row['mezeId'];
                                $mTimestamp = strtotime($row['mezeDate']);
                                $solTimestamp = strtotime($row['solutionDate'] ?? '');
                                $currentTimestamp = time();
                                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : '';

                                $isFuture = ($mTimestamp > $currentTimestamp);
                                $isArchived = ($isFuture && date('Y', $mTimestamp) >= 2030);
                                $isPrevYear = ((int)($row['mezeNumber'] ?? 0) < 27);

                                $tr_classes = [];
                                if ($isArchived) {
                                    $tr_classes[] = 'archived-meze-row';
                                } else if ($isFuture) {
                                    $tr_classes[] = 'future-meze-row';
                                }
                                if ($isPrevYear) {
                                    $tr_classes[] = 'prev-year-meze-row';
                                }

                                // Pagination logic only for non-archived items
                                if (!$isArchived) {
                                    if ($isFuture) {
                                        $futureCount++;
                                        if ($futureCount > 15) $tr_classes[] = 'd-none hidden-admin-meze-item';
                                    } else {
                                        $visibleCount++;
                                        if ($visibleCount > 15) $tr_classes[] = 'd-none hidden-admin-meze-item';
                                    }
                                }

                                $isLocked = (isset($row['isLocked']) && $row['isLocked'] == 1);
                                $isExpired = ($solTimestamp > 0 && $solTimestamp < $currentTimestamp);
                                $hasExtensions = (!empty($userYear)) ? $dbHandler->hasAnyExtension($mezeId, $userYear) : false;
                                $isClosed = $isLocked || ($isExpired && !$hasExtensions);
                                $groupDeadlines = $dbHandler->getGroupDeadlinesForMeze($mezeId);

                                $rowStyle = '';
                                $badgeHtml = '';

                                if ($isLocked) {
                                    $badgeHtml .= '<br><span class="badge bg-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-lock"></i> Κλειδωμένο (Manual)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                } elseif ($isClosed) {
                                    $badgeHtml .= '<br><span class="badge bg-secondary mt-1" style="font-size: 0.75rem; opacity: 0.8;"><i class="fa fa-lock"></i> Λήξη (Χωρίς Ext)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                }

                                if ($hasExtensions && !$isLocked && !$isArchived) {
                                    $badgeHtml .= '<br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-clock-o"></i> Ενεργή Παράταση</span>';
                                    if (empty($rowStyle) && !$isFuture) $rowStyle = 'style="background-color: #fffde7;"';
                                }

                                if (empty(trim($row['mezeSolution'] ?? '')) && empty($row['mezeSolutionImage'])) {
                                    $badgeHtml .= '<br><span class="badge bg-danger mt-1" style="font-size: 0.75rem;"><i class="fa fa-warning"></i> Χωρίς Λύση</span>';
                                }

                                if (!empty($userYear)) {
                                    $ungradedCount = $dbHandler->getUngradedSubmissionsCountForMeze($mezeId, $userYear);
                                    $notSubmittedData = $dbHandler->getNotSubmittedCount($mezeId, $userYear);
                                    $notSubmittedCount = is_array($notSubmittedData) ? $notSubmittedData['total'] : $notSubmittedData;
                                    $ungradedNotSubmittedCount = is_array($notSubmittedData) ? $notSubmittedData['ungraded'] : 0;

                                    if ($ungradedCount > 0) {
                                        $badgeHtml .= '<br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-exclamation-triangle"></i> ' . $ungradedCount . ' προς Βαθμολόγηση</span>';
                                        if (!$isFuture) $rowStyle = 'style="background-color: #fff3cd;"';
                                    }
                                    if ($notSubmittedCount > 0 && !$isFuture && !$isArchived) {
                                        if ($ungradedNotSubmittedCount > 0) {
                                            $badgeHtml .= '<br><span class="badge bg-danger mt-1" style="font-size: 0.75rem;"><i class="fa fa-exclamation-circle"></i> ' . $notSubmittedCount . ' δεν απάντησαν (' . $ungradedNotSubmittedCount . ' αβαθμολόγητοι)</span>';
                                            $rowStyle = 'style="background-color: #f8d7da;"';
                                        } else {
                                            $badgeHtml .= '<br><span class="badge bg-light text-muted border mt-1" style="font-size: 0.75rem;"><i class="fa fa-hourglass-o"></i> ' . $notSubmittedCount . ' δεν απάντησαν (βαθμολογήθηκαν)</span>';
                                        }
                                    }
                                }

                                if (isset($row['isSos']) && $row['isSos'] == 1) {
                                    $badgeHtml .= '<br><span class="badge bg-danger mt-1" style="font-size: 0.75rem;"><i class="fa fa-fire"></i> SOS</span>';
                                }

                                if (!empty($row['sourceBook'])) {
                                    $badgeHtml .= '<br><span class="badge bg-secondary mt-1" style="font-size: 0.75rem;"><i class="fa fa-book"></i> ' . htmlspecialchars($row['sourceBook']) . (!empty($row['sourceExercise']) ? ' - Άσκ. ' . htmlspecialchars($row['sourceExercise']) : '') . '</span>';
                                }

                                if (!empty($groupDeadlines)) {
                                    foreach ($allGroups as $grp) {
                                        if (isset($groupDeadlines[$grp['id']])) {
                                            $dDate = date('d/m H:i', strtotime($groupDeadlines[$grp['id']]));
                                            $badgeHtml .= '<br><span class="badge bg-info text-dark mt-1 shadow-sm" style="font-size: 0.75rem;"><i class="fa fa-users"></i> ' . htmlspecialchars($grp['group_name']) . ' &rarr; <i class="fa fa-clock-o text-danger"></i> ' . $dDate . '</span>';
                                        }
                                    }
                                }

                                if ($isArchived) {
                                    $badgeHtml .= '<br><span class="badge bg-secondary text-white mt-1 shadow-sm" style="font-size: 0.75rem;"><i class="fa fa-archive"></i> Αρχειοθετημένο</span>';
                                    $rowStyle = 'style="background-color: #e9ecef; color: #495057;"';
                                } else if ($isFuture) {
                                    $badgeHtml .= '<br><span class="badge bg-primary text-white mt-1 shadow-sm" style="font-size: 0.75rem;"><i class="fa fa-calendar-check-o"></i> Προγραμματισμένο</span>';
                                    $rowStyle = 'style="background-color: #e9f2fd; color: #495057;"';
                                }

                                // Βελτιωμένος καθαρισμός κειμένου HTML για την προεπισκόπηση
                                $cleanText = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/si', '', $row['mezeText']);
                                $cleanText = str_replace(['<br>', '<br/>', '</p>', '</div>', 'AEPP_RAW_START', 'AEPP_RAW_END', '&nbsp;'], ' ', $cleanText);
                                $cleanText = strip_tags($cleanText);
                                $cleanText = preg_replace('/\s+/u', ' ', $cleanText);

                                // Αφαίρεση τυποποιημένων επικεφαλίδων Πανελλαδικών (με ανοχή στους τόνους πεζών/κεφαλαίων)
                                // 1. Αφαιρεί ολόκληρο το κατεβατό "ΠΑΝΕΛΛΑΔΙΚΕΣ ΕΞΕΤΑΣΕΙΣ ... ΑΝΑΠΤΥΞΗ ΕΦΑΡΜΟΓΩΝ..." (ό,τι και αν μεσολαβεί)
                                $cleanText = preg_replace('/(ΕΠΑΝΑΛΗΠΤΙΚ[ΕΈ]Σ\s+)?ΠΑΝΕΛΛΑΔΙΚ[ΕΈ]Σ\s+ΕΞΕΤ[ΑΆ]ΣΕΙΣ.*?(ΑΝ[ΑΆ]ΠΤΥΞΗ\s+ΕΦΑΡΜΟΓ[ΩΏ]Ν\s+ΣΕ\s+ΠΡΟΓΡΑΜΜΑΤΙΣΤΙΚ[ΟΌ]\s+ΠΕΡΙΒ[ΑΆ]ΛΛΟΝ|ΠΛΗΡΟΦΟΡΙΚ[ΗΉ](?:\s+ΠΡΟΣΑΝΑΤΟΛΙΣΜΟ[ΥΎ])?)(?:\s*Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+)?/iu', '', $cleanText);
                                // 2. Αφαιρεί σκέτο το όνομα του μαθήματος (αν έχει ξεμείνει χωρίς το "Πανελλαδικές")
                                $cleanText = preg_replace('/(ΕΞΕΤΑΖ[ΟΌ]ΜΕΝΟ\s+Μ[ΑΆ]ΘΗΜΑ:?)?\s*(ΑΝ[ΑΆ]ΠΤΥΞΗ\s+ΕΦΑΡΜΟΓ[ΩΏ]Ν\s+ΣΕ\s+ΠΡΟΓΡΑΜΜΑΤΙΣΤΙΚ[ΟΌ]\s+ΠΕΡΙΒ[ΑΆ]ΛΛΟΝ|ΠΛΗΡΟΦΟΡΙΚ[ΗΉ](?:\s+ΠΡΟΣΑΝΑΤΟΛΙΣΜΟ[ΥΎ])?)\s*(Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+)?/iu', '', $cleanText);
                                // 3. Αφαιρεί τυχόν "ΘΕΜΑ Χ" που έμεινε ακάλυπτο στην αρχή του κειμένου
                                $cleanText = preg_replace('/^\s*Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+\s*/iu', '', $cleanText);

                                $previewText = mb_substr(trim($cleanText), 0, 150);
                                if (mb_strlen(trim($cleanText)) > 150) $previewText .= "...";

                                // Ανάκτηση των Τεχνικών (Tags) για το συγκεκριμένο Μεζεδάκι
                                $typeIds = $dbHandler->getMezeTypeIds($mezeId);
                                $tagsHtml = '';
                                if (!empty($typeIds)) {
                                    foreach ($typeIds as $tid) {
                                        if (isset($typeMap[$tid])) {
                                            $tagsHtml .= '<span class="badge bg-info text-dark me-1 shadow-sm mt-1" style="font-size: 0.7rem;"><i class="fa fa-tag"></i> ' . $typeMap[$tid] . '</span> ';
                                        }
                                    }
                                    $tagsHtml = '<div class="mt-1">' . $tagsHtml . '</div>';
                                }
                        ?>
                                <tr <?php echo $rowStyle; ?> class="align-middle <?php echo implode(' ', $tr_classes); ?>">
                                    <td class="text-center fw-bold"><?php echo $row['mezeNumber']; ?></td>
                                    <td class="text-center small"><?php echo $dbHandler->formatGreekDate($row['mezeDate']); ?></td>
                                    <td class="text-center small">
                                        <?php
                                        if (!empty($row['solutionDate'])) {
                                            $dateColor = $isExpired ? 'text-danger fw-bold' : '';
                                            $timeColor = $isExpired ? 'text-danger' : 'text-muted';
                                            echo "<span class='$dateColor'>" . $dbHandler->formatGreekDate($row['solutionDate']) . "</span><br><span class='$timeColor'>" . date('H:i', strtotime($row['solutionDate'])) . "</span>";
                                        } else {
                                            echo "-";
                                        }
                                        ?>
                                    </td>
                                    <td class="small">
                                        <div class="text-dark mb-1" style="line-height: 1.5; font-size: 0.95rem; font-weight: 500;"><?php echo $previewText; ?></div>
                                        <?php echo $tagsHtml; ?>
                                        <?php echo $badgeHtml; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                            <a href="index.php?action=toggleMezeLock&amp;id=<?php echo $mezeId; ?>&amp;status=<?php echo $isLocked ? 0 : 1; ?>" class="btn btn-outline-<?php echo $isLocked ? 'success' : 'secondary'; ?> btn-sm" title="<?php echo $isLocked ? 'Ξεκλείδωμα' : 'Κλείδωμα'; ?>"><i class="fa fa-<?php echo $isLocked ? 'unlock' : 'lock'; ?>"></i></a>
                                            <a href="index.php?action=previewMeze&amp;id=<?php echo $mezeId; ?>" target="_blank" class="btn btn-dark btn-sm" title="Προεπισκόπηση"><i class="fa fa-search"></i></a>
                                            <a href="index.php?action=editMezedaki&amp;id=<?php echo $mezeId; ?>" class="btn btn-warning btn-sm" title="Επεξεργασία"><i class="fa fa-edit"></i></a>
                                            <a href="index.php?action=viewSubmissions&amp;id=<?php echo $mezeId; ?>" class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-info'; ?> btn-sm" title="Απαντήσεις"><i class="fa fa-eye"></i></a>
                                            <a href="index.php?action=deleteMezedaki&amp;id=<?php echo $mezeId; ?>" class="btn btn-danger btn-sm" title="Διαγραφή" onclick="return confirm('Διαγραφή;')"><i class="fa fa-trash"></i></a>

                                            <a href="index.php?action=setMezeToday&id=<?php echo $mezeId; ?>" class="btn btn-success btn-sm" title="Ορισμός ημερομηνίας εμφάνισης για Σήμερα"><i class="fa fa-calendar-check-o"></i> Σήμερα</a>

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-users"></i> Deadlines
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 320px;">
                                                    <?php foreach ($allGroups as $g):
                                                        $hasDeadline = isset($groupDeadlines[$g['id']]);
                                                        $deadlineDate = $hasDeadline ? date('d/m H:i', strtotime($groupDeadlines[$g['id']])) : '';
                                                    ?>
                                                        <li>
                                                            <form action="index.php?action=toggleGroupDeadline" method="post" class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                                                                <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                                                <input type="hidden" name="group_id" value="<?php echo $g['id']; ?>">
                                                                <span class="fw-bold me-auto text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($g['group_name']); ?>"><?php echo htmlspecialchars($g['group_name']); ?></span>
                                                                <?php if ($hasDeadline): ?>
                                                                    <span class="badge bg-success me-2 shadow-sm"><i class="fa fa-clock-o"></i> <?php echo $deadlineDate; ?></span>
                                                                <?php endif; ?>
                                                                <button type="submit" class="btn btn-sm <?php echo $hasDeadline ? 'btn-danger' : 'btn-success'; ?>"><?php echo $hasDeadline ? 'Ακύρωση' : 'Set'; ?></button>
                                                            </form>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>

            <?php
            $hiddenTotal = 0;
            if (isset($visibleCount)) $hiddenTotal += max(0, $visibleCount - 15);
            if (isset($futureCount)) $hiddenTotal += max(0, $futureCount - 15);
            if ($hiddenTotal > 0):
            ?>
                <div class="text-center my-4" id="loadMoreAdminMezeContainer">
                    <button class="btn btn-outline-primary shadow-sm font-weight-bold px-4" onclick="loadMoreAdminMezedakia()">
                        <i class="fa fa-arrow-down"></i> Εμφάνιση παλαιότερων (<?php echo $hiddenTotal; ?>)
                    </button>
                </div>
            <?php endif; ?>
            <script>
                // Διακόπτης εμφάνισης/απόκρυψης προγραμματισμένων
                var toggleFutureBtn = document.getElementById('toggleFutureMeze');
                if (toggleFutureBtn) {
                    toggleFutureBtn.addEventListener('change', function() {
                        document.getElementById('mezeTable').classList.toggle('hide-future', !this.checked);
                    });
                }

                // Διακόπτης εμφάνισης/απόκρυψης αρχειοθετημένων
                var toggleArchivedBtn = document.getElementById('toggleArchivedMeze');
                if (toggleArchivedBtn) {
                    toggleArchivedBtn.addEventListener('click', function() {
                        var table = document.getElementById('mezeTable');
                        table.classList.toggle('hide-archive');
                        this.classList.toggle('active');
                        this.innerHTML = table.classList.contains('hide-archive') ? '<i class="fa fa-archive"></i> <span class="d-none d-sm-inline">Εμφάνιση Αρχείου</span>' : '<i class="fa fa-eye-slash"></i> <span class="d-none d-sm-inline">Απόκρυψη Αρχείου</span>';
                    });
                }

                // Διακόπτης εμφάνισης/απόκρυψης περσινών
                var togglePrevYearBtn = document.getElementById('togglePrevYearMeze');
                if (togglePrevYearBtn) {
                    togglePrevYearBtn.addEventListener('click', function() {
                        var table = document.getElementById('mezeTable');
                        table.classList.toggle('hide-prev-year');
                        this.classList.toggle('active');
                        this.innerHTML = table.classList.contains('hide-prev-year') ? '<i class="fa fa-history"></i> <span class="d-none d-sm-inline">Περσινά</span>' : '<i class="fa fa-eye-slash"></i> <span class="d-none d-sm-inline">Απόκρυψη Περσινών</span>';
                    });
                }

                function loadMoreAdminMezedakia() {
                    document.querySelectorAll(".hidden-admin-meze-item").forEach(function(el) {
                        el.classList.remove("d-none");
                    });
                    var container = document.getElementById("loadMoreAdminMezeContainer");
                    if (container) container.style.display = "none";
                }

                // Αν ο χρήστης ξεκινήσει να γράφει στην αναζήτηση, εμφανίζουμε όλα τα παλιά μεζεδάκια 
                var filterInput = document.getElementById('mezeFilter');
                if (filterInput) {
                    filterInput.addEventListener('input', function() {
                        if (this.value.trim() !== '') {
                            loadMoreAdminMezedakia();
                        }
                    });
                }
            </script>
        </div>
    <?php
    }

    public function addMezedakiForm($exerciseTypes = [], $nextNumber = 1)
    {
        if (empty($exerciseTypes)) {
            $db = new AdminDbHandler();
            $exerciseTypes = $db->getExerciseTypes();
        }
    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="fa fa-coffee"></i> Νέο Μεζεδάκι</h3>
                <a href="index.php?action=listMezedakia" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Επιστροφή στη Λίστα
                </a>
            </div>
            <form action="index.php?action=saveMezedaki" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Αριθμός</label>
                        <input type="number" name="mezeNumber" class="form-control" value="<?php echo $nextNumber; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ημερομηνία Εμφάνισης</label>
                        <div class="input-group">
                            <input type="date" name="mezeDate" id="mezeDateAdd" class="form-control" value="<?php echo date('Y-m-d'); ?>" required onchange="autoSetDeadline('mezeDateAdd', 'solutionDateAdd')">
                            <button class="btn btn-success px-2" type="button" onclick="setToday('mezeDateAdd', 'solutionDateAdd')" title="Ορισμός ημερομηνίας εμφάνισης για Σήμερα"><i class="fa fa-calendar-check-o"></i></button>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Deadline Λύσης</label>
                        <input type="datetime-local" name="solutionDate" id="solutionDateAdd" class="form-control" value="<?php echo date('Y-m-d\T03:00', strtotime('+1 day')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-danger">
                        <label class="switch-container text-danger font-weight-bold mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="isSos" value="1" id="isSosCheckAdd">
                            <span><i class="fa fa-fire"></i> Χαρακτηρισμός ως SOS (Τεχνική Πανελληνίων)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-primary">
                        <label class="switch-container text-primary font-weight-bold mb-2" style="cursor: pointer;">
                            <input type="checkbox" name="isPanhellenic" value="1" id="isPanCheck" onchange="togglePanFields(this)">
                            <span><i class="fa fa-university"></i> Θέμα Πανελληνίων Εξετάσεων</span>
                        </label>

                        <div id="panelliniesFields" style="display: none;" class="mt-3 p-3 bg-light border rounded">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Έτος</label>
                                    <input type="number" name="panYear" class="form-control" placeholder="π.χ. 2024">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Θέμα</label>
                                    <select name="panThema" class="form-control">
                                        <option value="">--</option>
                                        <option value="A">Θέμα Α</option>
                                        <option value="B">Θέμα Β</option>
                                        <option value="G">Θέμα Γ</option>
                                        <option value="D">Θέμα Δ</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Είδος Εξέτασης</label>
                                    <select name="panExamType" class="form-control">
                                        <option value="Kanonikes">Κανονικές</option>
                                        <option value="Epanaliptikes">Επαναληπτικές</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Σχολείο</label>
                                    <select name="panSchoolType" class="form-control">
                                        <option value="Hmerisio">Ημερήσιο</option>
                                        <option value="Esperino">Εσπερινό</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-info">
                        <label class="font-weight-bold text-info mb-2"><i class="fa fa-book"></i> Πηγή Άσκησης (Βοήθημα)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Βιβλίο / Συγγραφέας</label>
                                <select name="sourceBook" class="form-control">
                                    <option value="">-- Καμία / Άλλη --</option>
                                    <option value="Κοψίνης 1">Κοψίνης Τεύχος 1</option>
                                    <option value="Κοψίνης 2">Κοψίνης Τεύχος 2</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Κεφάλαιο.Άσκηση (π.χ. 4.13)</label>
                                <input type="text" name="sourceExercise" class="form-control" placeholder="π.χ. 4.13">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold"><i class="fa fa-tags text-info"></i> Είδος Άσκησης / Τεχνικές</label>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start bg-white" type="button" id="typeDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Επιλογή Τεχνικών...
                        </button>
                        <div class="dropdown-menu p-3 w-100 shadow" aria-labelledby="typeDropdown" style="max-height: 350px; overflow-y: auto;">
                            <input type="text" class="form-control form-control-sm mb-2" id="typeSearch" placeholder="Αναζήτηση τεχνικής..." autocomplete="off">
                            <hr class="my-2">
                            <div class="row px-2">
                                <?php foreach ($exerciseTypes as $type): ?>
                                    <div class="col-md-4 col-6 mb-2 type-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="type_<?php echo $type['id']; ?>" name="exercise_types[]" value="<?php echo $type['id']; ?>" style="cursor:pointer;">
                                            <label class="form-check-label small" for="type_<?php echo $type['id']; ?>" style="cursor:pointer; margin-left: 5px;">
                                                <?php echo $type['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Κείμενο / Κώδικας (HTML/Bootstrap)</label>
                        <div>
                            <button type="button" id="formatBtn_newMezeText" class="btn btn-outline-info btn-sm me-2 shadow-sm d-none" onclick="formatCM('newMezeEditor_cm')" title="Αυτόματη στοίχιση κώδικα">
                                <i class="fa fa-indent"></i> Format
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2 shadow-sm" onclick="toggleCKEditor('newMezeText', 'newMezeEditor', Base64UploadAdapterPlugin)" title="Εναλλαγή σε προβολή κώδικα HTML">
                                <i class="fa fa-code"></i> HTML
                            </button>
                            <!-- Κουμπί που ανοίγει το Modal του Builder -->
                            <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#builderModal">
                                <i class="fa fa-magic"></i> Γρήγορη Δημιουργία Κενών / Σ-Λ
                            </button>
                        </div>
                    </div>
                    <textarea name="mezeText" id="newMezeText" class="form-control" rows="6" placeholder="Γράψε την εκφώνηση εδώ..."></textarea>

                    <!-- Το Bootstrap Modal για τον AEPP Builder -->
                    <div class="modal fade" id="builderModal" tabindex="-1" aria-labelledby="builderModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title" id="builderModalLabel"><i class="fa fa-magic text-warning"></i> AEPP Builder</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <iframe src="../builder.html" id="builderIframeNew" style="width: 100%; height: 80vh; border: none; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία (Προαιρετικά)</label>
                    <input type="file" name="mezeImage" class="form-control">
                </div>
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0"><strong>Οδηγίες / Hints (προς μαθητές):</strong></label>
                        <div>
                            <button type="button" id="formatBtn_newMezeHints" class="btn btn-outline-info btn-sm me-2 shadow-sm d-none" onclick="formatCM('newHintsEditor_cm')" title="Αυτόματη στοίχιση κώδικα">
                                <i class="fa fa-indent"></i> Format
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditor('newMezeHints', 'newHintsEditor', Base64UploadAdapterPlugin)">
                                <i class="fa fa-code"></i> HTML
                            </button>
                        </div>
                    </div>
                    <textarea name="mezeHints" id="newMezeHints" class="form-control" rows="3" placeholder="Μικρές βοήθειες..."></textarea>
                </div>
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Λύση (Προαιρετικά - θα εμφανίζεται σε accordion)</label>
                        <div>
                            <button type="button" id="formatBtn_newMezeSolution" class="btn btn-outline-info btn-sm me-2 shadow-sm d-none" onclick="formatCM('newSolutionEditor_cm')" title="Αυτόματη στοίχιση κώδικα">
                                <i class="fa fa-indent"></i> Format
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditor('newMezeSolution', 'newSolutionEditor', Base64UploadAdapterPlugin)">
                                <i class="fa fa-code"></i> HTML
                            </button>
                        </div>
                    </div>
                    <textarea name="mezeSolution" id="newMezeSolution" class="form-control" rows="4" placeholder="Γράψε τη λύση εδώ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία Λύσης (Προαιρετικά)</label>
                    <input type="file" name="mezeSolutionImage" class="form-control-file">
                </div>
                <div class="mt-4 row gap-2 gap-md-0">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning btn-block font-weight-bold shadow w-100">
                            <i class="fa fa-save"></i> Αποθήκευση & Νέο
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="return_to_list" value="1" class="btn btn-success btn-block font-weight-bold shadow w-100">
                            <i class="fa fa-list"></i> Αποθήκευση & Επιστροφή
                        </button>
                    </div>
                    <div class="col-md-4">
                        <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-block shadow w-100">
                            <i class="fa fa-times"></i> Ακύρωση
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <script>
            // Custom Upload Adapter για να επιτρέπει την εισαγωγή (paste/drag-drop) εικόνων κατευθείαν στον Editor ως Base64
            class Base64UploadAdapter {
                constructor(loader) {
                    this.loader = loader;
                }
                upload() {
                    return this.loader.file.then(file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const MAX_WIDTH = 800; // Μέγιστο πλάτος για να χωράει στα κινητά και να μην βαραίνει τη βάση
                                let width = img.width;
                                let height = img.height;
                                if (width > MAX_WIDTH) {
                                    height = Math.round((height * MAX_WIDTH) / width);
                                    width = MAX_WIDTH;
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.fillStyle = '#ffffff'; // Λευκό φόντο για τυχόν διάφανα PNG
                                ctx.fillRect(0, 0, width, height);
                                ctx.drawImage(img, 0, 0, width, height);
                                resolve({
                                    default: canvas.toDataURL('image/jpeg', 0.85)
                                }); // Αυτόματη συμπίεση σε ελαφρύ JPEG
                            };
                            img.onerror = () => resolve({
                                default: e.target.result
                            }); // Σε περίπτωση σφάλματος κρατάει την αρχική
                            img.src = e.target.result;
                        };
                        reader.onerror = error => reject(error);
                        reader.readAsDataURL(file);
                    }));
                }
                abort() {}
            }

            function Base64UploadAdapterPlugin(editor) {
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new Base64UploadAdapter(loader);
            }

            window.newMezeEditor = null;
            window.newSolutionEditor = null;
            window.newHintsEditor = null;

            function initSmartEditorNew(textareaId, editorVar, plugin) {
                const textarea = document.querySelector('#' + textareaId);
                if (!textarea) return;

                const val = textarea.value;
                const cmVar = editorVar + '_cm';
                const formatBtnId = 'formatBtn_' + textareaId;
                const formatBtn = document.getElementById(formatBtnId);

                // Έλεγχος για "ευαίσθητο" HTML κώδικα
                const hasRawHtml = val.includes('<pre') || val.includes('<table') || val.includes('AEPP_RAW_START') || val.includes('<div');

                if (hasRawHtml) {
                    window[cmVar] = CodeMirror.fromTextArea(textarea, {
                        mode: "xml",
                        htmlMode: true,
                        lineNumbers: true,
                        lineWrapping: true,
                        viewportMargin: Infinity
                    });
                    window[cmVar].on('change', function(cm) {
                        textarea.value = cm.getValue();
                    });
                    if (formatBtn) formatBtn.classList.remove('d-none');
                } else {
                    ClassicEditor.create(textarea, {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            textarea.value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }

            initSmartEditorNew('newMezeText', 'newMezeEditor', Base64UploadAdapterPlugin);
            initSmartEditorNew('newMezeSolution', 'newSolutionEditor', Base64UploadAdapterPlugin);
            initSmartEditorNew('newMezeHints', 'newHintsEditor', Base64UploadAdapterPlugin);

            if (typeof formatCM === 'undefined') {
                window.formatCM = function(cmVar) {
                    if (window[cmVar] && typeof html_beautify !== 'undefined') {
                        const unformatted = window[cmVar].getValue();
                        const formatted = html_beautify(unformatted, {
                            indent_size: 4,
                            wrap_line_length: 0,
                            unformatted: ['pre', 'code']
                        });
                        window[cmVar].setValue(formatted);
                    }
                }
            }

            if (typeof toggleCKEditor === 'undefined') {
                window.toggleCKEditor = function(textareaId, editorVar, plugin) {
                    const cmVar = editorVar + '_cm';
                    const formatBtnId = 'formatBtn_' + textareaId;
                    const formatBtn = document.getElementById(formatBtnId);

                    if (window[editorVar]) {
                        document.querySelector('#' + textareaId).value = window[editorVar].getData();
                        window[editorVar].destroy().then(() => {
                            window[editorVar] = null;

                            window[cmVar] = CodeMirror.fromTextArea(document.querySelector('#' + textareaId), {
                                mode: "xml",
                                htmlMode: true,
                                lineNumbers: true,
                                lineWrapping: true,
                                viewportMargin: Infinity
                            });
                            window[cmVar].on('change', function(cm) {
                                document.querySelector('#' + textareaId).value = cm.getValue();
                            });

                            if (formatBtn) formatBtn.classList.remove('d-none');
                        });
                    } else {
                        if (window[cmVar]) {
                            document.querySelector('#' + textareaId).value = window[cmVar].getValue();
                            window[cmVar].toTextArea();
                            window[cmVar] = null;

                            if (formatBtn) formatBtn.classList.add('d-none');
                        }
                        ClassicEditor.create(document.querySelector('#' + textareaId), {
                            extraPlugins: [plugin]
                        }).then(editor => {
                            window[editorVar] = editor;
                            editor.model.document.on('change:data', () => {
                                document.querySelector('#' + textareaId).value = editor.getData();
                            });
                        }).catch(error => console.error(error));
                    }
                }
            }

            function togglePanFields(checkbox) {
                document.getElementById('panelliniesFields').style.display = checkbox.checked ? 'block' : 'none';
            }

            // Αυτόματη φόρτωση της εκφώνησης στον Builder όταν ανοίγει το Modal (Προσθήκη)
            var builderModalNew = document.getElementById('builderModal');
            if (builderModalNew) {
                builderModalNew.addEventListener('shown.bs.modal', function() {
                    var iframe = document.getElementById('builderIframeNew');
                    var textVal = document.getElementById('newMezeText').value;
                    if (window.newMezeEditor) {
                        textVal = window.newMezeEditor.getData();
                    } else if (window.newMezeEditor_cm) {
                        textVal = window.newMezeEditor_cm.getValue();
                    }
                    if (iframe && iframe.contentWindow) {
                        var builderInput = iframe.contentWindow.document.getElementById('rawInput');
                        if (builderInput && builderInput.value.trim() === '' && textVal.trim() !== '') {
                            // Αυτόματη φόρτωση ΜΟΝΟ αν το κείμενο έχει παραχθεί εξ ολοκλήρου από τον Builder
                            if (textVal.indexOf('AEPP_RAW_START') !== -1) {
                                builderInput.value = textVal;
                                if (typeof iframe.contentWindow.recoverFromHTML === 'function') {
                                    iframe.contentWindow.recoverFromHTML(true);
                                }
                            }
                        }
                    }
                });

                // Όταν κλείνει το Modal, αν ο Builder ενημέρωσε την textarea, περνάμε την αλλαγή στον Editor
                builderModalNew.addEventListener('hidden.bs.modal', function() {
                    var ta = document.getElementById('newMezeText');
                    if (window.newMezeEditor && ta.value !== window.newMezeEditor.getData()) {
                        window.newMezeEditor.setData(ta.value);
                    } else if (window.newMezeEditor_cm && ta.value !== window.newMezeEditor_cm.getValue()) {
                        window.newMezeEditor_cm.setValue(ta.value);
                    }
                });

                // Αλλαγή συμπεριφοράς του κουμπιού "Επιστροφή" μέσα στον Builder για να κλείνει απλώς το Modal
                var iframeNew = document.getElementById('builderIframeNew');
                if (iframeNew) {
                    iframeNew.addEventListener('load', function() {
                        try {
                            var doc = iframeNew.contentWindow.document;
                            var elements = doc.querySelectorAll('a, button');
                            for (var i = 0; i < elements.length; i++) {
                                var el = elements[i];
                                if (el.textContent.includes('Επιστροφή') || el.innerText.includes('Επιστροφή')) {
                                    el.removeAttribute('href');
                                    el.removeAttribute('onclick');
                                    el.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                        var bsModal = bootstrap.Modal.getInstance(builderModalNew);
                                        if (bsModal) bsModal.hide();
                                    }, true);
                                }
                            }
                        } catch (e) {
                            console.log('Builder iframe cross-origin error:', e);
                        }
                    });
                }
            }
            // JS για να μην κλείνει το dropdown όταν επιλέγεις checkboxes
            $(document).on('click', '.dropdown-menu', function(e) {
                e.stopPropagation();
            });

            // Φιλτράρισμα Τύπων στην αναζήτηση
            $('#typeSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.type-item').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            if (typeof autoSetDeadline === 'undefined') {
                window.autoSetDeadline = function(dateInputId, deadlineInputId) {
                    var dateVal = document.getElementById(dateInputId).value;
                    if (dateVal) {
                        var d = new Date(dateVal);
                        d.setDate(d.getDate() + 1); // +1 Ημέρα
                        var year = d.getFullYear();
                        var month = ('0' + (d.getMonth() + 1)).slice(-2);
                        var day = ('0' + d.getDate()).slice(-2);
                        document.getElementById(deadlineInputId).value = year + '-' + month + '-' + day + 'T03:00';
                    }
                }
            }

            if (typeof setToday === 'undefined') {
                window.setToday = function(dateInputId, deadlineInputId) {
                    var d = new Date();
                    var year = d.getFullYear();
                    var month = ('0' + (d.getMonth() + 1)).slice(-2);
                    var day = ('0' + d.getDate()).slice(-2);
                    document.getElementById(dateInputId).value = year + '-' + month + '-' + day;
                    autoSetDeadline(dateInputId, deadlineInputId);
                }
            }
        </script>
    <?php
    }

    /**
     * @param array $row
     */
    public function editMezedakiForm($row)
    {
        $db = new AdminDbHandler();
        $exerciseTypes = $db->getExerciseTypes();
        $selectedTypeIds = $db->getMezeTypeIds($row['mezeId']);
        $allGroups = $db->getGroups(isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "");
        $groupDeadlines = $db->getGroupDeadlinesForMeze($row['mezeId']);

        $mezeId = $row['mezeId'];
        $isLocked = (isset($row['isLocked']) && $row['isLocked'] == 1);

    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-3 gap-3">
                <h3 class="mb-0 text-center text-xl-start"><i class="fa fa-edit"></i> Επεξεργασία #<?php echo $row['mezeNumber']; ?></h3>

                <div class="d-flex flex-wrap justify-content-center justify-content-xl-end gap-1">
                    <a href="index.php?action=toggleMezeLock&amp;id=<?php echo $mezeId; ?>&amp;status=<?php echo $isLocked ? 0 : 1; ?>" class="btn btn-outline-<?php echo $isLocked ? 'success' : 'secondary'; ?> btn-sm" title="<?php echo $isLocked ? 'Ξεκλείδωμα' : 'Κλείδωμα'; ?>"><i class="fa fa-<?php echo $isLocked ? 'unlock' : 'lock'; ?>"></i></a>
                    <a href="index.php?action=previewMeze&amp;id=<?php echo $mezeId; ?>" target="_blank" class="btn btn-dark btn-sm" title="Προεπισκόπηση"><i class="fa fa-search"></i></a>
                    <a href="index.php?action=viewSubmissions&amp;id=<?php echo $mezeId; ?>" class="btn btn-info btn-sm" title="Απαντήσεις"><i class="fa fa-eye"></i></a>
                    <a href="index.php?action=deleteMezedaki&amp;id=<?php echo $mezeId; ?>" class="btn btn-danger btn-sm" title="Διαγραφή" onclick="return confirm('Διαγραφή;')"><i class="fa fa-trash"></i></a>

                    <a href="index.php?action=setMezeToday&id=<?php echo $mezeId; ?>&source=edit_page" class="btn btn-success btn-sm" title="Ορισμός ημερομηνίας εμφάνισης για Σήμερα (Προσοχή: Αποθηκεύεται κατευθείαν)"><i class="fa fa-calendar-check-o"></i> Σήμερα</a>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-users"></i> Deadlines
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 320px;">
                            <?php foreach ($allGroups as $g):
                                $hasDeadline = isset($groupDeadlines[$g['id']]);
                                $deadlineDate = $hasDeadline ? date('d/m H:i', strtotime($groupDeadlines[$g['id']])) : '';
                            ?>
                                <li>
                                    <form action="index.php?action=toggleGroupDeadline" method="post" class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                                        <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                        <input type="hidden" name="group_id" value="<?php echo $g['id']; ?>">
                                        <input type="hidden" name="source" value="edit_page">
                                        <span class="fw-bold me-auto text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($g['group_name']); ?>"><?php echo htmlspecialchars($g['group_name']); ?></span>
                                        <?php if ($hasDeadline): ?>
                                            <span class="badge bg-success me-2 shadow-sm"><i class="fa fa-clock-o"></i> <?php echo $deadlineDate; ?></span>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-sm <?php echo $hasDeadline ? 'btn-danger' : 'btn-success'; ?>" title="Προσοχή: Η αλλαγή αποθηκεύεται κατευθείαν"><?php echo $hasDeadline ? 'Ακύρωση' : 'Set'; ?></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-sm shadow-sm ms-2">
                        <i class="fa fa-arrow-left"></i> Λίστα
                    </a>
                </div>
            </div>
            <hr class="mt-0">
            <form action="index.php?action=updateMezedaki" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mezeId" value="<?php echo $row['mezeId']; ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Αριθμός</label>
                        <input type="number" name="mezeNumber" class="form-control" value="<?php echo $row['mezeNumber']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ημερομηνία Εμφάνισης</label>
                        <div class="input-group">
                            <input type="date" name="mezeDate" id="mezeDateEdit" class="form-control" value="<?php echo $row['mezeDate']; ?>" required onchange="autoSetDeadline('mezeDateEdit', 'solutionDateEdit')">
                            <button class="btn btn-success px-2" type="button" onclick="setToday('mezeDateEdit', 'solutionDateEdit')" title="Ορισμός ημερομηνίας εμφάνισης για Σήμερα"><i class="fa fa-calendar-check-o"></i></button>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Deadline Λύσης</label>
                        <?php
                        $timestamp = (!empty($row['solutionDate'])) ? strtotime($row['solutionDate']) : false;
                        if (!$timestamp || $timestamp <= 0 || date('Y', $timestamp) < 1980) {
                            $currentVal = date('Y-m-d\TH:i');
                        } else {
                            $currentVal = date('Y-m-d\TH:i', $timestamp);
                        }
                        ?>
                        <input type="datetime-local" name="solutionDate" id="solutionDateEdit" class="form-control" value="<?php echo $currentVal; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-danger">
                        <label class="switch-container text-danger font-weight-bold mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="isSos" value="1" id="isSosCheckEdit" <?php echo (isset($row['isSos']) && $row['isSos'] == 1) ? 'checked' : ''; ?>>
                            <span><i class="fa fa-fire"></i> Χαρακτηρισμός ως SOS (Τεχνική Πανελληνίων)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-primary">
                        <label class="switch-container text-primary font-weight-bold mb-2" style="cursor: pointer;">
                            <input type="checkbox" name="isPanhellenic" value="1" id="isPanCheckEdit" onchange="togglePanFieldsEdit(this)" <?php echo (isset($row['isPanhellenic']) && $row['isPanhellenic'] == 1) ? 'checked' : ''; ?>>
                            <span><i class="fa fa-university"></i> Θέμα Πανελληνίων Εξετάσεων</span>
                        </label>

                        <div id="panelliniesFieldsEdit" style="display: <?php echo (isset($row['isPanhellenic']) && $row['isPanhellenic'] == 1) ? 'block' : 'none'; ?>;" class="mt-3 p-3 bg-light border rounded">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Έτος</label>
                                    <input type="number" name="panYear" class="form-control" placeholder="π.χ. 2024" value="<?php echo $row['panYear']; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Θέμα</label>
                                    <select name="panThema" class="form-control">
                                        <option value="">--</option>
                                        <?php foreach (['A', 'B', 'G', 'D'] as $thema): ?>
                                            <option value="<?php echo $thema; ?>" <?php echo ($row['panThema'] == $thema) ? 'selected' : ''; ?>>Θέμα <?php echo $thema; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Είδος Εξέτασης</label>
                                    <select name="panExamType" class="form-control">
                                        <option value="Kanonikes" <?php echo ($row['panExamType'] == 'Kanonikes') ? 'selected' : ''; ?>>Κανονικές</option>
                                        <option value="Epanaliptikes" <?php echo ($row['panExamType'] == 'Epanaliptikes') ? 'selected' : ''; ?>>Επαναληπτικές</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Σχολείο</label>
                                    <select name="panSchoolType" class="form-control">
                                        <option value="Hmerisio" <?php echo ($row['panSchoolType'] == 'Hmerisio') ? 'selected' : ''; ?>>Ημερήσιο</option>
                                        <option value="Esperino" <?php echo ($row['panSchoolType'] == 'Esperino') ? 'selected' : ''; ?>>Εσπερινό</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm border-info">
                        <label class="font-weight-bold text-info mb-2"><i class="fa fa-book"></i> Πηγή Άσκησης (Βοήθημα)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Βιβλίο / Συγγραφέας</label>
                                <select name="sourceBook" class="form-control">
                                    <option value="">-- Καμία / Άλλη --</option>
                                    <option value="Κοψίνης 1" <?php echo (isset($row['sourceBook']) && $row['sourceBook'] == 'Κοψίνης 1') ? 'selected' : ''; ?>>Κοψίνης Τεύχος 1</option>
                                    <option value="Κοψίνης 2" <?php echo (isset($row['sourceBook']) && $row['sourceBook'] == 'Κοψίνης 2') ? 'selected' : ''; ?>>Κοψίνης Τεύχος 2</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Κεφάλαιο.Άσκηση (π.χ. 4.13)</label>
                                <input type="text" name="sourceExercise" class="form-control" placeholder="π.χ. 4.13" value="<?php echo isset($row['sourceExercise']) ? htmlspecialchars($row['sourceExercise']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold"><i class="fa fa-tags text-info"></i> Είδος Άσκησης / Τεχνικές</label>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start bg-white" type="button" id="editTypeDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Επιλογή Τεχνικών...
                        </button>
                        <div class="dropdown-menu p-3 w-100 shadow" aria-labelledby="editTypeDropdown" style="max-height: 350px; overflow-y: auto;">
                            <input type="text" class="form-control form-control-sm mb-2" id="editTypeSearch" placeholder="Αναζήτηση τεχνικής..." autocomplete="off">
                            <hr class="my-2">
                            <div class="row px-2">
                                <?php foreach ($exerciseTypes as $type): ?>
                                    <div class="col-md-4 col-6 mb-2 edit-type-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="edit_type_<?php echo $type['id']; ?>" name="exercise_types[]" value="<?php echo $type['id']; ?>" <?php echo in_array($type['id'], $selectedTypeIds) ? 'checked' : ''; ?> style="cursor:pointer;">
                                            <label class="form-check-label small" for="edit_type_<?php echo $type['id']; ?>" style="cursor:pointer; margin-left: 5px;">
                                                <?php echo $type['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Εκφώνηση (Κείμενο ή HTML)</label>
                        <div>
                            <button type="button" id="formatBtn_newMezeText" class="btn btn-outline-info btn-sm me-2 shadow-sm d-none" onclick="formatCM('newMezeEditor_cm')" title="Αυτόματη στοίχιση κώδικα">
                                <i class="fa fa-indent"></i> Format
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2 shadow-sm" onclick="toggleCKEditor('editMezeText', 'editMezeEditor', Base64UploadAdapterPluginEdit)" title="Εναλλαγή σε προβολή κώδικα HTML">
                                <i class="fa fa-code"></i> HTML
                            </button>
                            <!-- Κουμπί που ανοίγει το Modal του Builder (Edit) -->
                            <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#builderModalEdit">
                                <i class="fa fa-magic"></i> Γρήγορη Δημιουργία Κενών / Σ-Λ
                            </button>
                        </div>
                    </div>
                    <textarea name="mezeText" id="editMezeText" class="form-control" rows="5"><?php echo $row['mezeText']; ?></textarea>

                    <!-- Το Bootstrap Modal για τον AEPP Builder (Edit) -->
                    <div class="modal fade" id="builderModalEdit" tabindex="-1" aria-labelledby="builderModalEditLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title" id="builderModalEditLabel"><i class="fa fa-magic text-warning"></i> AEPP Builder</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <iframe src="../builder.html" id="builderIframeEdit" style="width: 100%; height: 80vh; border: none; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group card p-3 bg-white border-info shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold text-info mb-0"><i class="fa fa-lightbulb-o"></i> Οδηγίες / Hints (Προς Μαθητές)</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditor('editMezeHints', 'editHintsEditor', Base64UploadAdapterPluginEdit)">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="mezeHints" id="editMezeHints" class="form-control" rows="3" placeholder="Γράψτε εδώ μικρές βοήθειες ή hints..."><?php echo isset($row['mezeHints']) ? $row['mezeHints'] : ''; ?></textarea>
                    <small class="form-text text-muted">Αυτό το κείμενο θα εμφανίζεται στο Site των μαθητών με κουμπί "Χρειάζεσαι βοήθεια;".</small>
                </div>

                <div class="form-group card p-3 bg-white shadow-sm">
                    <label class="font-weight-bold">Εικόνα Εκφώνησης</label>
                    <?php if (!empty($row['mezeImage'])): ?>
                        <div class="mb-3 p-2 border rounded bg-light" style="max-width: 250px;">
                            <small class="text-muted">Τρέχουσα εικόνα:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeImage']; ?>" width="150" class="img-thumbnail mb-2" alt="Εικόνα Εκφώνησης">
                            <div class="mt-1">
                                <input type="checkbox" name="deleteMezeImage" value="1" id="delImgQ">
                                <label for="delImgQ" class="text-danger font-weight-bold" style="cursor:pointer; margin-left: 5px;">
                                    Διαγραφή υπάρχουσας
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="mezeImage" class="form-control-file">
                    <small class="form-text text-muted">Επιλέξτε αρχείο μόνο αν θέλετε να αλλάξετε την εικόνα.</small>
                </div>

                <div class="form-group card p-3 bg-white border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold text-success mb-0">Λύση (mezeSolution)</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditor('editMezeSolution', 'editSolutionEditor', Base64UploadAdapterPluginEdit)">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="mezeSolution" id="editMezeSolution" class="form-control mb-3" rows="5"><?php echo $row['mezeSolution']; ?></textarea>

                    <label class="font-weight-bold">Εικόνα Λύσης (Προαιρετικά)</label>
                    <?php if (!empty($row['mezeSolutionImage'])): ?>
                        <div class="mb-3 p-2 border rounded bg-light" style="max-width: 250px;">
                            <small class="text-muted">Τρέχουσα εικόνα λύσης:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeSolutionImage']; ?>" width="150" class="img-thumbnail border-success mb-2" alt="Εικόνα Λύσης">
                            <div class="mt-1">
                                <input type="checkbox" name="deleteMezeSolutionImage" value="1" id="delImgA">
                                <label for="delImgA" class="text-danger font-weight-bold" style="cursor:pointer; margin-left: 5px;">
                                    Διαγραφή υπάρχουσας
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="mezeSolutionImage" class="form-control-file">
                    <small class="form-text text-muted">Επιλέξτε αρχείο μόνο αν θέλετε να αλλάξετε την εικόνα λύσης.</small>
                </div>

                <div class="mt-4 row gap-2 gap-md-0">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow w-100">
                            <i class="fa fa-save"></i> Ενημέρωση
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="return_to_list" value="1" class="btn btn-success btn-block font-weight-bold shadow w-100">
                            <i class="fa fa-list"></i> Ενημέρωση & Επιστροφή
                        </button>
                    </div>
                    <div class="col-md-4">
                        <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-block shadow w-100">
                            <i class="fa fa-times"></i> Ακύρωση
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <script>
            class Base64UploadAdapterEdit {
                constructor(loader) {
                    this.loader = loader;
                }
                upload() {
                    return this.loader.file.then(file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const MAX_WIDTH = 800;
                                let width = img.width;
                                let height = img.height;
                                if (width > MAX_WIDTH) {
                                    height = Math.round((height * MAX_WIDTH) / width);
                                    width = MAX_WIDTH;
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, width, height);
                                ctx.drawImage(img, 0, 0, width, height);
                                resolve({
                                    default: canvas.toDataURL('image/jpeg', 0.85)
                                });
                            };
                            img.onerror = () => resolve({
                                default: e.target.result
                            });
                            img.src = e.target.result;
                        };
                        reader.onerror = error => reject(error);
                        reader.readAsDataURL(file);
                    }));
                }
                abort() {}
            }

            function Base64UploadAdapterPluginEdit(editor) {
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new Base64UploadAdapterEdit(loader);
            }

            window.editMezeEditor = null;
            window.editSolutionEditor = null;
            window.editHintsEditor = null;

            function initSmartEditorEdit(textareaId, editorVar, plugin) {
                const textarea = document.querySelector('#' + textareaId);
                if (!textarea) return;

                const val = textarea.value;
                const cmVar = editorVar + '_cm';
                const formatBtnId = 'formatBtn_' + textareaId;
                const formatBtn = document.getElementById(formatBtnId);

                // Έλεγχος για "ευαίσθητο" HTML κώδικα
                const hasRawHtml = val.includes('<pre') || val.includes('<table') || val.includes('AEPP_RAW_START') || val.includes('<div');

                if (hasRawHtml) {
                    window[cmVar] = CodeMirror.fromTextArea(textarea, {
                        mode: "xml",
                        htmlMode: true,
                        lineNumbers: true,
                        lineWrapping: true,
                        viewportMargin: Infinity
                    });
                    window[cmVar].on('change', function(cm) {
                        textarea.value = cm.getValue();
                    });
                    if (formatBtn) formatBtn.classList.remove('d-none');
                } else {
                    ClassicEditor.create(textarea, {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            textarea.value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }

            initSmartEditorEdit('editMezeText', 'editMezeEditor', Base64UploadAdapterPluginEdit);
            initSmartEditorEdit('editMezeSolution', 'editSolutionEditor', Base64UploadAdapterPluginEdit);
            initSmartEditorEdit('editMezeHints', 'editHintsEditor', Base64UploadAdapterPluginEdit);

            function formatCM(cmVar) {
                if (window[cmVar] && typeof html_beautify !== 'undefined') {
                    const unformatted = window[cmVar].getValue();
                    const formatted = html_beautify(unformatted, {
                        indent_size: 4,
                        wrap_line_length: 0,
                        unformatted: ['pre', 'code']
                    });
                    window[cmVar].setValue(formatted);
                }
            }

            function toggleCKEditor(textareaId, editorVar, plugin) {
                const cmVar = editorVar + '_cm';
                const formatBtnId = 'formatBtn_' + textareaId;
                const formatBtn = document.getElementById(formatBtnId);

                if (window[editorVar]) {
                    document.querySelector('#' + textareaId).value = window[editorVar].getData();
                    window[editorVar].destroy().then(() => {
                        window[editorVar] = null;

                        window[cmVar] = CodeMirror.fromTextArea(document.querySelector('#' + textareaId), {
                            mode: "xml",
                            htmlMode: true,
                            lineNumbers: true,
                            lineWrapping: true,
                            viewportMargin: Infinity
                        });
                        window[cmVar].on('change', function(cm) {
                            document.querySelector('#' + textareaId).value = cm.getValue();
                        });

                        if (formatBtn) formatBtn.classList.remove('d-none');
                    });
                } else {
                    if (window[cmVar]) {
                        document.querySelector('#' + textareaId).value = window[cmVar].getValue();
                        window[cmVar].toTextArea();
                        window[cmVar] = null;

                        if (formatBtn) formatBtn.classList.add('d-none');
                    }
                    ClassicEditor.create(document.querySelector('#' + textareaId), {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            document.querySelector('#' + textareaId).value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }

            function togglePanFieldsEdit(checkbox) {
                document.getElementById('panelliniesFieldsEdit').style.display = checkbox.checked ? 'block' : 'none';
            }

            // Αυτόματη φόρτωση της εκφώνησης στον Builder όταν ανοίγει το Modal (Επεξεργασία)
            var builderModalEdit = document.getElementById('builderModalEdit');
            if (builderModalEdit) {
                builderModalEdit.addEventListener('shown.bs.modal', function() {
                    var iframe = document.getElementById('builderIframeEdit');
                    var textVal = document.getElementById('editMezeText').value;
                    if (window.editMezeEditor) {
                        textVal = window.editMezeEditor.getData();
                    } else if (window.editMezeEditor_cm) {
                        textVal = window.editMezeEditor_cm.getValue();
                    }
                    if (iframe && iframe.contentWindow) {
                        var builderInput = iframe.contentWindow.document.getElementById('rawInput');
                        if (builderInput && builderInput.value.trim() === '' && textVal.trim() !== '') {
                            // Αυτόματη φόρτωση ΜΟΝΟ αν το κείμενο έχει παραχθεί εξ ολοκλήρου από τον Builder
                            if (textVal.indexOf('AEPP_RAW_START') !== -1) {
                                builderInput.value = textVal;
                                if (typeof iframe.contentWindow.recoverFromHTML === 'function') {
                                    iframe.contentWindow.recoverFromHTML(true);
                                }
                            }
                        }
                    }
                });

                builderModalEdit.addEventListener('hidden.bs.modal', function() {
                    var ta = document.getElementById('editMezeText');
                    if (window.editMezeEditor && ta.value !== window.editMezeEditor.getData()) {
                        window.editMezeEditor.setData(ta.value);
                    } else if (window.editMezeEditor_cm && ta.value !== window.editMezeEditor_cm.getValue()) {
                        window.editMezeEditor_cm.setValue(ta.value);
                    }
                });

                // Αλλαγή συμπεριφοράς του κουμπιού "Επιστροφή" μέσα στον Builder για να κλείνει απλώς το Modal
                var iframeEdit = document.getElementById('builderIframeEdit');
                if (iframeEdit) {
                    iframeEdit.addEventListener('load', function() {
                        try {
                            var doc = iframeEdit.contentWindow.document;
                            var elements = doc.querySelectorAll('a, button');
                            for (var i = 0; i < elements.length; i++) {
                                var el = elements[i];
                                if (el.textContent.includes('Επιστροφή') || el.innerText.includes('Επιστροφή')) {
                                    el.removeAttribute('href');
                                    el.removeAttribute('onclick');
                                    el.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                        var bsModal = bootstrap.Modal.getInstance(builderModalEdit);
                                        if (bsModal) bsModal.hide();
                                    }, true);
                                }
                            }
                        } catch (e) {
                            console.log('Builder iframe cross-origin error:', e);
                        }
                    });
                }
            }

            if (typeof autoSetDeadline === 'undefined') {
                window.autoSetDeadline = function(dateInputId, deadlineInputId) {
                    var dateVal = document.getElementById(dateInputId).value;
                    if (dateVal) {
                        var d = new Date(dateVal);
                        d.setDate(d.getDate() + 1); // +1 Ημέρα
                        var year = d.getFullYear();
                        var month = ('0' + (d.getMonth() + 1)).slice(-2);
                        var day = ('0' + d.getDate()).slice(-2);
                        document.getElementById(deadlineInputId).value = year + '-' + month + '-' + day + 'T03:00';
                    }
                }
            }

            if (typeof setToday === 'undefined') {
                window.setToday = function(dateInputId, deadlineInputId) {
                    var d = new Date();
                    var year = d.getFullYear();
                    var month = ('0' + (d.getMonth() + 1)).slice(-2);
                    var day = ('0' + d.getDate()).slice(-2);
                    document.getElementById(dateInputId).value = year + '-' + month + '-' + day;
                    autoSetDeadline(dateInputId, deadlineInputId);
                }
            }
        </script>
    <?php
    }

    /**
     * @param array $students
     * @param int|string $mezeId
     * @param int|string $displayNumber
     * @param array $existingGrades
     */
    public function showGradesForm($students, $mezeId, $displayNumber, $existingGrades = [])
    {
    ?>
        <div class="container mt-4 border p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="fa fa-pencil"></i> Βαθμολόγιο #<?php echo $displayNumber; ?></h3>
                <a href="index.php?action=listMezedakia" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Επιστροφή στη Λίστα
                </a>
            </div>
            <form action="index.php?action=saveGrades" method="post">
                <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Μαθητής</th>
                            <th>Βαθμός (0-20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student):
                            $stId = $student['studentId'];
                            $currentGrade = $existingGrades[$stId] ?? "";
                        ?>
                            <tr>
                                <td><?php echo $student['name'] . " " . $student['lastName']; ?></td>
                                <td><input type="number" name="grades[<?php echo $stId; ?>]" class="form-control" step="0.1" value="<?php echo $currentGrade; ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success">Αποθήκευση</button>
            </form>
        </div>
    <?php
    }

    /**
     * @param array $submissions
     * @param array $students
     * @param array $mezeData
     * @param array|mysqli_result $allMezedakia
     * @param array $existingGrades
     */
    public function showSubmissionsForGrading($submissions, $students, $mezeData, $allMezedakia, $existingGrades = [])
    {
        $dbHandler = new AdminDbHandler(); // Για έλεγχο αδειών και μαζική βαθμολόγηση
        $mezeNumber = (int)$mezeData['mezeNumber'];
        $mezeId = $mezeData['mezeId'];
        $isSos = (isset($mezeData['isSos']) && $mezeData['isSos'] == 1);
        $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";
        $isExpired = (strtotime($mezeData['solutionDate'] ?? '') < time());

        $pendingSubmissions = [];
        $gradedSubmissions = [];

        // 1. Προετοιμασία ευρετηρίων για ταχύτητα
        $submissionsByStudent = [];
        if (is_array($submissions)) {
            foreach ($submissions as $sub) {
                $submissionsByStudent[(int)$sub['student_id']] = $sub;
            }
        }

        $gradesByStudent = [];
        if (is_array($existingGrades)) {
            foreach ($existingGrades as $eg) {
                $egL = array_change_key_case($eg, CASE_LOWER);
                $gradesByStudent[(int)$egL['student_id']] = $egL;
            }
        }

        // 2. Διαχωρισμός μαθητών σε "Προς Βαθμολόγηση" και "Ολοκληρωμένες"
        foreach ($students as $student) {
            $stId = (int)$student['studentId'];
            $subData = isset($submissionsByStudent[$stId]) ? $submissionsByStudent[$stId] : null;
            $gradeData = isset($gradesByStudent[$stId]) ? $gradesByStudent[$stId] : null;

            $isGraded = false;
            $isResubmission = false;
            if ($gradeData) {
                $isGraded = true; // Θεωρούμε τον μαθητή βαθμολογημένο εφόσον υπάρχει εγγραφή βαθμού

                // Αν υπάρχει υποβολή πιο πρόσφατη από τη βαθμολόγηση, επιστρέφει στα εκκρεμή
                if ($subData && !empty($gradeData['updated_at'])) {
                    $updTime = strtotime($gradeData['updated_at']);
                    $subTime = strtotime($subData['submission_date']);
                    if ($updTime > 0 && $subTime > 0 && $subTime > $updTime) {
                        $isGraded = false;
                        $isResubmission = true;
                    }
                }
            }

            if ($isGraded) {
                $gradedSubmissions[] = ['sub' => $subData, 'grade' => $gradeData, 'student' => $student];
            } else {
                // Περνάμε και το grade (αν υπάρχει) για να το προφορτώσουμε στο UI των εκκρεμών
                $pendingSubmissions[] = ['sub' => $subData, 'grade' => $gradeData, 'student' => $student, 'isResubmission' => $isResubmission];
            }
        }
    ?>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary mb-0">
                    <i class="fa fa-mortar-board"></i> Απαντήσεις #<?php echo $mezeNumber; ?>
                    <?php if ($isSos): ?>
                        <span class="badge bg-danger ms-2 shadow-sm pulse-sos"><i class="fa fa-fire"></i> SOS</span>
                    <?php endif; ?>
                </h3>
                <a href="index.php?action=listMezedakia" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Επιστροφή στη Λίστα
                </a>
            </div>

            <!-- ΕΝΟΤΗΤΑ: ΠΡΟΣ ΒΑΘΜΟΛΟΓΗΣΗ -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="text-danger fw-bold mb-0"><i class="fa fa-clock-o"></i> Προς Βαθμολόγηση (<?php echo count($pendingSubmissions); ?>)</h5>
                    <?php if ($isExpired):
                        $zeroStudents = $dbHandler->getStudentsWithZeroGrade($mezeId, $userYear);
                    ?>
                        <div class="btn-group">
                            <a href="index.php?action=massGradeZero&id=<?php echo $mezeId; ?>"
                                class="btn btn-outline-danger btn-sm shadow-sm"
                                onclick="return confirm('ΠΡΟΣΟΧΗ: Όλοι οι μαθητές που δεν έχουν υποβάλει εργασία ΚΑΙ δεν έχουν ήδη βαθμό, θα πάρουν 0. Συνέχεια;')">
                                <i class="fa fa-battery-empty"></i> 1. Μαζικό 0
                            </a>
                            <a href="index.php?action=massEmailZero&id=<?php echo $mezeId; ?>" class="btn btn-danger btn-sm shadow-sm <?php echo empty($zeroStudents) ? 'disabled' : ''; ?>" onclick="return confirm('Θέλετε να στείλετε email ενημέρωσης σε όλους όσους έχουν βαθμό 0;')">
                                <i class="fa fa-envelope"></i> 2. Emails σε όσους έχουν 0
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <hr class="border-danger">
                <?php foreach ($pendingSubmissions as $item):
                    $sub = $item['sub'];
                    $st = $item['student'];
                    $stId = $st['studentId'];
                    $currGrade = isset($item['grade']['grade_value']) ? $item['grade']['grade_value'] : '';
                    $currComm = isset($item['grade']['teacher_comments']) ? htmlspecialchars($item['grade']['teacher_comments']) : '';
                    $isAllowed = $dbHandler->isSubmissionAllowed($stId, $mezeId, $userYear);
                    $isResubmission = isset($item['isResubmission']) && $item['isResubmission'];

                    // Αν είναι επανυποβολή, αγνοούμε (καθαρίζουμε) τον παλιό βαθμό και τα σχόλια
                    if ($isResubmission) {
                        $currGrade = '';
                        $currComm = '';
                    }

                    $highlightData = $this->getHighlightedStudentAnswer($sub['student_text'] ?? '', $mezeData['mezeSolution'] ?? '');
                    $highlightedHtml = $highlightData['html'];
                    $suggestedGrade = $highlightData['autoGrade'];
                    $displayGrade = ($suggestedGrade !== '') ? $suggestedGrade : $currGrade;
                ?>
                    <div class="card mb-4 shadow-sm border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                            <strong>
                                <?php echo $st['name'] . " " . $st['lastName']; ?>
                                <?php if ($isResubmission): ?>
                                    <span class="badge bg-warning text-dark ms-2"><i class="fa fa-refresh"></i> Επανυποβολή</span>
                                <?php endif; ?>
                            </strong>
                            <small><?php echo ($sub) ? date('d/m/Y H:i', strtotime($sub['submission_date'])) : '<span class="badge bg-warning text-dark">Δεν δόθηκε απάντηση</span>'; ?></small>
                        </div>
                        <div class="card-body">
                            <?php if ($sub): ?>
                                <div class="row">
                                    <div class="col-md-<?php echo (!empty($mezeData['mezeSolution']) || !empty($mezeData['mezeSolutionImage'])) ? '6' : '12'; ?>">
                                        <h6 class="text-muted small fw-bold">Απάντηση Μαθητή:</h6>
                                        <div class="small bg-light p-2 border rounded" style="height: 250px; min-height: 100px; overflow-y: auto; resize: vertical;">
                                            <?php echo $highlightedHtml; ?>
                                        </div>
                                        <div class="row mb-3">
                                            <?php foreach (['file1', 'file2', 'file3'] as $f): if (!empty($sub[$f])): ?>
                                                    <div class="col-md-4 mb-2">
                                                        <a href="../uploads/submissions/<?php echo $sub[$f]; ?>" target="_blank" class="btn btn-sm btn-block btn-outline-primary text-truncate w-100">
                                                            <i class="fa fa-file-image-o"></i> <?php echo $sub[$f]; ?>
                                                        </a>
                                                    </div>
                                            <?php endif;
                                            endforeach; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($mezeData['mezeSolution']) || !empty($mezeData['mezeSolutionImage'])): ?>
                                        <div class="col-md-6">
                                            <h6 class="text-success small fw-bold">Σωστή Λύση:</h6>
                                            <div class="small bg-white p-2 border border-success rounded" style="height: 250px; min-height: 100px; overflow-y: auto; resize: vertical;">
                                                <?php echo $mezeData['mezeSolution']; ?>
                                                <?php if (!empty($mezeData['mezeSolutionImage'])): ?>
                                                    <div class="mt-2"><a href="../images/mezedakia/<?php echo $mezeData['mezeSolutionImage']; ?>" target="_blank" class="btn btn-sm btn-outline-success w-100"><i class="fa fa-image"></i> Προβολή Εικόνας Λύσης</a></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 small d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-exclamation-triangle"></i> Εκκρεμεί υποβολή.</span>
                                    <form action="index.php?action=giveExtension" method="post" class="form-inline m-0 d-flex">
                                        <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                        <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                        <input type="number" name="hours" class="form-control form-control-sm me-1" style="width: 50px;" value="24">
                                        <button type="submit" class="btn btn-xs <?php echo $isAllowed ? 'btn-success' : 'btn-outline-danger'; ?> py-0 px-2" style="font-size:0.7rem;">
                                            <i class="fa fa-<?php echo $isAllowed ? 'unlock' : 'lock'; ?>"></i> <?php echo $isAllowed ? "Ανοιχτή" : "Άνοιγμα"; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <form action="index.php?action=quickGrade" method="post" class="bg-light p-2 rounded border mt-2">
                                <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                <div class="d-flex align-items-start">
                                    <input type="number" name="grade" step="0.5" class="form-control form-control-sm me-2" style="width:80px" placeholder="Βαθμός" value="<?php echo $displayGrade; ?>" required>
                                    <div style="flex:1;">
                                        <textarea name="teacher_comments" id="comm_<?php echo $stId; ?>" class="form-control form-control-sm mb-1" rows="2" placeholder="Σχόλια..."><?php echo $currComm; ?></textarea>
                                        <div class="d-flex flex-wrap">
                                            <button type="button" class="btn btn-outline-secondary btn-mini me-1 mb-1" onclick="document.getElementById('comm_<?php echo $stId; ?>').value='Εξαιρετική δουλειά! Μπράβο.';">👏 Μπράβο</button>
                                            <button type="button" class="btn btn-outline-secondary btn-mini me-1 mb-1" onclick="document.getElementById('comm_<?php echo $stId; ?>').value='Πολύ καλή προσπάθεια, πρόσεξε λίγο περισσότερο τη σύνταξη.';">✍️ Σύνταξη</button>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <button type="submit" class="btn btn-sm btn-success px-3 ms-2 mb-1">OK</button>
                                        <button type="button" class="btn btn-sm btn-primary ms-2" onclick="quickGrade20(this)" title="Γρήγορη Βαθμολόγηση με 20/20">
                                            <i class="fa fa-star"></i> 20
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ΕΝΟΤΗΤΑ: ΟΛΟΚΛΗΡΩΜΕΝΕΣ -->
            <div class="mt-5">
                <h5 class="text-success fw-bold"><i class="fa fa-check-circle"></i> Ολοκληρωμένες (<?php echo count($gradedSubmissions); ?>)</h5>
                <hr class="border-success">
                <div class="list-group shadow-sm">
                    <?php foreach ($gradedSubmissions as $item):
                        $sub = $item['sub'];
                        $grade = $item['grade'];
                        $st = $item['student'];
                        $stId = $st['studentId'];
                        $isAllowed = $dbHandler->isSubmissionAllowed($stId, $mezeId, $userYear);

                        $highlightData = $this->getHighlightedStudentAnswer($sub['student_text'] ?? '', $mezeData['mezeSolution'] ?? '');
                        $highlightedHtml = $highlightData['html'];
                    ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" style="cursor: pointer; border-left: 5px solid #28a745;" data-bs-toggle="collapse" data-bs-target="#editRow<?php echo $stId; ?>">
                            <div>
                                <span class="fw-bold me-3"><?php echo $st['name'] . " " . $st['lastName']; ?></span>
                                <span class="badge bg-success px-2 py-1">Βαθμός: <?php echo $grade['grade_value']; ?></span>
                            </div>
                            <i class="fa fa-chevron-down text-muted"></i>
                        </div>
                        <div id="editRow<?php echo $stId; ?>" class="collapse p-3 border border-top-0 mb-2 bg-light shadow-sm">
                            <?php if ($sub): ?>
                                <div class="row">
                                    <div class="col-md-<?php echo (!empty($mezeData['mezeSolution']) || !empty($mezeData['mezeSolutionImage'])) ? '6' : '12'; ?>">
                                        <h6 class="fw-bold text-muted small text-uppercase">Απάντηση:</h6>
                                        <div class="small bg-white p-2 border rounded" style="height: 200px; min-height: 100px; overflow-y: auto; resize: vertical;">
                                            <?php echo $highlightedHtml; ?>
                                        </div>
                                        <div class="row mb-2">
                                            <?php foreach (['file1', 'file2', 'file3'] as $f): if (!empty($sub[$f])): ?>
                                                    <div class="col-md-4"><a href="../uploads/submissions/<?php echo $sub[$f]; ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-100 text-truncate"><i class="fa fa-image"></i> <?php echo $sub[$f]; ?></a></div>
                                            <?php endif;
                                            endforeach; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($mezeData['mezeSolution']) || !empty($mezeData['mezeSolutionImage'])): ?>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-success small text-uppercase">Σωστή Λύση:</h6>
                                            <div class="small bg-white p-2 border border-success rounded" style="height: 200px; min-height: 100px; overflow-y: auto; resize: vertical;">
                                                <?php echo $mezeData['mezeSolution']; ?>
                                                <?php if (!empty($mezeData['mezeSolutionImage'])): ?>
                                                    <div class="mt-2"><a href="../images/mezedakia/<?php echo $mezeData['mezeSolutionImage']; ?>" target="_blank" class="btn btn-sm btn-outline-success w-100"><i class="fa fa-image"></i> Προβολή Εικόνας Λύσης</a></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-end mt-2 border-top pt-3">
                                <form action="index.php?action=quickGrade" method="post" class="mb-0">
                                    <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                    <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                    <div class="d-flex">
                                        <input type="number" name="grade" step="0.5" class="form-control form-control-sm me-2" style="width:70px" value="<?php echo $grade['grade_value']; ?>">
                                        <input type="text" name="teacher_comments" class="form-control form-control-sm me-2" value="<?php echo htmlspecialchars($grade['teacher_comments']); ?>">
                                        <button type="submit" class="btn btn-sm btn-info px-3 shadow-sm">Ενημέρωση</button>
                                    </div>
                                </form>

                                <form action="index.php?action=giveExtension" method="post" class="form-inline m-0 d-flex">
                                    <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                    <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                    <input type="number" name="hours" class="form-control form-control-sm me-1 shadow-sm" style="width: 55px;" value="24" title="Ώρες παράτασης">
                                    <button type="submit" class="btn btn-sm <?php echo $isAllowed ? 'btn-success' : 'btn-outline-danger'; ?> py-1 px-2 shadow-sm">
                                        <i class="fa fa-<?php echo $isAllowed ? 'unlock' : 'lock'; ?>"></i> <?php echo $isAllowed ? "Ανοιχτή" : "Παράταση"; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
            function quickGrade20(buttonEl) {
                const form = buttonEl.closest('form');
                if (form) {
                    const gradeInput = form.querySelector('input[name="grade"]');
                    const commentTextarea = form.querySelector('textarea[name="teacher_comments"]');

                    if (gradeInput) {
                        gradeInput.value = 20;
                    }
                    if (commentTextarea && commentTextarea.value.trim() === '') {
                        commentTextarea.value = 'Εξαιρετική δουλειά! Μπράβο.';
                    }

                    // Αποθήκευση της θέσης κύλισης (scroll)
                    sessionStorage.setItem('mezeScrollPos', window.scrollY);

                    buttonEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                    buttonEl.disabled = true;
                    form.submit();
                }
            }

            function markBlankCorrect(btn, points) {
                const container = btn.closest('.card-body') || btn.closest('.collapse');
                if (container) {
                    const gradeInput = container.querySelector('input[name="grade"]');
                    if (gradeInput) {
                        let currentVal = parseFloat(gradeInput.value) || 0;
                        let newVal = currentVal + points;
                        if (newVal > 20) newVal = 20;
                        gradeInput.value = (Math.round(newVal * 2) / 2).toFixed(1).replace('.0', '');

                        // Οπτικό εφέ ανανέωσης στον βαθμό
                        gradeInput.style.transition = 'background-color 0.3s';
                        gradeInput.style.backgroundColor = '#d4edda';
                        setTimeout(() => {
                            gradeInput.style.backgroundColor = '';
                        }, 800);
                    }
                }

                const row = btn.closest('.wrong-answer-row');
                if (row) {
                    // Αποθήκευση της αρχικής κατάστασης αν δεν έχει αποθηκευτεί
                    if (typeof row.dataset.originalBorder === 'undefined') {
                        row.dataset.originalBorder = row.classList.contains('border-warning') ? 'border-warning' : (row.classList.contains('border-danger') ? 'border-danger' : '');
                        const icon = row.querySelector('.wrong-icon');
                        if (icon) {
                            row.dataset.originalIconBg = icon.classList.contains('bg-warning') ? 'bg-warning' : 'bg-danger';
                            row.dataset.originalIconText = icon.classList.contains('text-dark') ? 'text-dark' : '';
                            row.dataset.originalIconHtml = icon.innerHTML;
                            row.dataset.originalColor = icon.style.color || '';
                        }
                    }

                    const icon = row.querySelector('.wrong-icon');
                    if (icon) {
                        if (icon.classList.contains('badge')) {
                            icon.innerHTML = '<i class="fa fa-check"></i> ' + icon.textContent.trim();
                            icon.classList.remove('bg-danger', 'bg-warning', 'text-dark');
                            icon.classList.add('bg-success', 'text-white');
                        } else {
                            icon.innerHTML = '✔';
                            icon.style.color = '#198754';
                        }
                    }
                    const hint = row.querySelector('.correct-hint');
                    if (hint) {
                        hint.style.textDecoration = 'line-through';
                        hint.style.opacity = '0.5';
                    }
                    row.classList.remove('border-danger', 'border-warning');
                    row.classList.add('border-success');

                    // Εναλλαγή Κουμπιών
                    btn.classList.add('d-none');
                    const undoBtn = row.querySelector('.btn-undo-correct');
                    if (undoBtn) undoBtn.classList.remove('d-none');
                }
            }

            function undoBlankCorrect(btn, points) {
                const container = btn.closest('.card-body') || btn.closest('.collapse');
                if (container) {
                    const gradeInput = container.querySelector('input[name="grade"]');
                    if (gradeInput) {
                        let currentVal = parseFloat(gradeInput.value) || 0;
                        let newVal = currentVal - points;
                        if (newVal < 0) newVal = 0;
                        gradeInput.value = (Math.round(newVal * 2) / 2).toFixed(1).replace('.0', '');

                        gradeInput.style.transition = 'background-color 0.3s';
                        gradeInput.style.backgroundColor = '#f8d7da';
                        setTimeout(() => {
                            gradeInput.style.backgroundColor = '';
                        }, 800);
                    }
                }

                const row = btn.closest('.wrong-answer-row');
                if (row) {
                    const icon = row.querySelector('.wrong-icon');
                    if (icon && typeof row.dataset.originalIconHtml !== 'undefined') {
                        icon.innerHTML = row.dataset.originalIconHtml;
                        icon.classList.remove('bg-success', 'text-white');
                        if (row.dataset.originalIconBg) icon.classList.add(row.dataset.originalIconBg);
                        if (row.dataset.originalIconText) icon.classList.add(row.dataset.originalIconText);
                        if (row.dataset.originalColor) icon.style.color = row.dataset.originalColor;
                    }
                    const hint = row.querySelector('.correct-hint');
                    if (hint) {
                        hint.style.textDecoration = '';
                        hint.style.opacity = '1';
                    }
                    row.classList.remove('border-success');
                    if (row.dataset.originalBorder) {
                        row.classList.add(row.dataset.originalBorder);
                    }

                    // Εναλλαγή Κουμπιών
                    btn.classList.add('d-none');
                    const markBtn = row.querySelector('.btn-mark-correct');
                    if (markBtn) markBtn.classList.remove('d-none');
                }
            }

            // Αποθήκευση της θέσης του scroll και για τα κανονικά κουμπιά (OK / Ενημέρωση)
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('mezeScrollPos', window.scrollY);
                });
            });

            // Επαναφορά του scroll στο ίδιο σημείο μόλις φορτώσει η σελίδα (ή κατά την επιστροφή με το Back)
            document.addEventListener('DOMContentLoaded', function() {
                const scrollPos = sessionStorage.getItem('mezeScrollPos');
                if (scrollPos) {
                    setTimeout(() => {
                        window.scrollTo(0, parseInt(scrollPos));
                    }, 50);
                    sessionStorage.removeItem('mezeScrollPos');
                }
            });
        </script>
    <?php
    }

    /**
     * @param string $studentName
     * @param int|string $mezeNumber
     * @param float|string $grade
     * @param string $comments
     * @param float|string $average
     * @param int|string $mezeId
     * @param array|null $studentData
     */
    public function showPrintableReport($studentName, $mezeNumber, $grade, $comments, $average, $mezeId, $studentData = null)
    {
        $email = $studentData['email'] ?? '';
        $phone = preg_replace('/[^0-9]/', '', $studentData['phone'] ?? '');
        if (strlen($phone) == 10) $phone = "30" . $phone;

        $waMessage = "Γεια σου! Η βαθμολογία σου στο Μεζεδάκι #$mezeNumber είναι: $grade/20.\nΣχόλια: $comments\nΟ τρέχων Μέσος Όρος σου είναι: " . number_format($average, 2);

        // Προετοιμασία HTML περιεχομένου για το Email
        $htmlEmail = "
        <div style='font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; border-radius:10px; max-width:600px;'>
            <h2 style='color:#007bff;'>Ενημέρωση Βαθμολογίας (ΑΕΠΠ)</h2>
            <p>Γεια σου <b>{$studentData['name']}</b>,</p>
            <p>Η βαθμολογία σου στο <b>Μεζεδάκι #$mezeNumber</b> καταχωρήθηκε:</p>
            <div style='background:#f8f9fa; padding:15px; border-radius:5px; margin:15px 0;'>
                <p style='margin:5px 0;'><b>Βαθμός:</b> <span style='color:#dc3545; font-size:1.2em;'>$grade/20</span></p>
                <div style='margin:5px 0;'><b>Σχόλια:</b><br><div style='white-space: pre-wrap; font-style: italic; padding-top: 5px;'>$comments</div></div>
                <p style='margin:5px 0;'><b>Τρέχων Μ.Ο.:</b> " . number_format($average, 2) . "</p>
            </div>
            <p>Καλή συνέχεια στη μελέτη σου!</p>
        </div>";
    ?>
        <div class="container mt-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h3 class="mb-0"><i class="fa fa-file-text-o"></i> Αναφορά Προόδου</h3>
                </div>
                <div class="card-body p-5 bg-white" id="printableReport">
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5 class="text-muted small text-uppercase mb-1">Μαθητής</h5>
                            <h4 class="fw-bold"><?php echo $studentName; ?></h4>
                        </div>
                        <div class="col-6 text-end">
                            <h5 class="text-muted small text-uppercase mb-1">Ημερομηνία</h5>
                            <h4 class="fw-bold"><?php echo date('d/m/Y'); ?></h4>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center my-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted small text-uppercase">Μεζεδάκι</h6>
                                <h2 class="mb-0">#<?php echo $mezeNumber; ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted small text-uppercase">Βαθμολογία</h6>
                                <h2 class="text-danger mb-0"><?php echo $grade; ?><span class="small text-muted">/20</span></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted small text-uppercase">Μέσος Όρος</h6>
                                <h2 class="text-primary mb-0"><?php echo number_format($average, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded mb-4" style="background-color: #fffaf0; min-height: 100px;">
                        <h6 class="text-muted small text-uppercase mb-2"><i class="fa fa-commenting-o"></i> Σχόλια Καθηγητή</h6>
                        <p class="lead italic mb-0" style="white-space: pre-wrap;"><?php echo $comments ?: "<i>Δεν καταχωρήθηκαν σχόλια.</i>"; ?></p>
                    </div>
                </div>

                <div class="card-footer bg-light p-4 text-center d-print-none">
                    <button onclick="window.print();" class="btn btn-secondary me-2 shadow-sm"><i class="fa fa-print"></i> Εκτύπωση</button>

                    <?php if (!empty($phone)): ?>
                        <a href="https://wa.me/<?php echo $phone; ?>?text=<?php echo urlencode($waMessage); ?>" target="_blank" class="btn btn-success me-2 shadow-sm">
                            <i class="fa fa-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($email)): ?>
                        <!-- Φόρμα για Άμεση Αποστολή Email (χωρίς Modal) -->
                        <form action="index.php?action=sendReportEmail" method="POST" class="d-inline">
                            <input type="hidden" name="email" value="<?php echo $email; ?>">
                            <input type="hidden" name="subject" value="Βαθμολογία: Μεζεδάκι #<?php echo $mezeNumber; ?>">
                            <input type="hidden" name="html_message" value="<?php echo htmlspecialchars($htmlEmail); ?>">
                            <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                            <button type="submit" class="btn btn-outline-danger me-2 shadow-sm" onclick="return confirm('Αποστολή email χωρίς προεπισκόπηση;')"><i class="fa fa-paper-plane-o"></i> Άμεση Αποστολή</button>
                        </form>

                        <button type="button" class="btn btn-danger me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#emailPreviewModal">
                            <i class="fa fa-eye"></i> Προεπισκόπηση & Αποστολή
                        </button>

                        <!-- Modal για Προεπισκόπηση Email -->
                        <div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg shadow">
                                <div class="modal-content border-0">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="emailPreviewLabel"><i class="fa fa-eye"></i> Προεπισκόπηση Email</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body bg-light text-start">
                                        <!-- Προσθήκη contenteditable για άμεση επεξεργασία -->
                                        <div class="p-4 bg-white border rounded shadow-sm" id="editableEmailContent" contenteditable="true" style="outline: 2px dashed #ffc107; cursor: text; min-height: 200px;">
                                            <?php echo $htmlEmail; ?>
                                        </div>
                                        <div class="alert alert-warning mt-3 py-2 small">
                                            <i class="fa fa-pencil"></i> <strong>Επεξεργασία:</strong> Μπορείτε να αλλάξετε τα σχόλια ή το κείμενο κάνοντας κλικ απευθείας πάνω στην προεπισκόπηση.
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                                        <button type="button" class="btn btn-outline-warning" onclick="resetEmailContent()"><i class="fa fa-undo"></i> Επαναφορά</button>
                                        <form action="index.php?action=sendReportEmail" method="POST" class="d-inline" onsubmit="return syncEmailContent()">
                                            <input type="hidden" name="email" value="<?php echo $email; ?>">
                                            <input type="hidden" name="subject" value="Βαθμολογία: Μεζεδάκι #<?php echo $mezeNumber; ?>">
                                            <input type="hidden" name="html_message" id="hiddenHtmlMessage" value="<?php echo htmlspecialchars($htmlEmail); ?>">
                                            <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                            <button type="submit" class="btn btn-danger"><i class="fa fa-paper-plane"></i> Επιβεβαίωση & Αποστολή</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <script>
                        // Αποθήκευση του αρχικού HTML για τη λειτουργία επαναφοράς
                        const originalEmailHtml = <?php echo json_encode($htmlEmail); ?>;

                        // Λειτουργία συγχρονισμού του επεξεργασμένου κειμένου με τη φόρμα
                        function syncEmailContent() {
                            const editedContent = document.getElementById('editableEmailContent').innerHTML;
                            document.getElementById('hiddenHtmlMessage').value = editedContent;
                            return true;
                        }

                        // Λειτουργία επαναφοράς στο αρχικό κείμενο
                        function resetEmailContent() {
                            if (confirm('Θέλετε να επαναφέρετε το αρχικό κείμενο; Όλες οι αλλαγές σας θα χαθούν.')) {
                                document.getElementById('editableEmailContent').innerHTML = originalEmailHtml;
                            }
                        }
                    </script>

                    <a href="index.php?action=listMezedakia" class="btn btn-primary shadow-sm">Επιστροφή</a>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * @param array $types
     */
    public function manageExerciseTypesForm($types)
    {
    ?>
        <div class="container mt-4">
            <h3>Διαχείριση Τεχνικών</h3>
            <form action="index.php?action=save_exercise_type" method="post" class="d-flex mb-3">
                <input type="text" name="type_name" class="form-control me-2" placeholder="Νέα τεχνική" required>
                <button type="submit" class="btn btn-success">Προσθήκη</button>
            </form>
            <table class="table table-bordered">
                <?php foreach ($types as $t): ?>
                    <tr>
                        <td><?php echo $t['name']; ?></td>
                        <td><a href="index.php?action=delete_exercise_type&amp;id=<?php echo $t['id']; ?>" class="btn btn-danger btn-sm">Διαγραφή</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php
    }

    /**
     * @param array $requests
     * @param array $students
     */
    public function listExtensionRequests($requests, $students)
    {
    ?>
        <div class="container mt-4">
            <h3 class="text-danger">Αιτήματα Παράτασης</h3>
            <table class="table table-bordered bg-white">
                <thead>
                    <tr>
                        <th>Μαθητής</th>
                        <th>Μεζεδάκι</th>
                        <th>Ενέργεια</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r):
                        $name = "ID: " . $r['student_id'];
                        foreach ($students as $s) if ($s['studentId'] == $r['student_id']) $name = $s['name'] . " " . $s['lastName'];
                    ?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td>#<?php echo $r['mezeNumber']; ?></td>
                            <td>
                                <form action="index.php?action=processExtension" method="POST" class="d-flex">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $r['student_id']; ?>">
                                    <input type="hidden" name="meze_id" value="<?php echo $r['meze_id']; ?>">
                                    <input type="number" name="hours" class="form-control form-control-sm w-25 me-2" value="<?php echo $r['requested_hours']; ?>">
                                    <button type="submit" name="approve" class="btn btn-success btn-sm me-1">OK</button>
                                    <button type="submit" name="reject" class="btn btn-danger btn-sm">X</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    /**
     * @param mysqli_result|bool|array $result
     * @param AdminDbHandler $dbHandler
     */
    public function mezeBank($result, $dbHandler)
    {
        $allTypes = $dbHandler->getExerciseTypes();
        $typeMap = [];
        foreach ($allTypes as $t) {
            $typeMap[$t['id']] = $t['name'];
        }
    ?>
        <div class="container mt-4 mb-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h3 class="mb-0 text-dark fw-bold text-center text-md-start"><i class="fa fa-archive text-warning"></i> Τράπεζα Μεζεδακίων (Κατάλογος)</h3>
                <div class="w-100" style="max-width: 400px;">
                    <input type="text" id="bankFilter" class="form-control form-control-lg shadow-sm w-100" placeholder="Αναζήτηση (λέξη-κλειδί, έτος, θέμα)...">
                </div>
            </div>

            <div class="row" id="bankContainer">
                <?php
                if ($result && $result->num_rows > 0) {
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) {
                        $mezeId = $row['mezeId'];

                        // Clean text for preview
                        $cleanText = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/si', '', $row['mezeText']);
                        $cleanText = str_replace(['<br>', '<br/>', '</p>', '</div>', 'AEPP_RAW_START', 'AEPP_RAW_END', '&nbsp;'], ' ', $cleanText);
                        $cleanText = strip_tags($cleanText);
                        $cleanText = preg_replace('/\s+/u', ' ', $cleanText);

                        // Αφαίρεση τυποποιημένων επικεφαλίδων Πανελλαδικών (με ανοχή στους τόνους πεζών/κεφαλαίων)
                        // 1. Αφαιρεί ολόκληρο το κατεβατό "ΠΑΝΕΛΛΑΔΙΚΕΣ ΕΞΕΤΑΣΕΙΣ ... ΑΝΑΠΤΥΞΗ ΕΦΑΡΜΟΓΩΝ..." (ό,τι και αν μεσολαβεί)
                        $cleanText = preg_replace('/(ΕΠΑΝΑΛΗΠΤΙΚ[ΕΈ]Σ\s+)?ΠΑΝΕΛΛΑΔΙΚ[ΕΈ]Σ\s+ΕΞΕΤ[ΑΆ]ΣΕΙΣ.*?(ΑΝ[ΑΆ]ΠΤΥΞΗ\s+ΕΦΑΡΜΟΓ[ΩΏ]Ν\s+ΣΕ\s+ΠΡΟΓΡΑΜΜΑΤΙΣΤΙΚ[ΟΌ]\s+ΠΕΡΙΒ[ΑΆ]ΛΛΟΝ|ΠΛΗΡΟΦΟΡΙΚ[ΗΉ](?:\s+ΠΡΟΣΑΝΑΤΟΛΙΣΜΟ[ΥΎ])?)(?:\s*Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+)?/iu', '', $cleanText);
                        // 2. Αφαιρεί σκέτο το όνομα του μαθήματος (αν έχει ξεμείνει χωρίς το "Πανελλαδικές")
                        $cleanText = preg_replace('/(ΕΞΕΤΑΖ[ΟΌ]ΜΕΝΟ\s+Μ[ΑΆ]ΘΗΜΑ:?)?\s*(ΑΝ[ΑΆ]ΠΤΥΞΗ\s+ΕΦΑΡΜΟΓ[ΩΏ]Ν\s+ΣΕ\s+ΠΡΟΓΡΑΜΜΑΤΙΣΤΙΚ[ΟΌ]\s+ΠΕΡΙΒ[ΑΆ]ΛΛΟΝ|ΠΛΗΡΟΦΟΡΙΚ[ΗΉ](?:\s+ΠΡΟΣΑΝΑΤΟΛΙΣΜΟ[ΥΎ])?)\s*(Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+)?/iu', '', $cleanText);
                        // 3. Αφαιρεί τυχόν "ΘΕΜΑ Χ" που έμεινε ακάλυπτο στην αρχή του κειμένου
                        $cleanText = preg_replace('/^\s*Θ[ΕΈ]ΜΑ\s+[Α-Ωα-ωA-Za-z0-9]+\s*/iu', '', $cleanText);

                        $previewText = mb_substr(trim($cleanText), 0, 200);
                        if (mb_strlen(trim($cleanText)) > 200) $previewText .= "...";

                        // Tags
                        $typeIds = $dbHandler->getMezeTypeIds($mezeId);
                        $tagsHtml = '';
                        if (!empty($typeIds)) {
                            foreach ($typeIds as $tid) {
                                if (isset($typeMap[$tid])) {
                                    $tagsHtml .= '<span class="badge bg-info text-dark me-1 mb-1 shadow-sm" style="font-size: 0.8rem;"><i class="fa fa-tag"></i> ' . $typeMap[$tid] . '</span> ';
                                }
                            }
                        }

                        // Panhellenic
                        $panHtml = '';
                        if (isset($row['isPanhellenic']) && $row['isPanhellenic'] == 1) {
                            $panTypeStr = ($row['panExamType'] == 'Kanonikes') ? 'Κανονικές' : 'Επαναληπτικές';
                            $panSchoolStr = ($row['panSchoolType'] == 'Hmerisio') ? 'Ημερ.' : 'Εσπερ.';
                            $panHtml = '<span class="badge bg-primary me-1 mb-1 shadow-sm" style="font-size: 0.8rem;"><i class="fa fa-university"></i> Πανελλαδικές ' . $row['panYear'] . ' - Θέμα ' . $row['panThema'] . ' (' . $panTypeStr . ' ' . $panSchoolStr . ')</span>';
                        }

                        if (isset($row['isSos']) && $row['isSos'] == 1) {
                            $panHtml .= '<span class="badge bg-danger me-1 mb-1 shadow-sm" style="font-size: 0.8rem;"><i class="fa fa-fire"></i> SOS</span>';
                        }

                        $sourceHtml = '';
                        if (!empty($row['sourceBook'])) {
                            $sourceHtml = '<span class="badge bg-secondary me-1 mb-1 shadow-sm" style="font-size: 0.8rem;"><i class="fa fa-book"></i> ' . htmlspecialchars($row['sourceBook']) . (!empty($row['sourceExercise']) ? ' - Άσκ. ' . htmlspecialchars($row['sourceExercise']) : '') . '</span>';
                        }
                ?>
                        <div class="col-md-6 col-lg-4 mb-4 bank-item">
                            <div class="card h-100 shadow-sm border-0" style="border-top: 5px solid #ffc107 !important; transition: transform 0.2s;">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                                    <h5 class="mb-0 fw-bold text-dark">Μεζεδάκι #<?php echo $row['mezeNumber']; ?></h5>
                                    <span class="text-muted small fw-bold bg-light px-2 py-1 rounded border"><i class="fa fa-calendar"></i> <?php echo date('d/m/Y', strtotime($row['mezeDate'])); ?></span>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="mb-3">
                                        <?php echo $panHtml; ?>
                                        <?php echo $sourceHtml; ?>
                                        <?php echo $tagsHtml; ?>
                                    </div>
                                    <p class="card-text text-dark" style="font-size: 0.95rem; line-height: 1.5;">
                                        <?php echo $previewText ?: "<i class='text-muted'>Δεν υπάρχει κείμενο εκφώνησης.</i>"; ?>
                                    </p>
                                    <?php if (!empty($row['mezeImage'])): ?>
                                        <div class="text-primary small mt-2 fw-bold"><i class="fa fa-image"></i> Έχει εικόνα εκφώνησης</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-light border-0 d-flex justify-content-between">
                                    <a href="index.php?action=previewMeze&amp;id=<?php echo $mezeId; ?>" target="_blank" class="btn btn-outline-dark btn-sm flex-fill me-1" title="Προβολή"><i class="fa fa-eye"></i></a>
                                    <a href="index.php?action=previewMeze&amp;id=<?php echo $mezeId; ?>&amp;autoprint=1" target="_blank" class="btn btn-outline-info btn-sm flex-fill mx-1" title="Εκτύπωση"><i class="fa fa-print"></i></a>
                                    <a href="index.php?action=editMezedaki&amp;id=<?php echo $mezeId; ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-fill ms-1" title="Επεξεργασία"><i class="fa fa-edit"></i></a>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<div class='col-12'><div class='alert alert-warning shadow-sm'>Δεν βρέθηκαν μεζεδάκια στη βάση δεδομένων.</div></div>";
                }
                ?>
            </div>
        </div>
        <style>
            .bank-item .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            }
        </style>
        <script>
            document.getElementById('bankFilter').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let items = document.querySelectorAll('.bank-item');
                items.forEach(function(item) {
                    let text = item.innerText.toLowerCase();
                    if (text.includes(filter)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        </script>
    <?php
    }

    // --- ΠΙΝΑΚΑΣ ΑΝΑΚΟΙΝΩΣΕΩΝ ---

    /**
     * @param array $announcements
     */
    public function listAnnouncements($announcements)
    {
    ?>
        <div class="container mt-4 mb-5">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <h3 class="mb-0 text-center text-sm-start"><i class="fa fa-bullhorn text-primary"></i> Πίνακας Ανακοινώσεων</h3>
                <a href="index.php?action=add_announcement" class="btn btn-success shadow-sm align-self-center align-self-sm-auto text-nowrap">
                    <i class="fa fa-plus"></i> Νέα Ανακοίνωση
                </a>
            </div>
            <div class="table-responsive shadow-sm border rounded bg-white">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 15%">Ημερομηνία</th>
                            <th style="width: 40%">Τίτλος / Προεπισκόπηση</th>
                            <th style="width: 15%">Εικόνα</th>
                            <th style="width: 30%">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($announcements)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Δεν υπάρχουν ανακοινώσεις.</td>
                            </tr>
                            <?php else: foreach ($announcements as $ann): ?>
                                <tr>
                                    <td class="text-center small fw-bold text-muted"><?php echo date('d/m/Y H:i', strtotime($ann['created_at'])); ?></td>
                                    <td>
                                        <strong class="text-primary" style="font-size: 1.1rem;"><?php echo htmlspecialchars($ann['title']); ?></strong><br>
                                        <span class="text-muted small"><?php echo mb_substr(strip_tags($ann['content']), 0, 80); ?>...</span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($ann['imagePath'])): ?>
                                            <img src="../images/announcements/<?php echo $ann['imagePath']; ?>" height="50" class="rounded shadow-sm border" alt="Εικόνα Ανακοίνωσης">
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?action=notify_announcement&amp;id=<?php echo $ann['id']; ?>" class="btn btn-info btn-sm m-1 text-white shadow-sm" onclick="return confirm('Θα σταλεί email σε όλους τους ενεργούς μαθητές. Είστε σίγουροι;')"><i class="fa fa-envelope"></i> Email</a>
                                        <a href="index.php?action=edit_announcement&amp;id=<?php echo $ann['id']; ?>" class="btn btn-warning btn-sm m-1 shadow-sm"><i class="fa fa-edit"></i></a>
                                        <a href="index.php?action=delete_announcement&amp;id=<?php echo $ann['id']; ?>" class="btn btn-danger btn-sm m-1 shadow-sm" onclick="return confirm('Οριστική διαγραφή ανακοίνωσης;')"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }

    public function announcementForm($announcement = null)
    {
        $isEdit = $announcement !== null;
        $action = $isEdit ? 'update_announcement' : 'save_announcement';
        $title = $isEdit ? $announcement['title'] : '';
        $content = $isEdit ? $announcement['content'] : '';
        $image = $isEdit ? $announcement['imagePath'] : '';
        $id = $isEdit ? $announcement['id'] : '';
    ?>
        <div class="container mt-4 border p-4 bg-light shadow mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 text-primary"><i class="fa <?php echo $isEdit ? 'fa-edit' : 'fa-bullhorn'; ?>"></i> <?php echo $isEdit ? 'Επεξεργασία Ανακοίνωσης' : 'Νέα Ανακοίνωση'; ?></h3>
                <a href="index.php?action=manage_announcements" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Επιστροφή
                </a>
            </div>
            <form action="index.php?action=<?php echo $action; ?>" method="post" enctype="multipart/form-data">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php endif; ?>
                <div class="form-group mb-3">
                    <label class="fw-bold">Τίτλος Ανακοίνωσης</label>
                    <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label class="fw-bold mb-2">Κείμενο (Υποστηρίζεται πλήρης HTML / Rich Text)</label>
                    <textarea name="content" id="annContent" class="form-control" rows="8"><?php echo $content; ?></textarea>
                </div>
                <div class="form-group card p-3 bg-white shadow-sm mb-4 border-info">
                    <label class="font-weight-bold text-info"><i class="fa fa-image"></i> Εικόνα / Αφίσα (Προαιρετικά)</label>
                    <?php if ($isEdit && !empty($image)): ?>
                        <div class="mb-3 p-2 border rounded bg-light" style="max-width: 250px;">
                            <img src="../images/announcements/<?php echo $image; ?>" class="img-fluid mb-2 rounded shadow-sm border" alt="Τρέχουσα εικόνα">
                            <div class="mt-1">
                                <input type="checkbox" name="deleteImage" value="1" id="delAnnImg">
                                <label for="delAnnImg" class="text-danger font-weight-bold" style="cursor:pointer; margin-left: 5px;">Διαγραφή υπάρχουσας εικόνας</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control-file" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100 shadow fw-bold"><i class="fa fa-save"></i> Αποθήκευση Ανακοίνωσης</button>
            </form>
        </div>
        <script>
            // Ενεργοποίηση του CKEditor στο textarea με ID annContent
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof ClassicEditor !== 'undefined') {
                    ClassicEditor.create(document.querySelector('#annContent')).catch(error => console.error(error));
                }
            });
        </script>
<?php
    }

    public function displayStudentOverview(array $students, array $dashData)
    {
        $statsMap  = $dashData['stats'];
        $totalMeze = $dashData['total_meze'];
        if (empty($students)) return;

        // Palette — ένα χρώμα ανά ομάδα
        $palette = ['#6366f1','#10b981','#0ea5e9','#f59e0b','#f43f5e','#8b5cf6','#14b8a6','#ef4444','#3b82f6','#84cc16'];
        $groupColors = [];
        $colorIdx = 0;

        $rows = [];
        foreach ($students as $s) {
            if ($s['studentId'] == 999999) continue;
            $sid = (int)$s['studentId'];
            $st  = $statsMap[$sid] ?? ['group' => '-', 'submitted' => 0, 'graded' => 0, 'avg_grade' => null, 'on_time' => 0, 'zero_count' => 0];
            if (!isset($groupColors[$st['group']])) {
                $groupColors[$st['group']] = $palette[$colorIdx++ % count($palette)];
            }
            $rows[] = array_merge($s, $st);
        }
        usort($rows, function($a, $b) {
            $avgA = $a['avg_grade'];
            $avgB = $b['avg_grade'];
            if ($avgA === null && $avgB === null) return strcmp($a['lastName'], $b['lastName']);
            if ($avgA === null) return 1;
            if ($avgB === null) return -1;
            if ($avgB != $avgA) return $avgB <=> $avgA;
            return strcmp($a['lastName'], $b['lastName']);
        });
?>
<style>
.sc-card {
    border-radius: 18px !important;
    border: none !important;
    transition: transform .2s ease, box-shadow .2s ease;
    text-decoration: none;
    display: block;
    color: inherit;
}
.sc-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(0,0,0,.13) !important;
    color: inherit;
}
.sc-avatar {
    width: 46px; height: 46px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px; color: #fff;
    flex-shrink: 0;
    letter-spacing: .5px;
}
.sc-donut {
    width: 84px; height: 84px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    margin: 0 auto;
}
.sc-donut-inner {
    width: 60px; height: 60px;
    background: #fff;
    border-radius: 50%;
    position: absolute;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column;
}
.sc-stat-label {
    font-size: 9px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-top: 2px;
}
</style>

<div class="container mt-3">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="fa fa-users text-primary"></i>
        <span class="fw-bold text-dark" style="font-size:1.05rem;">Επισκόπηση Μαθητών</span>
        <?php if ($totalMeze > 0): ?>
            <span class="badge rounded-pill bg-light text-muted border ms-1"><?php echo $totalMeze; ?> μεζεδάκια φετινής χρονιάς</span>
        <?php else: ?>
            <span class="badge rounded-pill bg-light text-muted border ms-1">Δεν έχουν ξεκινήσει ακόμα τα μεζεδάκια</span>
        <?php endif; ?>
    </div>

    <div class="row g-3">
    <?php foreach ($rows as $r):
        $sid         = (int)$r['studentId'];
        $group       = $r['group'] ?? '-';
        $gColor      = $groupColors[$group] ?? '#6366f1';
        $initials    = mb_strtoupper(mb_substr($r['name'], 0, 1) . mb_substr($r['lastName'], 0, 1), 'UTF-8');

        // Παραδόσεις
        $subPct   = $totalMeze > 0 ? round($r['submitted'] / $totalMeze * 100) : 0;
        $subDeg   = $subPct * 3.6;
        $subHex   = $subPct >= 80 ? '#10b981' : ($subPct >= 50 ? '#f59e0b' : ($totalMeze === 0 ? '#cbd5e1' : '#f43f5e'));

        // Μ.Ο.
        $avg      = $r['avg_grade'];
        $avgHex   = $avg === null ? '#94a3b8' : ($avg >= 8 ? '#10b981' : ($avg >= 5 ? '#f59e0b' : '#f43f5e'));
        $avgDisp  = $avg !== null ? number_format($avg, 1) : '—';

        // Εγκαιρότητα
        $graded   = $r['graded'];
        $otPct    = $graded > 0 ? round($r['on_time'] / $graded * 100) : null;
        $otHex    = $otPct === null ? '#94a3b8' : ($otPct >= 80 ? '#10b981' : ($otPct >= 60 ? '#f59e0b' : '#f43f5e'));
        $otDisp   = $otPct !== null ? $otPct . '%' : '—';

        // Μηδενικά
        $zeros    = $r['zero_count'];
        $zeroHex  = $zeros > 0 ? '#f43f5e' : '#10b981';
        $zeroDisp = $zeros > 0 ? $zeros : '✓';

        // Top accent = απόδοση
        $accent   = $avg === null ? '#cbd5e1' : ($avg >= 8 ? '#10b981' : ($avg >= 5 ? '#f59e0b' : '#f43f5e'));
    ?>
    <div class="col-6 col-md-4 col-xl-3">
        <a href="index.php?action=studentReport&studentId=<?php echo $sid; ?>" class="sc-card card shadow-sm h-100">
            <div style="height:5px; background:<?php echo $accent; ?>; border-radius:18px 18px 0 0;"></div>
            <div class="card-body p-3 d-flex flex-column gap-3">

                <!-- Avatar + Όνομα -->
                <div class="d-flex align-items-center gap-2">
                    <div class="sc-avatar flex-shrink-0" style="background:<?php echo $gColor; ?>;">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                    <div class="fw-bold text-dark" style="font-size:.95rem; line-height:1.3;">
                        <?php echo htmlspecialchars($r['name'] . ' ' . $r['lastName']); ?>
                    </div>
                </div>

                <!-- Donut — Παραδόσεις -->
                <div class="sc-donut"
                     style="background: conic-gradient(<?php echo $subHex; ?> <?php echo $subDeg; ?>deg, #f1f5f9 0deg);">
                    <div class="sc-donut-inner">
                        <?php if ($totalMeze > 0): ?>
                            <span class="fw-bold lh-1" style="font-size:13px; color:#1e293b;">
                                <?php echo $r['submitted']; ?>/<?php echo $totalMeze; ?>
                            </span>
                            <span style="font-size:9px; color:#94a3b8; margin-top:2px;">παραδ.</span>
                        <?php else: ?>
                            <span style="font-size:9px; color:#94a3b8; text-align:center; line-height:1.3;">δεν<br>ξεκίνησε</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats -->
                <div class="d-flex justify-content-around align-items-start pt-2"
                     style="border-top:1px solid #f1f5f9;">
                    <div class="text-center">
                        <div class="fw-bold lh-1" style="font-size:1.15rem; color:<?php echo $avgHex; ?>;">
                            <?php echo $avgDisp; ?>
                        </div>
                        <div class="sc-stat-label">ΜΟ</div>
                    </div>
                    <div style="width:1px; background:#f1f5f9; align-self:stretch;"></div>
                    <div class="text-center">
                        <div class="fw-bold lh-1" style="font-size:1.15rem; color:<?php echo $otHex; ?>;">
                            <?php echo $otDisp; ?>
                        </div>
                        <div class="sc-stat-label">Εγκαιρ.</div>
                    </div>
                    <div style="width:1px; background:#f1f5f9; align-self:stretch;"></div>
                    <div class="text-center">
                        <div class="fw-bold lh-1" style="font-size:1.15rem; color:<?php echo $zeroHex; ?>;">
                            <?php echo $zeroDisp; ?>
                        </div>
                        <div class="sc-stat-label">Μηδεν.</div>
                    </div>
                </div>

            </div>
        </a>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php
    }

    public function displayDashboardActivity(array $stats)
    {
        $groups = $stats['groups'];
        $noActiveMeze = $stats['groupsWithoutActiveMeze'];
        if (empty($groups)) return;

        $colClass = count($groups) <= 2 ? 'col-sm-6 col-md-4' : (count($groups) <= 4 ? 'col-6 col-md-3' : 'col-6 col-md-2');
?>
        <div class="container mt-3">

            <?php if (!empty($noActiveMeze)): ?>
            <div class="alert alert-danger shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="fa fa-exclamation-triangle fa-lg"></i>
                <span>
                    <strong>Δεν υπάρχει ενεργό μεζεδάκι</strong> για:
                    <?php echo implode(', ', array_map(fn($n) => "<strong>$n</strong>", $noActiveMeze)); ?>.
                    <a href="index.php?action=listMezedakia" class="alert-link ms-2">Λίστα μεζεδακίων →</a>
                </span>
            </div>
            <?php endif; ?>

            <div class="row g-3 mb-3">
                <!-- Χθεσινές υποβολές -->
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary text-white fw-bold py-2">
                            <i class="fa fa-calendar-minus-o"></i> Υποβολές Χθες
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <?php foreach ($groups as $g):
                                    $pct = $g['total'] > 0 ? round($g['yesterday'] / $g['total'] * 100) : 0;
                                    $barColor = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="<?php echo $colClass; ?>">
                                    <div class="border rounded p-2 text-center h-100">
                                        <div class="small fw-bold text-muted mb-1"><?php echo htmlspecialchars($g['name']); ?></div>
                                        <div class="fs-4 fw-bold"><?php echo $g['yesterday']; ?><span class="fs-6 text-muted">/<?php echo $g['total']; ?></span></div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar <?php echo $barColor; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Σημερινές υποβολές -->
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-primary text-white fw-bold py-2">
                            <i class="fa fa-clock-o"></i> Υποβολές Σήμερα
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <?php foreach ($groups as $g):
                                    $pct = $g['total'] > 0 ? round($g['today'] / $g['total'] * 100) : 0;
                                    $barColor = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="<?php echo $colClass; ?>">
                                    <div class="border rounded p-2 text-center h-100">
                                        <div class="small fw-bold text-muted mb-1"><?php echo htmlspecialchars($g['name']); ?></div>
                                        <div class="fs-4 fw-bold"><?php echo $g['today']; ?><span class="fs-6 text-muted">/<?php echo $g['total']; ?></span></div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar <?php echo $barColor; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
