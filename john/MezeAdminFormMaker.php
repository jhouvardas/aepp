<?php
include_once 'AdminFormMaker.php';

class MezeAdminFormMaker extends AdminFormMaker
{
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
                    $dispCorrectAns = htmlspecialchars($rawCorrectAns, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    if ($studentAns !== '') {
                        if ($this->normalizeString($studentAns) == $this->normalizeString($correctAnswer)) {
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

    public function listMezedakia($result, $dbHandler)
    {
?>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fa fa-list text-primary"></i> Διαχείριση Μεζεδακίων</h3>
                <input type="text" id="mezeFilter" class="form-control w-25 shadow-sm" placeholder="Αναζήτηση...">
            </div>

            <div class="table-responsive shadow-sm border rounded" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-bordered table-striped align-middle mb-0" id="mezeTable">
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
                            $mezeCount = 0;
                            while ($row = $result->fetch_assoc()):
                                $mezeCount++;
                                $hiddenClass = ($mezeCount > 15) ? 'd-none hidden-admin-meze-item' : '';
                                $mezeId = $row['mezeId'];
                                $mTimestamp = strtotime($row['mezeDate']);
                                $solTimestamp = strtotime($row['solutionDate'] ?? '');
                                $currentTimestamp = time();
                                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : '';

                                $isFuture = ($mTimestamp > $currentTimestamp);
                                $isLocked = (isset($row['isLocked']) && $row['isLocked'] == 1);
                                $isExpired = ($solTimestamp > 0 && $solTimestamp < $currentTimestamp);
                                $hasExtensions = (!empty($userYear)) ? $dbHandler->hasAnyExtension($mezeId, $userYear) : false;
                                $isClosed = $isLocked || ($isExpired && !$hasExtensions);

                                $rowStyle = '';
                                $badgeHtml = '';

                                if ($isLocked) {
                                    $badgeHtml .= '<br><span class="badge bg-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-lock"></i> Κλειδωμένο (Manual)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                } elseif ($isClosed) {
                                    $badgeHtml .= '<br><span class="badge bg-secondary mt-1" style="font-size: 0.75rem; opacity: 0.8;"><i class="fa fa-lock"></i> Λήξη (Χωρίς Ext)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                }

                                if ($hasExtensions && !$isLocked) {
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
                                    if ($notSubmittedCount > 0 && !$isFuture) {
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

                                if ($isFuture) {
                                    $badgeHtml .= '<br><span class="badge bg-primary text-white mt-1 shadow-sm" style="font-size: 0.75rem;"><i class="fa fa-calendar-check-o"></i> Προγραμματισμένο</span>';
                                    $rowStyle = 'style="background-color: #e9f2fd; color: #495057;"';
                                }
                        ?>
                                <tr <?php echo $rowStyle; ?> class="align-middle <?php echo $hiddenClass; ?>">
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
                                    <td class="small"><?php echo mb_substr(strip_tags($row['mezeText']), 0, 60) . "..."; ?><?php echo $badgeHtml; ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap justify-content-center">
                                            <a href="index.php?action=toggleMezeLock&id=<?php echo $mezeId; ?>&status=<?php echo $isLocked ? 0 : 1; ?>" class="btn btn-outline-<?php echo $isLocked ? 'success' : 'secondary'; ?> btn-sm m-1"><i class="fa fa-<?php echo $isLocked ? 'unlock' : 'lock'; ?>"></i></a>
                                            <a href="index.php?action=previewMeze&id=<?php echo $mezeId; ?>" target="_blank" class="btn btn-dark btn-sm m-1"><i class="fa fa-search"></i></a>
                                            <a href="index.php?action=viewSubmissions&id=<?php echo $mezeId; ?>" class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-info'; ?> btn-sm m-1"><i class="fa fa-eye"></i></a>
                                            <a href="index.php?action=manageGrades&id=<?php echo $mezeId; ?>" class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-primary'; ?> btn-sm m-1"><i class="fa fa-graduation-cap"></i></a>
                                            <a href="index.php?action=editMezedaki&id=<?php echo $mezeId; ?>" class="btn btn-warning btn-sm m-1"><i class="fa fa-edit"></i></a>
                                            <a href="index.php?action=deleteMezedaki&id=<?php echo $mezeId; ?>" class="btn btn-danger btn-sm m-1" onclick="return confirm('Διαγραφή;')"><i class="fa fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                        <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($mezeCount) && $mezeCount > 15): ?>
                <div class="text-center my-4" id="loadMoreAdminMezeContainer">
                    <button class="btn btn-outline-primary shadow-sm font-weight-bold px-4" onclick="loadMoreAdminMezedakia()">
                        <i class="fa fa-arrow-down"></i> Εμφάνιση παλαιότερων (<?php echo ($mezeCount - 15); ?>)
                    </button>
                </div>
                <script>
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
            <?php endif; ?>
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
                    <div class="form-group col-md-6">
                        <label>Αριθμός Μεζεδακίου</label>
                        <input type="number" name="mezeNumber" class="form-control" value="<?php echo $nextNumber; ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Ημερομηνία Εμφάνισης</label>
                        <input type="date" name="mezeDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="bg-white border p-3 rounded shadow-sm">
                        <label class="switch-container text-danger font-weight-bold mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="isSos" value="1" id="isSosCheck">
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
                                    <select name="panSchoolType" class="form-group form-control">
                                        <option value="Hmerisio">Ημερήσιο</option>
                                        <option value="Esperino">Εσπερινό</option>
                                    </select>
                                </div>
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
                    <label>Ημερομηνία & Ώρα Λύσης (Deadline)</label>
                    <input type="datetime-local" name="solutionDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Κείμενο / Κώδικας (HTML/Bootstrap)</label>
                        <!-- Κουμπί που ανοίγει το Modal του Builder -->
                        <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#builderModal">
                            <i class="fa fa-magic"></i> Γρήγορη Δημιουργία Κενών / Σ-Λ
                        </button>
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
                    <label><strong>Οδηγίες / Hints (προς μαθητές):</strong></label>
                    <textarea name="mezeHints" class="form-control" rows="3" placeholder="Μικρές βοήθειες..."></textarea>
                </div>
                <div class="form-group">
                    <label>Λύση (Προαιρετικά - θα εμφανίζεται σε accordion)</label>
                    <textarea name="mezeSolution" class="form-control" rows="4" placeholder="Γράψε τη λύση εδώ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία Λύσης (Προαιρετικά)</label>
                    <input type="file" name="mezeSolutionImage" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-warning btn-block font-weight-bold">Αποθήκευση Μεζεδακίου</button>
            </form>
        </div>
        <script>
            function togglePanFields(checkbox) {
                document.getElementById('panelliniesFields').style.display = checkbox.checked ? 'block' : 'none';
            }

            // Αυτόματη φόρτωση της εκφώνησης στον Builder όταν ανοίγει το Modal (Προσθήκη)
            var builderModalNew = document.getElementById('builderModal');
            if (builderModalNew) {
                builderModalNew.addEventListener('shown.bs.modal', function() {
                    var iframe = document.getElementById('builderIframeNew');
                    var textarea = document.getElementById('newMezeText');
                    if (iframe && iframe.contentWindow && textarea) {
                        var builderInput = iframe.contentWindow.document.getElementById('rawInput');
                        if (builderInput && builderInput.value.trim() === '' && textarea.value.trim() !== '') {
                            // Αυτόματη φόρτωση ΜΟΝΟ αν το κείμενο έχει παραχθεί εξ ολοκλήρου από τον Builder
                            if (textarea.value.indexOf('AEPP_RAW_START') !== -1) {
                                builderInput.value = textarea.value;
                                if (typeof iframe.contentWindow.recoverFromHTML === 'function') {
                                    iframe.contentWindow.recoverFromHTML(true);
                                }
                            }
                        }
                    }
                });
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
        </script>
    <?php
    }

    public function editMezedakiForm($row)
    {
        $db = new AdminDbHandler();
        $exerciseTypes = $db->getExerciseTypes();
        $selectedTypeIds = $db->getMezeTypeIds($row['mezeId']);

    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fa fa-edit"></i> Επεξεργασία Μεζεδακίου #<?php echo $row['mezeNumber']; ?></h3>
                <a href="index.php?action=listMezedakia" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Επιστροφή στη Λίστα
                </a>
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
                        <input type="date" name="mezeDate" class="form-control" value="<?php echo $row['mezeDate']; ?>" required>
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
                        <input type="datetime-local" name="solutionDate" class="form-control" value="<?php echo $currentVal; ?>" required>
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
                        <!-- Κουμπί που ανοίγει το Modal του Builder (Edit) -->
                        <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#builderModalEdit">
                            <i class="fa fa-magic"></i> Γρήγορη Δημιουργία Κενών / Σ-Λ
                        </button>
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
                    <label class="font-weight-bold text-info"><i class="fa fa-lightbulb-o"></i> Οδηγίες / Hints (Προς Μαθητές)</label>
                    <textarea name="mezeHints" class="form-control" rows="3" placeholder="Γράψτε εδώ μικρές βοήθειες ή hints..."><?php echo isset($row['mezeHints']) ? $row['mezeHints'] : ''; ?></textarea>
                    <small class="form-text text-muted">Αυτό το κείμενο θα εμφανίζεται στο Site των μαθητών με κουμπί "Χρειάζεσαι βοήθεια;".</small>
                </div>

                <div class="form-group card p-3 bg-white shadow-sm">
                    <label class="font-weight-bold">Εικόνα Εκφώνησης</label>
                    <?php if (!empty($row['mezeImage'])): ?>
                        <div class="mb-3 p-2 border rounded bg-light" style="max-width: 250px;">
                            <small class="text-muted">Τρέχουσα εικόνα:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeImage']; ?>" width="150" class="img-thumbnail mb-2">
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
                    <label class="font-weight-bold text-success">Λύση (mezeSolution)</label>
                    <textarea name="mezeSolution" class="form-control mb-3" rows="5"><?php echo $row['mezeSolution']; ?></textarea>

                    <label class="font-weight-bold">Εικόνα Λύσης (Προαιρετικά)</label>
                    <?php if (!empty($row['mezeSolutionImage'])): ?>
                        <div class="mb-3 p-2 border rounded bg-light" style="max-width: 250px;">
                            <small class="text-muted">Τρέχουσα εικόνα λύσης:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeSolutionImage']; ?>" width="150" class="img-thumbnail border-success mb-2">
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

                <div class="mt-4 row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow">
                            <i class="fa fa-save"></i> Ενημέρωση Μεζεδακίου
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-block shadow">
                            <i class="fa fa-times"></i> Ακύρωση
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <script>
            function togglePanFieldsEdit(checkbox) {
                document.getElementById('panelliniesFieldsEdit').style.display = checkbox.checked ? 'block' : 'none';
            }

            // Αυτόματη φόρτωση της εκφώνησης στον Builder όταν ανοίγει το Modal (Επεξεργασία)
            var builderModalEdit = document.getElementById('builderModalEdit');
            if (builderModalEdit) {
                builderModalEdit.addEventListener('shown.bs.modal', function() {
                    var iframe = document.getElementById('builderIframeEdit');
                    var textarea = document.getElementById('editMezeText');
                    if (iframe && iframe.contentWindow && textarea) {
                        var builderInput = iframe.contentWindow.document.getElementById('rawInput');
                        if (builderInput && builderInput.value.trim() === '' && textarea.value.trim() !== '') {
                            // Αυτόματη φόρτωση ΜΟΝΟ αν το κείμενο έχει παραχθεί εξ ολοκλήρου από τον Builder
                            if (textarea.value.indexOf('AEPP_RAW_START') !== -1) {
                                builderInput.value = textarea.value;
                                if (typeof iframe.contentWindow.recoverFromHTML === 'function') {
                                    iframe.contentWindow.recoverFromHTML(true);
                                }
                            }
                        }
                    }
                });
            }
        </script>
    <?php
    }

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
                <p style='margin:5px 0;'><b>Σχόλια:</b> $comments</p>
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
                        <p class="lead italic mb-0"><?php echo nl2br($comments) ?: "<i>Δεν καταχωρήθηκαν σχόλια.</i>"; ?></p>
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
                        <td><a href="index.php?action=delete_exercise_type&id=<?php echo $t['id']; ?>" class="btn btn-danger btn-sm">Διαγραφή</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php
    }

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
}
