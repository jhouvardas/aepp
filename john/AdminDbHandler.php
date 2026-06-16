<?php
include_once __DIR__ . '/../DbHandler.php';

class AdminDbHandler extends DbHandler
{
    /**
     * @var array
     */
    private $cachedStudents = [];

    // Μέθοδος για Διαγραφή (Χρήσιμη για σένα)
    public function deleteTheoryQuestion($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("DELETE FROM theory_questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getTaskById($taskId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT gt.*, g.group_name 
                                FROM aepp_group_tasks gt 
                                JOIN aepp_groups g ON gt.group_id = g.id 
                                WHERE gt.id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getStudentsByGroupId($groupId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT student_id FROM aepp_student_groups WHERE group_id = ?");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $res = $stmt->get_result();
        $studentIds = [];
        while ($row = $res->fetch_assoc()) $studentIds[] = $row['student_id'];
        $stmt->close();
        $conn->close();

        if (empty($studentIds)) return [];

        // Φιλτραρισμένο query στη βάση Tutor για καλύτερη απόδοση
        $connTutor = $this->connectToTutorDB();
        if (!$connTutor) return [];

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $sql = "SELECT studentId, name, lastName, email, phone, birthday, school FROM student WHERE studentId IN ($placeholders) AND schoolYear = ? ORDER BY name ASC";
        $stmtT = $connTutor->prepare($sql);

        $types = str_repeat('i', count($studentIds)) . 's';
        $params = array_merge($studentIds, [$userYear]);
        $stmtT->bind_param($types, ...$params);
        $stmtT->execute();

        $results = $stmtT->get_result()->fetch_all(MYSQLI_ASSOC);

        if (in_array(999999, $studentIds)) {
            $results[] = [
                'studentId' => 999999,
                'name' => 'Δοκιμαστικός',
                'lastName' => 'Μαθητής',
                'email' => 'test@test.com',
                'phone' => '-',
                'birthday' => '0000-00-00',
                'school' => 'Test School'
            ];
        }

        return $results;
    }

    public function getTaskGrades($taskId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_task_grades WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $res = $stmt->get_result();
        $grades = [];
        while ($row = $res->fetch_assoc()) $grades[$row['student_id']] = $row;
        return $grades;
    }

    public function saveTaskGrade($taskId, $studentId, $grade, $comments)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_task_grades (task_id, student_id, grade_value, teacher_comments) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE grade_value = VALUES(grade_value), teacher_comments = VALUES(teacher_comments)");
        $stmt->bind_param("iids", $taskId, $studentId, $grade, $comments);
        return $stmt->execute();
    }

    // Σημείωση: Η saveTheory() κάνει το ίδιο με την insertTheoryItem, 
    // οπότε μπορείς να κρατήσεις μόνο μία από τις δύο για να είναι καθαρός ο κώδικας.

    // Προσθήκη νέου βιβλίου
    public function insertBook($title)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO theory_books (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Διαγραφή βιβλίου
    public function deleteBook($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("DELETE FROM theory_books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Ενημέρωση τίτλου βιβλίου
    public function updateBook($id, $newTitle)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("UPDATE theory_books SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $newTitle, $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Φέρνει μία συγκεκριμένη ερώτηση για επεξεργασία
    public function getQuestionById($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM theory_questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }

    // Λήψη όλων των ερωτήσεων για τον πίνακα διαχείρισης (Admin Table)
    public function getAllTheoryQuestions()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT q.*, b.title as book_title 
                FROM theory_questions q 
                LEFT JOIN theory_books b ON q.book_id = b.id 
                ORDER BY 
                    CASE WHEN b.title = 'Βιβλίο Μαθητή' THEN 1 ELSE 2 END ASC, 
                    CAST(q.chapter_num AS UNSIGNED) ASC, 
                    q.chapter_num ASC, 
                    b.id ASC, q.id ASC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    // Βοηθητική μέθοδος για το upload
    private function uploadImage($file)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';

            // Ασφάλεια: Έλεγχος κατάληξης αρχείου
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) return null;

            // Δημιουργία μοναδικού ονόματος για να μην υπάρχουν διπλότυπα
            $fileName = time() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $fileName;
            }
        }
        return null;
    }

    public function insertTheoryItem($book_id, $chapter, $question, $answer, $page, $q_file = null, $a_file = null)
    {
        $q_image = $this->uploadImage($q_file); // Ανέβασμα εικόνας ερώτησης
        $a_image = $this->uploadImage($a_file); // Ανέβασμα εικόνας απάντησης

        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO theory_questions (book_id, chapter_num, question_text, answer_text, page_number, question_image, answer_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $book_id, $chapter, $question, $answer, $page, $q_image, $a_image);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function updateTheoryItem($id, $book_id, $chapter, $question, $answer, $page, $q_file = null, $a_file = null)
    {
        $q_image = $this->uploadImage($q_file);
        $a_image = $this->uploadImage($a_file);

        $conn = $this->connectToFamilyDB();

        // Δυναμικό χτίσιμο του Query ανάλογα με το αν ανέβηκαν νέες εικόνες
        $sql = "UPDATE theory_questions SET book_id=?, chapter_num=?, question_text=?, answer_text=?, page_number=?";
        $params = [$book_id, $chapter, $question, $answer, $page];
        $types = "isssi";

        if ($q_image) {
            $sql .= ", question_image=?";
            $params[] = $q_image;
            $types .= "s";
        }
        if ($a_image) {
            $sql .= ", answer_image=?";
            $params[] = $a_image;
            $types .= "s";
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);

        // Επιδιόρθωση για PHP 7.4 (Απαιτεί οι μεταβλητές να περνούν by reference)
        $refs = [];
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        $stmt->bind_param($types, ...$refs);

        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getMultipleQuestionsByIds($ids_array)
    {
        if (empty($ids_array) || !is_array($ids_array)) return false;

        $conn = $this->connectToFamilyDB();
        // Δημιουργούμε μια λίστα από ερωτηματικά (?,?,?) για το query
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));

        $sql = "SELECT q.*, b.title as book_title 
                FROM theory_questions q 
                JOIN theory_books b ON q.book_id = b.id 
                WHERE q.id IN ($placeholders)
                ORDER BY FIELD(q.id, $placeholders)"; // Διατηρεί τη σειρά επιλογής

        $stmt = $conn->prepare($sql);

        // Δυναμικό binding των IDs (δύο φορές λόγω του ORDER BY FIELD)
        $full_ids = array_merge($ids_array, $ids_array);
        $types = str_repeat('i', count($full_ids));
        $stmt->bind_param($types, ...$full_ids);

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function handleKenaUpload($postData, $fileData)
    {
        $conn = $this->connectToFamilyDB();
        $exerciseHtml = !empty($postData['exerciseHtml']) ? $postData['exerciseHtml'] : null;
        $newFileName = "";

        // Αν δεν έδωσες HTML, προσπάθησε να ανεβάσεις εικόνα
        if (!$exerciseHtml && isset($fileData["exerciseImage"]) && $fileData["exerciseImage"]["error"] == 0) {
            $targetDir = "../images/themata/kenaNew/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

            $fileExtension = pathinfo($fileData["exerciseImage"]["name"], PATHINFO_EXTENSION);
            $newFileName = "Kena_" . $postData['exerciseYear'] . "_" . time() . "." . $fileExtension;
            move_uploaded_file($fileData["exerciseImage"]["tmp_name"], $targetDir . $newFileName);
        }

        $sql = "INSERT INTO kena_exercises (exerciseYear, examType, schoolType, exerciseDescription, exerciseHtml, imageName) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Προσθέσαμε το exerciseHtml (s) στις παραμέτρους
        $stmt->bind_param(
            "isssss",
            $postData['exerciseYear'],
            $postData['examType'],
            $postData['schoolType'],
            $postData['exerciseDescription'],
            $exerciseHtml,
            $newFileName
        );

        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getAllKena()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM kena_exercises ORDER BY exerciseYear DESC, id DESC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    public function deleteKena($id)
    {
        $conn = $this->connectToFamilyDB();

        // 1. Βρίσκουμε το όνομα του αρχείου για να το διαγράψουμε από τον server
        $stmt = $conn->prepare("SELECT imageName FROM kena_exercises WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $filePath = "../images/themata/kenaNew/" . $row['imageName'];
            if (file_exists($filePath)) {
                unlink($filePath); // Διαγραφή αρχείου
            }
        }
        $stmt->close();

        // 2. Διαγραφή της εγγραφής από τη βάση
        $stmt = $conn->prepare("DELETE FROM kena_exercises WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function insertThemaG($data, $file)
    {
        $conn = $this->connectToFamilyDB();
        $imageName = "";

        // Λογική για την εικόνα (ανέβασμα αν υπάρχει)
        if (isset($file['eikona']) && $file['eikona']['error'] == 0) {
            $targetDir = "../images/themata/themaG/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $imageName = time() . "_" . basename($file['eikona']['name']);
            move_uploaded_file($file['eikona']['tmp_name'], $targetDir . $imageName);
        }

        $stmt = $conn->prepare("INSERT INTO aepp_themataG (etos, typosSxoleiou, typosEksetaseon, ekfonisi, lysi, eikonaPath) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $data['etos'], $data['typosSxoleiou'], $data['typosEksetaseon'], $data['ekfonisi'], $data['lysi'], $imageName);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getAllThemataG()
    {
        $conn = $this->connectToFamilyDB();
        $result = $conn->query("SELECT * FROM aepp_themataG ORDER BY etos DESC, id DESC");
        $conn->close();
        return $result;
    }

    public function deleteThemaG($id)
    {
        $conn = $this->connectToFamilyDB();

        // Διαγραφή αρχείου εικόνας αν υπάρχει
        $stmt = $conn->prepare("SELECT eikonaPath FROM aepp_themataG WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (!empty($row['eikonaPath'])) {
                @unlink("../images/themata/themaG/" . $row['eikonaPath']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM aepp_themataG WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function deleteMezedaki($id)
    {
        $conn = $this->connectToFamilyDB();

        // 1. Βρίσκουμε τα ονόματα των αρχείων
        $stmt = $conn->prepare("SELECT mezeImage, mezeSolutionImage FROM aepp_mezedakia WHERE mezeId = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            // Διαγραφή εικόνας εκφώνησης
            if (!empty($row['mezeImage'])) {
                @unlink("../images/mezedakia/" . $row['mezeImage']);
            }
            // Διαγραφή εικόνας λύσης
            if (!empty($row['mezeSolutionImage'])) {
                @unlink("../images/mezedakia/" . $row['mezeSolutionImage']);
            }
        }

        // 2. Διαγραφή από τη βάση
        $stmtDel = $conn->prepare("DELETE FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtDel->bind_param("i", $id);
        $success = $stmtDel->execute();
        $conn->close();
        return $success;
    }

    public function massHideOldMezedakia($futureDate = '2030-01-01', $futureSolutionDate = '2030-01-02 23:59:00')
    {
        $conn = $this->connectToFamilyDB();
        // Ενημερώνει ΜΟΝΟ τα μεζεδάκια που είναι ήδη ορατά (έτσι αν έχεις ήδη ετοιμάσει κάποια νέα για τον Ιούλιο δεν θα επηρεαστούν)
        $stmt = $conn->prepare("UPDATE aepp_mezedakia SET mezeDate = ?, solutionDate = ? WHERE mezeDate <= CURDATE()");
        $stmt->bind_param("ss", $futureDate, $futureSolutionDate);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function massDeleteOldSubmissions()
    {
        $conn = $this->connectToFamilyDB();

        // 1. Βρίσκουμε τα αρχεία για να τα διαγράψουμε από τον server (εξοικονόμηση χώρου)
        $stmt = $conn->prepare("SELECT file1, file2, file3 FROM aepp_meze_submissions");
        $stmt->execute();
        $res = $stmt->get_result();

        $targetDir = "../uploads/submissions/";
        while ($row = $res->fetch_assoc()) {
            for ($i = 1; $i <= 3; $i++) {
                $f = "file" . $i;
                if (!empty($row[$f])) {
                    $filePath = $targetDir . $row[$f];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
        }
        $stmt->close();

        // 2. Διαγράφουμε τις εγγραφές από τη βάση
        $success = $conn->query("DELETE FROM aepp_meze_submissions");

        // Προαιρετικά: Καθαρίζουμε και τα παλιά αιτήματα παράτασης/ενεργές παρατάσεις
        $conn->query("DELETE FROM aepp_meze_requests");
        $conn->query("DELETE FROM aepp_meze_extensions");

        $conn->close();
        return $success;
    }

    public function extendMezeForAll($mezeId, $hours, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        // Πλέον χρησιμοποιούμε το student_id = 0 για καθολική παράταση αντί να αλλάζουμε το deadline
        $sql = "INSERT INTO aepp_meze_extensions (student_id, meze_id, user_year, expires_at) 
                VALUES (0, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR)) 
                ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL ? HOUR), user_year = VALUES(user_year)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $mezeId, $userYear, $hours, $hours);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function removeGlobalExtension($mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "DELETE FROM aepp_meze_extensions WHERE student_id = 0 AND meze_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mezeId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function hasGlobalExtension($mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $nowStr = (new DateTime())->format('Y-m-d H:i:s');

        $sql = "SELECT id FROM aepp_meze_extensions WHERE student_id = 0 AND meze_id = ? AND (expires_at IS NULL OR expires_at > ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $mezeId, $nowStr);
        $stmt->execute();
        $exists = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
        $conn->close();
        return $exists;
    }

    public function getExerciseTypes()
    {
        $conn = $this->connectToFamilyDB();
        $result = $conn->query("SELECT * FROM aepp_exercise_types ORDER BY name ASC");
        $types = $result->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        return $types;
    }

    public function getMezeTypeIds($mezeId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT type_id FROM aepp_meze_type_mapping WHERE meze_id = ?");
        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_assoc()) $ids[] = $row['type_id'];
        $conn->close();
        return $ids;
    }

    public function insertExerciseType($name)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_exercise_types (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $success = $stmt->execute();
        $conn->close();
        return $success;
    }

    public function deleteExerciseType($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("DELETE FROM aepp_exercise_types WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $conn->close();
        return $success;
    }

    public function insertMezedaki($data, $file)
    {
        $conn = $this->connectToFamilyDB();
        $imageName = "";
        $solImageName = "";

        // Νέα πεδία
        $isSos = isset($data['isSos']) ? 1 : 0;
        $isPan = isset($data['isPanhellenic']) ? 1 : 0;
        $panYear = !empty($data['panYear']) ? $data['panYear'] : null;
        $panThema = !empty($data['panThema']) ? $data['panThema'] : null;
        $panExamType = !empty($data['panExamType']) ? $data['panExamType'] : null;
        $panSchoolType = !empty($data['panSchoolType']) ? $data['panSchoolType'] : null;

        $sourceBook = !empty($data['sourceBook']) ? $data['sourceBook'] : null;
        $sourceExercise = !empty($data['sourceExercise']) ? $data['sourceExercise'] : null;

        $selectedTypes = (isset($data['exercise_types']) && is_array($data['exercise_types'])) ? $data['exercise_types'] : [];

        // Εικόνα Εκφώνησης
        if (!empty($file['mezeImage']['name'])) {
            $imageName = time() . '_q_' . $file['mezeImage']['name']; // Προσθήκη _q_ για διάκριση
            move_uploaded_file($file['mezeImage']['tmp_name'], "../images/mezedakia/" . $imageName);
        }

        // Εικόνα Λύσης
        if (!empty($file['mezeSolutionImage']['name'])) {
            $solImageName = time() . '_a_' . $file['mezeSolutionImage']['name']; // Προσθήκη _a_ για διάκριση
            move_uploaded_file($file['mezeSolutionImage']['tmp_name'], "../images/mezedakia/" . $solImageName);
        }

        $stmt = $conn->prepare("INSERT INTO aepp_mezedakia (mezeNumber, mezeDate, solutionDate, mezeImage, mezeText, mezeHints, mezeSolution, mezeSolutionImage, isSos, isPanhellenic, panYear, panThema, panExamType, panSchoolType, sourceBook, sourceExercise) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssiiisssss",
            $data['mezeNumber'],
            $data['mezeDate'],
            $data['solutionDate'],
            $imageName,
            $data['mezeText'],
            $data['mezeHints'],
            $data['mezeSolution'],
            $solImageName,
            $isSos,
            $isPan,
            $panYear,
            $panThema,
            $panExamType,
            $panSchoolType,
            $sourceBook,
            $sourceExercise
        );
        $success = $stmt->execute();

        if ($success) {
            $mezeId = $conn->insert_id;
            // Αποθήκευση των Types
            if (!empty($selectedTypes)) {
                $stmtType = $conn->prepare("INSERT INTO aepp_meze_type_mapping (meze_id, type_id) VALUES (?, ?)");
                foreach ($selectedTypes as $typeId) {
                    $stmtType->bind_param("ii", $mezeId, $typeId);
                    $stmtType->execute();
                }
            }
        }

        $stmt->close();
        $conn->close();
        return $success;
    }

    public function updateMezedaki($data, $file)
    {
        $conn = $this->connectToFamilyDB();
        $mezeId = $data['mezeId'];

        // 1. Φέρνουμε τα τρέχοντα ονόματα αρχείων από τη βάση
        $stmtQuery = $conn->prepare("SELECT mezeImage, mezeSolutionImage FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtQuery->bind_param("i", $mezeId);
        $stmtQuery->execute();
        $currentRow = $stmtQuery->get_result()->fetch_assoc();
        $stmtQuery->close();

        $newImageName = $currentRow['mezeImage'];
        $newSolImageName = $currentRow['mezeSolutionImage'];
        $path = "../images/mezedakia/";

        // 2. Διαχείριση Εικόνας Εκφώνησης
        if (isset($data['deleteMezeImage']) && $data['deleteMezeImage'] == "1") {
            if (!empty($currentRow['mezeImage']) && file_exists($path . $currentRow['mezeImage'])) {
                @unlink($path . $currentRow['mezeImage']);
            }
            $newImageName = null;
        }
        if (!empty($file['mezeImage']['name'])) {
            if (!empty($currentRow['mezeImage']) && file_exists($path . $currentRow['mezeImage'])) {
                @unlink($path . $currentRow['mezeImage']);
            }
            $newImageName = time() . '_q_' . basename($file['mezeImage']['name']);
            move_uploaded_file($file['mezeImage']['tmp_name'], $path . $newImageName);
        }

        // 3. Διαχείριση Εικόνας Λύσης
        if (isset($data['deleteMezeSolutionImage']) && $data['deleteMezeSolutionImage'] == "1") {
            if (!empty($currentRow['mezeSolutionImage']) && file_exists($path . $currentRow['mezeSolutionImage'])) {
                @unlink($path . $currentRow['mezeSolutionImage']);
            }
            $newSolImageName = null;
        }
        if (!empty($file['mezeSolutionImage']['name'])) {
            if (!empty($currentRow['mezeSolutionImage']) && file_exists($path . $currentRow['mezeSolutionImage'])) {
                @unlink($path . $currentRow['mezeSolutionImage']);
            }
            $newSolImageName = time() . '_a_' . basename($file['mezeSolutionImage']['name']);
            move_uploaded_file($file['mezeSolutionImage']['tmp_name'], $path . $newSolImageName);
        }

        $isSos = isset($data['isSos']) ? 1 : 0;
        $isPan = isset($data['isPanhellenic']) ? 1 : 0;
        $panYear = !empty($data['panYear']) ? $data['panYear'] : null;
        $panThema = !empty($data['panThema']) ? $data['panThema'] : null;
        $panExamType = !empty($data['panExamType']) ? $data['panExamType'] : null;
        $panSchoolType = !empty($data['panSchoolType']) ? $data['panSchoolType'] : null;

        $sourceBook = !empty($data['sourceBook']) ? $data['sourceBook'] : null;
        $sourceExercise = !empty($data['sourceExercise']) ? $data['sourceExercise'] : null;

        $selectedTypes = (isset($data['exercise_types']) && is_array($data['exercise_types'])) ? $data['exercise_types'] : [];

        // 4. Εκτέλεση του Update (Εδώ μπαίνει το mezeHints)
        $sql = "UPDATE aepp_mezedakia SET 
            mezeNumber = ?, 
            mezeDate = ?, 
            solutionDate = ?, 
            mezeText = ?, 
            mezeHints = ?, 
            mezeSolution = ?, 
            mezeImage = ?, 
            mezeSolutionImage = ?,
            isSos = ?,
            isPanhellenic = ?,
            panYear = ?,
            panThema = ?,
            panExamType = ?,
            panSchoolType = ?,
            sourceBook = ?,
            sourceExercise = ?
            WHERE mezeId = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssssssiiisssssi",
            $data['mezeNumber'],
            $data['mezeDate'],
            $data['solutionDate'],
            $data['mezeText'],
            $data['mezeHints'],
            $data['mezeSolution'],
            $newImageName,
            $newSolImageName,
            $isSos,
            $isPan,
            $panYear,
            $panThema,
            $panExamType,
            $panSchoolType,
            $sourceBook,
            $sourceExercise,
            $mezeId
        );

        $success = $stmt->execute();

        if ($success) {
            // Ενημέρωση των Types (delete and re-insert)
            $stmtDel = $conn->prepare("DELETE FROM aepp_meze_type_mapping WHERE meze_id = ?");
            $stmtDel->bind_param("i", $mezeId);
            $stmtDel->execute();
            $stmtDel->close();

            $stmtType = $conn->prepare("INSERT INTO aepp_meze_type_mapping (meze_id, type_id) VALUES (?, ?)");
            foreach ($selectedTypes as $typeId) {
                $stmtType->bind_param("ii", $mezeId, $typeId);
                $stmtType->execute();
            }
        }

        $stmt->close();
        $conn->close();

        return $success;
    }

    public function getMezedakiById($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_mezedakia WHERE mezeId = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // 1. Φέρνει τους μαθητές από την tutor (μόνο για το admin)
    public function getTutorStudents($userYear)
    {
        if (isset($this->cachedStudents[$userYear])) {
            return $this->cachedStudents[$userYear];
        }

        $connTutor = $this->connectToTutorDB();
        if (!$connTutor || $connTutor->connect_error) {
            return []; // Επιστρέφει άδειο πίνακα αν αποτύχει η σύνδεση
        }

        $sql = "SELECT studentId, name, lastName, email, phone, birthday, school FROM student WHERE status = 1 AND schoolYear = ? ORDER BY name ASC";
        $stmt = $connTutor->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        // Εδώ γίνεται η "μαγεία": Αδειάζουμε το mysqli_result σε έναν πίνακα
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        // --- ΠΡΟΣΘΗΚΗ ΕΙΚΟΝΙΚΟΥ ΜΑΘΗΤΗ ΓΙΑ ΔΟΚΙΜΕΣ ΣΤΟ ADMIN ---
        $students[] = [
            'studentId' => 999999,
            'name' => 'Δοκιμαστικός',
            'lastName' => 'Μαθητής',
            'email' => 'test@test.com',
            'phone' => '-',
            'birthday' => '0000-00-00',
            'school' => 'Test School'
        ];

        $stmt->close();
        $connTutor->close();

        $this->cachedStudents[$userYear] = $students;

        return $students; // Επιστρέφει ΕΤΟΙΜΟ ΠΙΝΑΚΑ (Array)
    }

    // 2. Αποθηκεύει τον βαθμό στη familyDB
    public function saveMezeGrade($studentId, $mezeId, $grade, $userYear)
    {
        $conn = $this->connectToFamilyDB();

        // Η εντολή τώρα θα ξέρει: Αν υπάρχει ήδη ο συνδυασμός student_id + meze_id, 
        // απλώς άλλαξε (update) τον βαθμό.
        $stmt = $conn->prepare("INSERT INTO meze_grades (student_id, meze_id, grade_value, user_year) 
                            VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE grade_value = VALUES(grade_value)");

        $stmt->bind_param("iids", $studentId, $mezeId, $grade, $userYear);
        $success = $stmt->execute();

        // Ασφαλής ενημέρωση του updated_at (αγνοεί σφάλμα αν η στήλη δεν υπάρχει)
        $conn->query("UPDATE meze_grades SET updated_at = NOW() WHERE student_id = " . (int)$studentId . " AND meze_id = " . (int)$mezeId . " AND user_year = '" . $conn->real_escape_string($userYear) . "'");

        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getMezeNumberById($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT mezeNumber FROM aepp_mezedakia WHERE mezeId = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row ? $row['mezeNumber'] : $id; // Αν δεν το βρει, ας δείξει το id
    }

    public function getGradesForMeze($mezeId)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT student_id, grade_value, teacher_comments, updated_at FROM meze_grades WHERE meze_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            // Fallback αν η στήλη updated_at δεν υπάρχει ακόμα στη βάση για να αποφύγουμε το Fatal Error
            $sql = "SELECT student_id, grade_value, teacher_comments FROM meze_grades WHERE meze_id = ?";
            $stmt = $conn->prepare($sql);
        }

        if (!$stmt) return [];

        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $grades = [];
        // Μετατρέπουμε το result set σε array
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }

        $stmt->close();
        $conn->close();
        return $grades; // Τώρα επιστρέφει Array, όχι mysqli_result
    }

    public function getFullGradesReport($userYear)
    {
        $conn = $this->connectToFamilyDB();
        // Φέρνουμε τους βαθμούς μαζί με τον αριθμό από το μεζεδάκι
        $sql = "SELECT g.student_id, g.grade_value, g.first_grade_value, g.is_on_time, m.mezeNumber 
            FROM meze_grades g 
            JOIN aepp_mezedakia m ON g.meze_id = m.mezeId 
            WHERE g.user_year = ? 
            ORDER BY m.mezeNumber ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $report = [];
        while ($row = $result->fetch_assoc()) {
            $report[$row['student_id']][$row['mezeNumber']] = [
                'val' => $row['grade_value'],
                'first' => $row['first_grade_value'],
                'on_time' => $row['is_on_time']
            ];
        }
        $stmt->close();
        $conn->close();
        return $report;
    }

    public function getStudentGrades($studentId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT g.grade_value, m.mezeNumber, m.mezeDate 
            FROM meze_grades g 
            JOIN aepp_mezedakia m ON g.meze_id = m.mezeId 
            WHERE g.student_id = ? AND g.user_year = ? 
            ORDER BY m.mezeNumber ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $studentId, $userYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $grades;
    }

    public function deleteSpecificGrade($studentId, $mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("DELETE FROM meze_grades WHERE student_id = ? AND meze_id = ? AND user_year = ?");
        $stmt->bind_param("iis", $studentId, $mezeId, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getSubmissionsByMeze($mezeId)
    {
        $conn = $this->connectToFamilyDB();
        // Φέρνουμε τα στοιχεία της υποβολής και το όνομα του μαθητή από τη βάση tutor
        // Σημείωση: Επειδή οι μαθητές είναι σε άλλη βάση, θα κάνουμε το JOIN προσεκτικά
        $sql = "SELECT s.* FROM aepp_meze_submissions s WHERE s.meze_id = ? ORDER BY s.submission_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $submissions;
    }

    public function updateOrInsertGrade($studentId, $mezeId, $grade, $userYear, $comments = "")
    {
        $conn = $this->connectToFamilyDB();

        // 1. ΕΛΕΓΧΟΣ: Υπάρχει ήδη βαθμός;
        $checkSql = "SELECT gradeId, first_grade_value FROM meze_grades WHERE student_id = ? AND meze_id = ? AND user_year = ?";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->bind_param("iis", $studentId, $mezeId, $userYear);
        $stmtCheck->execute();
        $existing = $stmtCheck->get_result()->fetch_assoc();

        // 2. Υπολογισμός Συνέπειας (On Time)
        $onTime = 1; // Default: Εμπρόθεσμο

        $stmtD = $conn->prepare("SELECT solutionDate FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtD->bind_param("i", $mezeId);
        $stmtD->execute();
        $meze = $stmtD->get_result()->fetch_assoc();
        $stmtD->close();

        $stmtS = $conn->prepare("SELECT submission_date FROM aepp_meze_submissions WHERE student_id = ? AND meze_id = ? LIMIT 1");
        $stmtS->bind_param("ii", $studentId, $mezeId);
        $stmtS->execute();
        $sub = $stmtS->get_result()->fetch_assoc();
        $stmtS->close();

        if ($meze && $sub) {
            // Αν η ημερομηνία υποβολής είναι μεταγενέστερη της λύσης
            if (strtotime($sub['submission_date']) > strtotime($meze['solutionDate'])) {
                // Είναι εκπρόθεσμο ΜΟΝΟ αν δεν υπήρχε προηγούμενος έγκυρος βαθμός (> 0)
                // Αυτό καλύπτει την περίπτωση που ο μαθητής πήρε 0 στην ώρα του και το ξαναστέλνει
                $firstGrade = ($existing && isset($existing['first_grade_value'])) ? $existing['first_grade_value'] : 0;
                if ($firstGrade <= 0) {
                    $onTime = 0;
                }
            }
        } elseif ($meze && !$sub) {
            // Αν δεν υπάρχει καθόλου υποβολή και βαθμολογούμε, ελέγχουμε αν έχει περάσει η προθεσμία
            if (!empty($meze['solutionDate']) && time() > strtotime($meze['solutionDate'])) {
                $firstGrade = ($existing && isset($existing['first_grade_value'])) ? $existing['first_grade_value'] : 0;
                if ($firstGrade <= 0) {
                    $onTime = 0;
                }
            }
        }

        if ($existing) {
            // UPDATE: Ενημέρωση τρέχοντος βαθμού, αλλά διατήρηση του αρχικού (first_grade_value)
            $updateSql = "UPDATE meze_grades SET grade_value = ?, teacher_comments = ?, is_on_time = ? WHERE student_id = ? AND meze_id = ? AND user_year = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("dsiiis", $grade, $comments, $onTime, $studentId, $mezeId, $userYear);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            // INSERT: Πρώτη φορά βαθμολόγηση - ο τρέχων βαθμός γίνεται και "αρχικός"
            $insertSql = "INSERT INTO meze_grades (student_id, meze_id, grade_value, first_grade_value, is_on_time, teacher_comments, user_year) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("iiddiss", $studentId, $mezeId, $grade, $grade, $onTime, $comments, $userYear);
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        // Ασφαλής ενημέρωση του updated_at (αγνοεί σφάλμα αν η στήλη δεν υπάρχει)
        $conn->query("UPDATE meze_grades SET updated_at = NOW() WHERE student_id = " . (int)$studentId . " AND meze_id = " . (int)$mezeId . " AND user_year = '" . $conn->real_escape_string($userYear) . "'");

        $conn->close();
    }

    public function getStudentAverage($studentId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT AVG(grade_value) as average FROM meze_grades WHERE student_id = ? AND user_year = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $studentId, $userYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $avg = $row['average'] ? $row['average'] : 0;
        $stmt->close();
        $conn->close();
        return round($avg, 2);
    }

    public function getStudentPerformanceTrend($studentId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT g.grade_value, m.mezeNumber, m.mezeDate, g.teacher_comments 
                FROM meze_grades g 
                JOIN aepp_mezedakia m ON g.meze_id = m.mezeId 
                WHERE g.student_id = ? AND g.user_year = ? 
                ORDER BY m.mezeDate DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $studentId, $userYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return array_reverse($data);
    }

    public function getNextMezeNumber()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT MAX(mezeNumber) as maxNum FROM aepp_mezedakia";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $next = ($row['maxNum'] ?? 0) + 1;
        $conn->close();
        return $next;
    }

    public function getAllMezedakiaForAdmin()
    {
        $conn = $this->connectToFamilyDB();

        // Τα μελλοντικά (προγραμματισμένα) ταξινομούνται σε ΑΥΞΟΥΣΑ σειρά (για να φαίνονται τα πιο κοντινά πρώτα)
        // Τα τρέχοντα και παλιά παραμένουν σε ΦΘΙΝΟΥΣΑ σειρά (για να βλέπεις τα πιο πρόσφατα πρώτα)
        $sql = "SELECT * FROM aepp_mezedakia 
                ORDER BY 
                    CASE WHEN mezeDate > NOW() THEN 0 ELSE 1 END ASC,
                    CASE WHEN mezeDate > NOW() THEN mezeDate END ASC,
                    CASE WHEN mezeDate <= NOW() THEN mezeDate END DESC,
                    mezeNumber DESC";
        $result = $conn->query($sql);

        $conn->close();
        return $result;
    }

    /**
     * Επιστρέφει το πλήθος των υποβολών για ένα μεζεδάκι που δεν έχουν ακόμα βαθμολογηθεί.
     */
    public function getUngradedSubmissionsCountForMeze($mezeId, $userYear)
    {
        $students = $this->getTutorStudents($userYear);
        if (empty($students)) return 0;

        $studentIds = array_column($students, 'studentId');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        $conn = $this->connectToFamilyDB();
        $sql = "SELECT COUNT(s.student_id) 
                FROM aepp_meze_submissions s
                LEFT JOIN meze_grades g ON s.student_id = g.student_id AND s.meze_id = g.meze_id AND g.user_year = ?
                WHERE s.meze_id = ? AND s.student_id IN ($placeholders) AND (
                    g.grade_value IS NULL 
                    OR (g.updated_at IS NOT NULL AND s.submission_date > g.updated_at)
                )";
        $stmt = $conn->prepare($sql);

        $types = "si" . str_repeat('i', count($studentIds));
        $params = array_merge([$userYear, $mezeId], $studentIds);
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row['COUNT(s.student_id)'];
    }

    /**
     * Επιστρέφει το πλήθος των μαθητών που δεν έχουν υποβάλει ακόμα λύση για ένα μεζεδάκι.
     */
    public function getNotSubmittedCount($mezeId, $userYear)
    {
        $students = $this->getTutorStudents($userYear);
        if (empty($students)) return ['total' => 0, 'ungraded' => 0];

        $studentIds = array_column($students, 'studentId');

        $conn = $this->connectToFamilyDB();

        // 1. Βρίσκουμε ποιοι έχουν υποβάλει
        $stmtSub = $conn->prepare("SELECT DISTINCT student_id FROM aepp_meze_submissions WHERE meze_id = ?");
        $stmtSub->bind_param("i", $mezeId);
        $stmtSub->execute();
        $resSub = $stmtSub->get_result();
        $submittedIds = [];
        while ($row = $resSub->fetch_assoc()) $submittedIds[] = (int)$row['student_id'];
        $stmtSub->close();

        // 2. Βρίσκουμε ποιοι έχουν βαθμολογηθεί
        $stmtGrade = $conn->prepare("SELECT DISTINCT student_id FROM meze_grades WHERE meze_id = ? AND user_year = ?");
        $stmtGrade->bind_param("is", $mezeId, $userYear);
        $stmtGrade->execute();
        $resGrade = $stmtGrade->get_result();
        $gradedIds = [];
        while ($row = $resGrade->fetch_assoc()) $gradedIds[] = (int)$row['student_id'];
        $stmtGrade->close();

        $conn->close();

        $notSubmitted = 0;
        $ungradedNotSubmitted = 0;

        foreach ($studentIds as $sid) {
            $sid = (int)$sid;
            if (!in_array($sid, $submittedIds)) {
                $notSubmitted++;
                if (!in_array($sid, $gradedIds)) {
                    $ungradedNotSubmitted++;
                }
            }
        }

        return ['total' => $notSubmitted, 'ungraded' => $ungradedNotSubmitted];
    }

    public function hasAnyExtension($mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $nowStr = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM aepp_meze_extensions WHERE meze_id = ? AND (expires_at IS NULL OR expires_at > ?)");
        $stmt->bind_param("is", $mezeId, $nowStr);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return ($res['total'] > 0);
    }

    public function toggleMezeLock($mezeId, $status)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("UPDATE aepp_mezedakia SET isLocked = ? WHERE mezeId = ?");
        if (!$stmt) {
            die("SQL Error: " . $conn->error . ". Βεβαιωθείτε ότι έχετε εκτελέσει το SQL: ALTER TABLE aepp_mezedakia ADD COLUMN isLocked TINYINT(1) DEFAULT 0;");
        }

        $stmt->bind_param("ii", $status, $mezeId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // --- GROUP MANAGEMENT ---

    public function createGroup($name, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_groups (group_name, user_year) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function renameGroup($groupId, $newName, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("UPDATE aepp_groups SET group_name = ? WHERE id = ? AND user_year = ?");
        $stmt->bind_param("sis", $newName, $groupId, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getGroups($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_groups WHERE user_year = ? ORDER BY group_name ASC");
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $groups = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $groups;
    }

    public function addStudentToGroup($studentId, $groupId)
    {
        $conn = $this->connectToFamilyDB();
        // Πρώτα καθαρίζουμε παλιές ομάδες αν θέλεις ο μαθητής να ανήκει μόνο σε μία
        $stmtDel = $conn->prepare("DELETE FROM aepp_student_groups WHERE student_id = ?");
        $stmtDel->bind_param("i", $studentId);
        $stmtDel->execute();

        $stmt = $conn->prepare("INSERT INTO aepp_student_groups (student_id, group_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $studentId, $groupId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function saveGroupTask($groupId, $taskText, $bookId = null, $taskFile = null)
    {
        $taskFileName = $this->uploadTaskFile($taskFile);
        $bookId = !empty($bookId) ? $bookId : null;

        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_group_tasks (group_id, task_text, book_id, task_file) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $groupId, $taskText, $bookId, $taskFileName);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    private function uploadTaskFile($file)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/tasks/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_task_' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                return $fileName;
            }
        }
        return null;
    }

    public function getGroupTasks($groupId)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT gt.*, b.title as book_title,
                (SELECT COUNT(*) FROM aepp_student_groups sg WHERE sg.group_id = gt.group_id) as total_students,
                (SELECT COUNT(*) FROM aepp_task_grades tg WHERE tg.task_id = gt.id) as graded_count
                FROM aepp_group_tasks gt 
                LEFT JOIN theory_books b ON gt.book_id = b.id 
                WHERE gt.group_id = ? ORDER BY gt.date_added DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getStudentGroupId($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT group_id FROM aepp_student_groups WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $conn->close();
        return $row ? $row['group_id'] : null;
    }

    public function getAssignedStudents()
    {
        $conn = $this->connectToFamilyDB();
        $result = $conn->query("SELECT student_id, group_id FROM aepp_student_groups");
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[$row['student_id']] = $row['group_id'];
        }
        $conn->close();
        return $assignments;
    }

    public function removeStudentFromGroup($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("DELETE FROM aepp_student_groups WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getAllGroupTasks($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT gt.*, g.group_name, b.title as book_title,
                (SELECT COUNT(*) FROM aepp_student_groups sg WHERE sg.group_id = gt.group_id) as total_students,
                (SELECT COUNT(*) FROM aepp_task_grades tg WHERE tg.task_id = gt.id) as graded_count
                FROM aepp_group_tasks gt 
                JOIN aepp_groups g ON gt.group_id = g.id 
                LEFT JOIN theory_books b ON gt.book_id = b.id 
                WHERE g.user_year = ? 
                ORDER BY gt.date_added DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $res = $stmt->get_result();
        $tasks = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $tasks;
    }

    public function getPendingExtensionRequests($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT r.*, m.mezeNumber 
                FROM aepp_meze_requests r
                JOIN aepp_mezedakia m ON r.meze_id = m.mezeId
                WHERE r.user_year = ? AND r.status = 0
                ORDER BY r.created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        return $res;
    }

    public function processExtensionRequest($requestId, $studentId, $mezeId, $hours, $userYear, $approve = true)
    {
        $conn = $this->connectToFamilyDB();
        $status = $approve ? 1 : 2;
        $stmt = $conn->prepare("UPDATE aepp_meze_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $requestId);
        $stmt->execute();
        $conn->close();

        if ($approve) {
            return $this->allowLateSubmission($studentId, $mezeId, $userYear, $hours);
        }
        return true;
    }

    /**
     * Επιστρέφει τον αριθμό των εκκρεμών αιτημάτων παράτασης για το τρέχον έτος.
     * @param string $userYear Το έτος χρήστη (π.χ. "jhouv2026").
     * @return int Ο αριθμός των εκκρεμών αιτημάτων.
     */
    public function getPendingExtensionRequestsCount($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT COUNT(*) FROM aepp_meze_requests WHERE user_year = ? AND status = 0";
        $stmt = $conn->prepare($sql);
        $count = 0;
        if ($stmt) {
            $stmt->bind_param("s", $userYear);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_row()) $count = (int)$row[0];
            $stmt->close();
        }
        $conn->close();
        return $count;
    }

    /**
     * Βαθμολογεί αυτόματα με 0 όσους μαθητές δεν έχουν υποβάλει εργασία και δεν έχουν ήδη βαθμό.
     */
    public function massGradeZeroForMeze($mezeId, $userYear)
    {
        $students = $this->getTutorStudents($userYear);
        if (empty($students)) return 0;

        $conn = $this->connectToFamilyDB();

        // 1. Λήψη IDs όσων έχουν κάνει υποβολή
        $subSql = "SELECT student_id FROM aepp_meze_submissions WHERE meze_id = ?";
        $stmtS = $conn->prepare($subSql);
        $stmtS->bind_param("i", $mezeId);
        $stmtS->execute();
        $subRes = $stmtS->get_result();
        $submittedIds = [];
        while ($r = $subRes->fetch_assoc()) $submittedIds[] = (int)$r['student_id'];
        $stmtS->close();

        // 2. Λήψη IDs όσων έχουν ήδη βαθμολογηθεί
        $gradeSql = "SELECT student_id FROM meze_grades WHERE meze_id = ? AND user_year = ?";
        $stmtG = $conn->prepare($gradeSql);
        $stmtG->bind_param("is", $mezeId, $userYear);
        $stmtG->execute();
        $gradeRes = $stmtG->get_result();
        $gradedIds = [];
        while ($r = $gradeRes->fetch_assoc()) $gradedIds[] = (int)$r['student_id'];
        $stmtG->close();

        $count = 0;
        $sql = "INSERT INTO meze_grades (student_id, meze_id, grade_value, first_grade_value, is_on_time, teacher_comments, user_year) VALUES (?, ?, 0, 0, 0, 'Αυτόματη βαθμολόγηση λόγω μη υποβολής', ?)";
        $stmt = $conn->prepare($sql);

        foreach ($students as $s) {
            $stId = (int)$s['studentId'];
            if (!in_array($stId, $submittedIds) && !in_array($stId, $gradedIds)) {
                $stmt->bind_param("iis", $stId, $mezeId, $userYear);
                if ($stmt->execute()) $count++;
            }
        }
        $stmt->close();
        $conn->close();
        return $count;
    }

    /**
     * Επιστρέφει τους μαθητές που έχουν βαθμό 0 για ένα συγκεκριμένο μεζεδάκι.
     */
    public function getStudentsWithZeroGrade($mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT student_id FROM meze_grades WHERE meze_id = ? AND user_year = ? AND grade_value = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $mezeId, $userYear);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_assoc()) $ids[] = (int)$row['student_id'];
        $stmt->close();
        $conn->close();

        if (empty($ids)) return [];

        $allStudents = $this->getTutorStudents($userYear);
        return array_filter($allStudents, function ($s) use ($ids) {
            return in_array((int)$s['studentId'], $ids) && !empty($s['email']);
        });
    }

    /**
     * Επιστρέφει τους μαθητές που δεν έχουν υποβάλει ακόμα λύση για ένα μεζεδάκι.
     */
    public function getStudentsWithoutSubmission($mezeId, $userYear)
    {
        $allStudents = $this->getTutorStudents($userYear);
        if (empty($allStudents)) return [];

        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT DISTINCT student_id FROM aepp_meze_submissions WHERE meze_id = ?");
        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $submittedIds = [];
        while ($row = $res->fetch_assoc()) $submittedIds[] = (int)$row['student_id'];
        $stmt->close();
        $conn->close();

        return array_filter($allStudents, function ($student) use ($submittedIds) {
            return (!in_array((int)$student['studentId'], $submittedIds) && !empty($student['email']));
        });
    }

    // --- ΑΝΑΚΟΙΝΩΣΕΙΣ (ANNOUNCEMENTS) ---

    public function insertAnnouncement($title, $content, $file, $userYear)
    {
        $imageName = null;
        if (isset($file['image']) && $file['image']['error'] == 0) {
            $targetDir = "../images/announcements/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $imageName = time() . "_" . basename($file['image']['name']);
            move_uploaded_file($file['image']['tmp_name'], $targetDir . $imageName);
        }
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_announcements (title, content, imagePath, user_year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $imageName, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function updateAnnouncement($id, $title, $content, $file, $deleteImage, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT imagePath FROM aepp_announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $imageName = $row['imagePath'];
        $targetDir = "../images/announcements/";

        if ($deleteImage == "1") {
            if (!empty($imageName)) @unlink($targetDir . $imageName);
            $imageName = null;
        }

        if (isset($file['image']) && $file['image']['error'] == 0) {
            if (!empty($imageName)) @unlink($targetDir . $imageName);
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $imageName = time() . "_" . basename($file['image']['name']);
            move_uploaded_file($file['image']['tmp_name'], $targetDir . $imageName);
        }

        $stmt = $conn->prepare("UPDATE aepp_announcements SET title=?, content=?, imagePath=? WHERE id=? AND user_year=?");
        $stmt->bind_param("sssis", $title, $content, $imageName, $id, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function deleteAnnouncement($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT imagePath FROM aepp_announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!empty($row['imagePath'])) @unlink("../images/announcements/" . $row['imagePath']);

        $stmt = $conn->prepare("DELETE FROM aepp_announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getAnnouncementById($id)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function getAllAnnouncements($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_announcements WHERE user_year = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function logGroupEmail($groupId, $subject, $message, $userYear)
    {
        $conn = $this->connectToFamilyDB();

        // Δημιουργία του πίνακα αν δεν υπάρχει
        $conn->query("CREATE TABLE IF NOT EXISTS aepp_group_email_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            user_year VARCHAR(50) NOT NULL,
            sent_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $stmt = $conn->prepare("INSERT INTO aepp_group_email_history (group_id, subject, message, user_year, sent_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("isss", $groupId, $subject, $message, $userYear);
            $success = $stmt->execute();
            $stmt->close();
        }
        $conn->close();
        return $success ?? false;
    }

    public function getGroupEmailHistory($userYear)
    {
        $conn = $this->connectToFamilyDB();

        $conn->query("CREATE TABLE IF NOT EXISTS aepp_group_email_history (
            id INT AUTO_INCREMENT PRIMARY KEY, group_id INT NOT NULL, subject VARCHAR(255) NOT NULL, message TEXT NOT NULL, user_year VARCHAR(50) NOT NULL, sent_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $sql = "SELECT h.*, g.group_name 
                FROM aepp_group_email_history h 
                LEFT JOIN aepp_groups g ON h.group_id = g.id 
                WHERE h.user_year = ? 
                ORDER BY h.sent_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $res;
    }
}
