<?php
include_once '../DbHandler.php';

class AdminDbHandler extends DbHandler
{



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
                JOIN theory_books b ON q.book_id = b.id 
                ORDER BY q.chapter_num ASC, q.id ASC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    // Βοηθητική μέθοδος για το upload
    private function uploadImage($file)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
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
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getMultipleQuestionsByIds($ids_array)
    {
        if (empty($ids_array)) return false;

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

    public function deleteKenaExercise($id)
    {
        $conn = $this->connectToFamilyDB();
        // Πρώτα βρίσκουμε το όνομα του αρχείου για να το σβήσουμε από το δίσκο
        $stmt = $conn->prepare("SELECT imageName FROM kena_exercises WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            unlink("../images/themata/kenaNew/" . $row['imageName']);
        }

        $stmt = $conn->prepare("DELETE FROM kena_exercises WHERE id = ?");
        $stmt->bind_param("i", $id);
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

    public function addMezedakiForm()
    {
?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-coffee"></i> Νέο Μεζεδάκι</h3>
            <form action="index.php?action=saveMezedaki" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Αριθμός Μεζεδακίου</label>
                        <input type="number" name="mezeNumber" class="form-control" placeholder="π.χ. 1" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Ημερομηνία Εμφάνισης</label>
                        <input type="date" name="mezeDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Κείμενο / Κώδικας (HTML/Bootstrap)</label>
                    <textarea name="mezeText" class="form-control" rows="6" placeholder="Γράψε την εκφώνηση εδώ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία (Προαιρετικά)</label>
                    <input type="file" name="mezeImage" class="form-control">
                </div>
                <div class="form-group">
                    <label>Λύση (Προαιρετικά - θα εμφανίζεται σε accordion)</label>
                    <textarea name="mezeSolution" class="form-control" rows="4" placeholder="Γράψε τη λύση εδώ..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning btn-block font-weight-bold">Αποθήκευση Μεζεδακίου</button>
            </form>
        </div>
<?php
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

    public function insertMezedaki($data, $file)
    {
        $conn = $this->connectToFamilyDB();
        $imageName = "";
        $solImageName = "";

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

        // Προσθήκη του mezeSolutionImage στα πεδία
        $stmt = $conn->prepare("INSERT INTO aepp_mezedakia (mezeNumber, mezeDate, solutionDate, mezeImage, mezeText, mezeSolution, mezeSolutionImage) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $data['mezeNumber'], $data['mezeDate'], $data['solutionDate'], $imageName, $data['mezeText'], $data['mezeSolution'], $solImageName);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function updateMezedaki($data, $file)
    {
        $conn = $this->connectToFamilyDB();
        $mezeId = $data['mezeId'];

        // Φέρνουμε τα τρέχοντα ονόματα εικόνων
        $stmtQuery = $conn->prepare("SELECT mezeImage, mezeSolutionImage FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtQuery->bind_param("i", $mezeId);
        $stmtQuery->execute();
        $currentRes = $stmtQuery->get_result();
        $currentRow = $currentRes->fetch_assoc();
        $stmtQuery->close();

        $newImageName = $currentRow['mezeImage'];
        $newSolImageName = $currentRow['mezeSolutionImage'];

        // 1. Διαχείριση Εικόνας Εκφώνησης
        if (!empty($file['mezeImage']['name'])) {
            // Σβήσιμο παλιάς
            if (!empty($currentRow['mezeImage'])) {
                @unlink("../images/mezedakia/" . $currentRow['mezeImage']);
            }
            // Ανέβασμα νέας
            $newImageName = time() . '_q_' . $file['mezeImage']['name'];
            move_uploaded_file($file['mezeImage']['tmp_name'], "../images/mezedakia/" . $newImageName);
        }

        // 2. Διαχείριση Εικόνας Λύσης
        if (!empty($file['mezeSolutionImage']['name'])) {
            // Σβήσιμο παλιάς
            if (!empty($currentRow['mezeSolutionImage'])) {
                @unlink("../images/mezedakia/" . $currentRow['mezeSolutionImage']);
            }
            // Ανέβασμα νέας
            $newSolImageName = time() . '_a_' . $file['mezeSolutionImage']['name'];
            move_uploaded_file($file['mezeSolutionImage']['tmp_name'], "../images/mezedakia/" . $newSolImageName);
        }

        // 3. Εκτέλεση του Update
        $stmt = $conn->prepare("UPDATE aepp_mezedakia SET mezeNumber=?, mezeDate=?, solutionDate=?, mezeText=?, mezeSolution=?, mezeImage=?, mezeSolutionImage=? WHERE mezeId=?");
        $stmt->bind_param("issssssi", $data['mezeNumber'], $data['mezeDate'], $data['solutionDate'], $data['mezeText'], $data['mezeSolution'], $newImageName, $newSolImageName, $mezeId);

        $success = $stmt->execute();
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
        // Στοιχεία σύνδεσης για τη βάση tutor
        $connTutor = new mysqli("jhouv.eu", "jhouvardas", "Jhouv@1957", "tutor");
        mysqli_set_charset($connTutor, "utf8");

        if ($connTutor->connect_error) {
            return []; // Επιστρέφει άδειο πίνακα αν αποτύχει η σύνδεση
        }

        $sql = "SELECT studentId, name, lastName FROM student WHERE status = 1 AND user = ? ORDER BY lastName ASC";
        $stmt = $connTutor->prepare($sql);
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        // Εδώ γίνεται η "μαγεία": Αδειάζουμε το mysqli_result σε έναν πίνακα
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        $stmt->close();
        $connTutor->close();

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
        $stmt = $conn->prepare("SELECT student_id, grade_value FROM meze_grades WHERE meze_id = ?");
        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[$row['student_id']] = $row['grade_value'];
        }

        $stmt->close();
        $conn->close();
        return $grades; // Επιστρέφει array με format [studentId => grade]
    }

    public function getFullGradesReport($userYear)
    {
        $conn = $this->connectToFamilyDB();
        // Φέρνουμε τους βαθμούς μαζί με τον αριθμό από το μεζεδάκι
        $sql = "SELECT g.student_id, g.grade_value, m.mezeNumber 
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
            $report[$row['student_id']][$row['mezeNumber']] = $row['grade_value'];
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

        $checkSql = "SELECT grade_id FROM meze_grades WHERE student_id = ? AND meze_id = ? AND user_year = ?";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->bind_param("iis", $studentId, $mezeId, $userYear);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($res->num_rows > 0) {
            // UPDATE: Προσθέτουμε και το teacher_comments
            $updateSql = "UPDATE meze_grades SET grade_value = ?, teacher_comments = ? WHERE student_id = ? AND meze_id = ? AND user_year = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("dsiis", $grade, $comments, $studentId, $mezeId, $userYear);
            $success = $stmtUpdate->execute();
        } else {
            // INSERT: Προσθέτουμε και το teacher_comments
            $insertSql = "INSERT INTO meze_grades (student_id, meze_id, grade_value, teacher_comments, user_year) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("iidss", $studentId, $mezeId, $grade, $comments, $userYear);
            $success = $stmtInsert->execute();
        }
        $conn->close();
        return $success;
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
}
