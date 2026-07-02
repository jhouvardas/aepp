<?php
require_once __DIR__ . '/john/config.php'; // Χρήση __DIR__ για σωστό εντοπισμό του αρχείου
date_default_timezone_set('Europe/Athens');

class DbHandler
{

    public function connectToFamilyDB()
    {
        if (!defined('FAMILY_DB_SERVER')) {
            die("Σφάλμα: Λείπουν οι ρυθμίσεις της βάσης Family στο config.php");
        }

        $servername = FAMILY_DB_SERVER;
        $username = FAMILY_DB_USER;
        $password = FAMILY_DB_PASS;
        $dbname = FAMILY_DB_NAME;
        $conn = new mysqli($servername, $username, $password, $dbname);
        mysqli_set_charset($conn, "utf8");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            //echo 'welcome ha';
        }
        return $conn;
    }

    protected function connectToTutorDB()
    {
        if (!defined('TUTOR_DB_SERVER')) {
            return null;
        }

        $servername = TUTOR_DB_SERVER;
        $username = TUTOR_DB_USER;
        $password = TUTOR_DB_PASS;
        $dbname = TUTOR_DB_NAME;
        $conn = new mysqli($servername, $username, $password, $dbname);
        mysqli_set_charset($conn, "utf8");
        return ($conn->connect_error) ? null : $conn;
    }

    public function login($username, $password)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM italiano_user WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            throw new Exception('Could not log you in');
        }
    }

    public function addThema()
    {
        $conn = $this->connectToFamilyDB();
        $school = $_POST['school'];
        $year = $_POST['year'];
        $thema = $_POST['thema'];
        $period = $_POST['period'];
        $section = $_POST['section'];
        $type = $_POST['type'];
        $file = $_FILES['fileToUpload']['name'];

        $stmt = $conn->prepare("INSERT INTO aepp_themata (school, year, thema, file, period, section, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssss", $school, $year, $thema, $file, $period, $section, $type);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    public function addNote()
    {
        $conn = $this->connectToFamilyDB();
        $note = $_POST['note'];
        $date = $_POST['date'];
        if (isset($_POST['submitNote'])) {
            $stmt = $conn->prepare("INSERT INTO italiano_note (note, date) VALUES (?, ?)");
            $stmt->bind_param("ss", $note, $date);
            if ($stmt->execute()) {
                echo "Η σημείωση αποθηκεύτηκε";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    public function updateNote()
    {
        $conn = $this->connectToFamilyDB();
        $noteId = $_POST['noteId'];
        $note = $_POST['note'];
        $date = $_POST['date'];
        if (isset($_POST['updateNote'])) {
            $sql = "UPDATE italiano_note SET note= '$note'  WHERE noteId = $noteId";
            if ($conn->query($sql) === TRUE) {
                echo "Η σημείωση διορθώθηκε";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    public function deleteNote()
    {
        $conn = $this->connectToFamilyDB();
        $noteId = $_POST['noteId'];
        if (isset($_POST['deleteNote'])) {
            $sql = "DELETE FROM italiano_note  WHERE noteId = $noteId";
            if ($conn->query($sql) === TRUE) {
                echo "Η σημείωση διαγράφηκε";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    public function getNotes()
    {
        $conn = $this->connectToFamilyDB();
        if (isset($_POST['date'])) {
            $date = $_POST['date'];
        } else {
            $date = '2020-01-01';
        }
        $sql = "SELECT * FROM italiano_note WHERE date >= '" . $date . "' ORDER BY date DESC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            //                 echo 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee' . $sql;
            return $result;
        } else {
            echo '0 results';
        }
    }

    public function getNote($noteId)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM italiano_note WHERE noteId = $noteId";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            echo 'μπράβο';
            return $result;
        } else {
            echo '0 results ' . $sql;
        }
    }

    public function getWords()
    {
        $conn = $this->connectToFamilyDB();
        if (isset($_POST['categoryId']) && $_POST['categoryId'] != '') {
            $categoryId = 'WHERE categoryId = ' . $_POST['categoryId'];
        } else {
            $categoryId = '';
        }
        $orderBy = $_POST['orderBy'];
        $order = 'ORDER BY italian ASC';
        if (isset($_POST['italian'])) {
            $language = 'italian';
        } else {
            $language = 'greek';
        }
        if ($orderBy == 'dateAdded') {
            $order = 'ORDER BY date ASC';
        } elseif ($orderBy == 'wordAsc') {
            $order = 'ORDER BY ' . $language . ' ASC';
        } elseif ($orderBy == 'wordDesc') {
            $order = 'ORDER BY ' . $language . ' DESC';
        } elseif ($orderBy == 'category') {
            $order = 'ORDER BY category ASC';
        }
        $sql = "SELECT * FROM italiano_word   $order"; //$category
        //        echo $sql;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo "Δεν υπάρχουν λέξεις στην κατηγορία " . $_POST['categoryId'];
        }
    }

    public function getThemata()
    {
        $conn = $this->connectToFamilyDB();
        $year = $_POST['year'];
        $type = $_POST['type'];
        $thema = $_POST['thema'];
        $sql = "SELECT * FROM aepp_themata WHERE 1=1  ";
        if (!empty($year)) {
            $sql .= " AND year = " . "IF('" . $year . "' = '', year, '" . $year . "')";
        }
        if (!empty($type)) {
            $sql .= " AND type = " . "IF('" . $type . "' = '', type, '" . $type . "')";
        }
        if (!empty($thema)) {
            $sql .= " AND thema = " . "IF('" . $thema . "' = '', thema, '" . $thema . "')";
        }
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo '';
        }
    }

    public function getSentences()
    {

        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM italiano_word WHERE type = 'πρόταση' ORDER BY italian ASC";

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            //            echo $sql;
            return $result;
        } else {
            echo 'error';
            //            echo $sql;
        }
    }

    public function getWord()
    {
        $conn = $this->connectToFamilyDB();
        $wordId = $_POST['wordId'];
        $sql = "SELECT * FROM italiano_word WHERE wordId = $wordId";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo "0 results", $sql;
        }
    }

    public function findWord()
    {
        $conn = $this->connectToFamilyDB();
        $word = $_POST['word'];
        $sql = "SELECT * FROM italiano_word WHERE italian LIKE '%$word%' OR greek LIKE '%$word%'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo "<p class=\"text-center\">Δεν υπάρχει στο λεξικό η λέξη <b>$word</b></p>";
        }
    }

    public function getWordCategories()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM italiano_wordCategories";
        //        echo $sql;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo "0 results", $sql;
        }
    }

    public function getTypes()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM italiano_wordType ORDER BY type ASC";
        //        echo $sql;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return $result;
        } else {
            echo "0 results", $sql;
        }
    }

    public function checkIfWordExists()
    {
        $conn = $this->connectToFamilyDB();
        $word = $_POST['italian'];
        $sql = "SELECT * FROM italiano_word WHERE italian = '$word'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $conn->close();
            return true;
        } else {
            return false;
        }
    }

    public function getTheoryBooks()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM theory_books";
        $result = $conn->query($sql);
        // Δεν κλείνουμε τη σύνδεση εδώ γιατί χρειαζόμαστε το result set στη φόρμα
        return $result;
    }

    public function getTheoryByChapter($chapter_num)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT q.*, b.title as book_title 
                            FROM theory_questions q 
                            JOIN theory_books b ON q.book_id = b.id 
                            WHERE q.chapter_num = ?
                            ORDER BY q.id ASC");
        $stmt->bind_param("s", $chapter_num);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function getDistinctChapters()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT DISTINCT chapter_num FROM theory_questions ORDER BY CAST(chapter_num AS UNSIGNED) ASC, chapter_num ASC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    public function getAllQuestionsOrdered()
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT q.*, b.title as book_title 
            FROM theory_questions q 
            JOIN theory_books b ON q.book_id = b.id 
            ORDER BY b.id ASC, CAST(q.chapter_num AS UNSIGNED) ASC, q.chapter_num ASC, q.id ASC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    public function getAllKenaExercises()
    {
        $conn = $this->connectToFamilyDB();
        // Αλλάζουμε το DESC σε ASC για αύξουσα σειρά κατά έτος
        $sql = "SELECT * FROM kena_exercises ORDER BY exerciseYear ASC, id ASC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    public function getThemaGByCriteria($year, $school, $period)
    {
        $conn = $this->connectToFamilyDB();
        // Χρησιμοποιούμε τα ονόματα στηλών camelCase όπως ορίσαμε στον πίνακα aepp_themataG
        $stmt = $conn->prepare("SELECT * FROM aepp_themataG 
                            WHERE etos = ? 
                            AND typosSxoleiou = ? 
                            AND typosEksetaseon = ? 
                            LIMIT 1");

        $stmt->bind_param("iss", $year, $school, $period);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function getThemaGDByCriteria($year, $school, $period, $type)
    {
        $conn = $this->connectToFamilyDB();

        // Αν το έτος είναι "all", αλλάζουμε το query για να μην φιλτράρει με etos
        if ($year === 'all') {
            $stmt = $conn->prepare("SELECT * FROM aepp_themataGD 
                                WHERE typosSxoleiou = ? 
                                AND typosEksetaseon = ? 
                                AND thema_type = ? 
                                ORDER BY etos DESC");
            $stmt->bind_param("sss", $school, $period, $type);
        } else {
            $stmt = $conn->prepare("SELECT * FROM aepp_themataGD 
                                WHERE etos = ? 
                                AND typosSxoleiou = ? 
                                AND typosEksetaseon = ? 
                                AND thema_type = ? 
                                ORDER BY id DESC");
            $stmt->bind_param("isss", $year, $school, $period, $type);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function getAllMezedakia($studentId = null)
    {
        $conn  = $this->connectToFamilyDB();
        $today = date('Y-m-d');

        if ($studentId) {
            // Κανόνας ορατότητας:
            // α) Μεζεδάκι ΧΩΡΙΣ group deadlines → ορατό σε όλους αν mezeDate <= σήμερα
            // β) Μεζεδάκι ΜΕ group deadlines   → ορατό ΜΟΝΟ στους μαθητές των ομάδων που έχουν deadline
            // Το solutionDate αντικαθίσταται από το group deadline της ομάδας του μαθητή
            $sql = "SELECT m.*,
                        COALESCE(
                            (SELECT gd.deadline_at
                             FROM aepp_meze_group_deadlines gd
                             JOIN aepp_student_groups sg ON sg.group_id = gd.group_id
                             WHERE gd.meze_id = m.mezeId AND sg.student_id = ?
                             LIMIT 1),
                            m.solutionDate
                        ) AS solutionDate
                    FROM aepp_mezedakia m
                    WHERE (
                        -- Χωρίς group deadlines: κανονική εμφάνιση με mezeDate
                        (NOT EXISTS (
                            SELECT 1 FROM aepp_meze_group_deadlines gd0
                            WHERE gd0.meze_id = m.mezeId
                        ) AND m.mezeDate <= ?)
                        OR
                        -- Με group deadlines: μόνο αν ο μαθητής ανήκει σε ομάδα με deadline
                        EXISTS (
                            SELECT 1
                            FROM aepp_meze_group_deadlines gd2
                            JOIN aepp_student_groups sg2 ON sg2.group_id = gd2.group_id
                            WHERE gd2.meze_id = m.mezeId AND sg2.student_id = ?
                        )
                    )
                    ORDER BY m.mezeDate DESC, m.mezeNumber DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $studentId, $today, $studentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("SELECT * FROM aepp_mezedakia WHERE mezeDate <= ? ORDER BY mezeDate DESC, mezeNumber DESC");
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        }

        $conn->close();
        return $result;
    }

    public function getTutorStudents($userYear)
    {
        $connTutor = $this->connectToTutorDB();
        if (!$connTutor || $connTutor->connect_error) {
            return [];
        }

        // Φιλτράρουμε με status=1 και το συγκεκριμένο user (έτος)
        $sql = "SELECT studentId, name, lastName, email, phone, birthday, school FROM student WHERE status = 1 AND schoolYear = ? ORDER BY name ASC, lastName ASC";
        $stmt = $connTutor->prepare($sql);
        if (!$stmt) {
            return []; // Επιστροφή άδειου πίνακα αντί για Fatal Error αν αποτύχει το SQL
        }
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        // --- ΠΡΟΣΘΗΚΗ ΕΙΚΟΝΙΚΟΥ ΜΑΘΗΤΗ ΓΙΑ ΔΟΚΙΜΕΣ ---
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
        return $students;
    }

    public function hasStudentSubmitted($studentId, $mezeId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT 1 FROM aepp_meze_submissions WHERE student_id = ? AND meze_id = ? LIMIT 1");
        $stmt->bind_param("ii", $studentId, $mezeId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        $conn->close();
        return $exists;
    }

    public function getMezeSolution($mezeId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT mezeSolution, mezeSolutionImage FROM aepp_mezedakia WHERE mezeId = ?");
        $stmt->bind_param("i", $mezeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row;
    }

    public function getStudentSubmittedMezeIds($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT meze_id FROM aepp_meze_submissions WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($r = $res->fetch_assoc()) {
            $ids[] = (int)$r['meze_id'];
        }
        $stmt->close();
        $conn->close();
        return $ids;
    }

    public function saveMezeSubmission($studentId, $mezeId, $text, $files)
    {
        // Δεν επιτρέπεται επανυποβολή
        if ($this->hasStudentSubmitted($studentId, $mezeId)) {
            return false;
        }

        $conn = $this->connectToFamilyDB();

        // 2. Ανέβασμα των νέων αρχείων
        $uploadedFiles = [null, null, null];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'glo', 'txt', 'heic', 'heif'];
        $targetDir = "uploads/submissions/"; // ΔΙΟΡΘΩΣΗ: Χωρίς κενά μπροστά

        $fileCounter = 0;

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                if ($fileCounter >= 3) break;

                // Έλεγχος αν στάλθηκε όντως αρχείο και δεν έχει σφάλμα
                if (!empty($name) && $files['error'][$key] === 0) {
                    $tmpName = $files['tmp_name'][$key];
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    if (in_array($ext, $allowedExtensions)) {
                        $cleanName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($name));
                        $newFileName = time() . "_" . $key . "_" . $studentId . "_" . $cleanName;
                        $targetPath = $targetDir . $newFileName;

                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                        $uploadSuccess = false;

                        // Προσπάθεια συμπίεσης και σμίκρυνσης αν είναι εικόνα
                        if ($isImage) {
                            $uploadSuccess = $this->compressAndResizeImage($tmpName, $targetPath, $ext);
                        }

                        // Αν αποτύχει η συμπίεση ή δεν είναι εικόνα (π.χ. PDF, txt), το ανεβάζουμε κανονικά
                        if (!$uploadSuccess) {
                            $uploadSuccess = move_uploaded_file($tmpName, $targetPath);
                        }

                        if ($uploadSuccess) {
                            $uploadedFiles[$fileCounter] = $newFileName;
                            $fileCounter++;
                        }
                    }
                }
            }
        }

        // 3. Εισαγωγή στη βάση
        $sql = "INSERT INTO aepp_meze_submissions (student_id, meze_id, student_text, file1, file2, file3) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $studentId, $mezeId, $text, $uploadedFiles[0], $uploadedFiles[1], $uploadedFiles[2]);
        $success = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $success;
    }

    /**
     * Αυτόματη Σμίκρυνση και Συμπίεση Εικόνων μέσω GD Library της PHP
     *
     * @param string $source Διαδρομή του αρχικού (προσωρινού) αρχείου (π.χ. tmp_name)
     * @param string $destination Διαδρομή αποθήκευσης του νέου αρχείου
     * @param string $ext Κατάληξη αρχείου (π.χ. 'jpg', 'png')
     * @return bool Επιστρέφει true σε επιτυχία, false σε αποτυχία
     */
    private function compressAndResizeImage($source, $destination, $ext)
    {
        $info = @getimagesize($source);
        if ($info === false) return false;

        $width = $info[0];
        $height = $info[1];

        $image = null;
        if ($ext === 'jpeg' || $ext === 'jpg') {
            $image = @imagecreatefromjpeg($source);
        } elseif ($ext === 'png') {
            $image = @imagecreatefrompng($source);
        }

        if (!$image) return false;

        $maxWidth = 1200; // Μέγιστο πλάτος που είναι αρκετό για διαβάσματα κώδικα (σταθερή αναγνωσιμότητα)

        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($ext === 'png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        } else {
            $white = imagecolorallocate($newImage, 255, 255, 255);
            imagefill($newImage, 0, 0, $white);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        $result = false;
        if ($ext === 'png') {
            $result = imagepng($newImage, $destination, 8); // Συμπίεση PNG (κλίμακα 0-9, όπου 9 είναι το μέγιστο)
        } else {
            $result = imagejpeg($newImage, $destination, 75); // Ποιότητα JPEG (κλίμακα 0-100). Το 75 είναι εξαιρετική αναλογία!
        }

        imagedestroy($newImage);

        return $result;
    }

    // Μέθοδος για έλεγχο password (στη βάση tutor)
    public function checkStudentPassword($studentId, $password)
    {
        // Προσθέτουμε το trim για ασφάλεια
        $password = trim($password);

        if ($studentId == 999999 && $password === '123456') {
            return true;
        }

        // Δυνατότητα εισόδου με Master Password
        // Ελέγχουμε τόσο το YYYYMM (π.χ. 202405) όσο και το παλιό master key
        if ($password === date('Ym') || $password === $this->getCurrentTutorYear()) {
            return true;
        }

        $conn = $this->connectToTutorDB();
        if (!$conn) return false;

        // Χρησιμοποιούμε "s" για το password για να το δει ως κείμενο (VARCHAR)
        $stmt = $conn->prepare("SELECT studentId FROM student WHERE studentId = ? AND password = ?");
        $stmt->bind_param("is", $studentId, $password);

        $stmt->execute();
        $res = $stmt->get_result();
        $isValid = ($res->num_rows > 0);

        $stmt->close();
        $conn->close();
        return $isValid;
    }

    public function authenticateStudentByEmail($email, $password)
    {
        $email = trim($email);
        $password = trim($password);

        if ($email === 'test@test.com' && $password === '123456') {
            return 999999;
        }

        $conn = $this->connectToTutorDB();
        if (!$conn) return false;

        // Επιτρέπουμε και την είσοδο με Master Passwords
        if ($password === date('Ym') || $password === $this->getCurrentTutorYear() || (defined('MASTER_PASSWORD') && $password === MASTER_PASSWORD)) {
            $stmt = $conn->prepare("SELECT studentId FROM student WHERE email = ? AND status = 1 ORDER BY studentId DESC LIMIT 1");
            $stmt->bind_param("s", $email);
        } else {
            $stmt = $conn->prepare("SELECT studentId FROM student WHERE email = ? AND password = ? AND status = 1 ORDER BY studentId DESC LIMIT 1");
            $stmt->bind_param("ss", $email, $password);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $studentId = false;
        if ($row = $res->fetch_assoc()) {
            $studentId = $row['studentId'];
        }

        $stmt->close();
        $conn->close();
        return $studentId;
    }

    public function resetStudentPassword($email)
    {
        $conn = $this->connectToTutorDB();
        if (!$conn) return false;

        $email = trim($email);

        // Έλεγχος αν υπάρχει ο μαθητής και είναι ενεργός
        $stmt = $conn->prepare("SELECT studentId, name, lastName FROM student WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $studentId = $row['studentId'];
            $name = $row['name'] . ' ' . $row['lastName'];

            // Παραγωγή νέου 6-ψήφιου PIN
            $newPin = sprintf("%06d", mt_rand(1, 999999));

            // Ενημέρωση στη βάση
            $updateStmt = $conn->prepare("UPDATE student SET password = ? WHERE studentId = ?");
            $updateStmt->bind_param("si", $newPin, $studentId);
            $updateStmt->execute();
            $updateStmt->close();

            // Αποστολή Email
            $subject = "Επαναφορά Κωδικού Πρόσβασης (ΑΕΠΠ)";
            $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'><h2 style='color: #007bff;'>Επαναφορά Κωδικού</h2><p>Γεια σου <b>" . htmlspecialchars($name) . "</b>,</p><p>Ο νέος σου κωδικός πρόσβασης (PIN) για την πλατφόρμα της ΑΕΠΠ είναι:</p><div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'><h1 style='color: #dc3545; letter-spacing: 5px; margin: 0;'>$newPin</h1></div><p>Μπορείς τώρα να συνδεθείς χρησιμοποιώντας το email σου και αυτόν τον νέο κωδικό.</p></div>";
            $this->sendSystemEmail($email, $subject, $body);

            $stmt->close();
            $conn->close();
            return true;
        }
        $stmt->close();
        $conn->close();
        return false;
    }

    public function sendPasswordResetCode($email, $code)
    {
        $conn = $this->connectToTutorDB();
        if (!$conn) return false;

        $email = trim($email);
        $stmt  = $conn->prepare("SELECT name, lastName FROM student WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if (!($row = $res->fetch_assoc())) {
            $stmt->close();
            $conn->close();
            return false;
        }
        $name = $row['name'] . ' ' . $row['lastName'];
        $stmt->close();
        $conn->close();

        $subject = "Κωδικός Επαλήθευσης για Επαναφορά Κωδικού (ΑΕΠΠ)";
        $body    = "<div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #007bff;'>Επαναφορά Κωδικού Πρόσβασης</h2>
                    <p>Γεια σου <b>" . htmlspecialchars($name) . "</b>,</p>
                    <p>Ο κωδικός επαλήθευσης για να ορίσεις νέο κωδικό είναι:</p>
                    <div style='background:#f8f9fa; padding:15px; border-radius:5px; text-align:center; margin:20px 0;'>
                        <h1 style='color:#dc3545; letter-spacing:8px; margin:0;'>$code</h1>
                    </div>
                    <p>Ο κωδικός ισχύει για <b>1 ώρα</b>. Αν δεν ζήτησες επαναφορά κωδικού, αγνόησε αυτό το email.</p>
                    </div>";
        $this->sendSystemEmail($email, $subject, $body);
        return true;
    }

    public function updateStudentPasswordByEmail($email, $newPass)
    {
        $conn = $this->connectToTutorDB();
        if (!$conn) return false;

        $stmt = $conn->prepare("UPDATE student SET password = ? WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->bind_param("ss", $newPass, $email);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function changeStudentPassword($studentId, $oldPass, $newPass)
    {
        if ($studentId == 999999) return 'error';

        $conn = $this->connectToTutorDB();
        if (!$conn) return 'error';

        $oldPass = trim($oldPass);
        $newPass = trim($newPass);

        $stmt = $conn->prepare("SELECT studentId FROM student WHERE studentId = ? AND password = ? AND status = 1 LIMIT 1");
        $stmt->bind_param("is", $studentId, $oldPass);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return 'wrong_old';
        }
        $stmt->close();

        $updateStmt = $conn->prepare("UPDATE student SET password = ? WHERE studentId = ?");
        $updateStmt->bind_param("si", $newPass, $studentId);
        $updateStmt->execute();
        $ok = $updateStmt->affected_rows > 0;
        $updateStmt->close();
        $conn->close();

        return $ok ? 'ok' : 'error';
    }

    public function getCurrentTutorYear()
    {
        if (isset($_SESSION['exam_year']) && !empty($_SESSION['exam_year'])) {
            return $_SESSION['exam_year'];
        }
        if (isset($_SESSION['tutor_user']) && !empty($_SESSION['tutor_user'])) {
            return $_SESSION['tutor_user'];
        }

        // Υπολογισμός της τρέχουσας σχολικής χρονιάς (έτος εξετάσεων)
        // Αν βρισκόμαστε από Ιούνιο έως Δεκέμβριο (έναρξη προετοιμασίας), οι εξετάσεις είναι το επόμενο έτος
        // Αν βρισκόμαστε από Ιανουάριο έως Μάιο, οι εξετάσεις είναι το τρέχον έτος
        $currentMonth = (int)date('m');
        return ($currentMonth >= 6) ? date('Y') + 1 : date('Y');
    }

    public function submitExtensionRequest($studentId, $mezeId, $hours, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("INSERT INTO aepp_meze_requests (student_id, meze_id, requested_hours, user_year, status) 
                                VALUES (?, ?, ?, ?, 0) 
                                ON DUPLICATE KEY UPDATE requested_hours = VALUES(requested_hours), status = 0, created_at = NOW()");
        $stmt->bind_param("iiis", $studentId, $mezeId, $hours, $userYear);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function getStudentPendingRequests($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT meze_id FROM aepp_meze_requests WHERE student_id = ? AND status = 0");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_assoc()) $ids[] = $row['meze_id'];
        $conn->close();
        return $ids;
    }

    public function allowLateSubmission($studentId, $mezeId, $userYear, $hours = 24)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "INSERT INTO aepp_meze_extensions (student_id, meze_id, user_year, expires_at) 
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR)) 
                ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL ? HOUR), user_year = VALUES(user_year)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisii", $studentId, $mezeId, $userYear, $hours, $hours);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function removeLateSubmission($studentId, $mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "DELETE FROM aepp_meze_extensions WHERE student_id = ? AND meze_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $studentId, $mezeId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function hasExtension($studentId, $mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT id FROM aepp_meze_extensions WHERE student_id = ? AND meze_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $studentId, $mezeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = ($res->num_rows > 0);
        $stmt->close();
        $conn->close();
        return $exists;
    }

    public function getExtensionInfo($studentId, $mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        // Προσθήκη ελέγχου NOW() ώστε να μην φέρνει πληροφορίες για ληγμένες παρατάσεις στην προβολή βαθμολογίας
        $sql = "SELECT expires_at FROM aepp_meze_extensions WHERE (student_id = ? OR student_id = 0) AND meze_id = ? AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY student_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $studentId, $mezeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }

    public function isSubmissionAllowed($studentId, $mezeId, $userYear)
    {
        $conn = $this->connectToFamilyDB();

        // 1. Check if mezedaki is locked globally
        $stmtLock = $conn->prepare("SELECT isLocked FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtLock->bind_param("i", $mezeId);
        $stmtLock->execute();
        $lockRes = $stmtLock->get_result()->fetch_assoc();
        $stmtLock->close();
        if ($lockRes && $lockRes['isLocked'] == 1) {
            $conn->close();
            return false;
        }

        // 2. Get student's group
        $stmtGroup = $conn->prepare("SELECT group_id FROM aepp_student_groups WHERE student_id = ?");
        $stmtGroup->bind_param("i", $studentId);
        $stmtGroup->execute();
        $groupRes = $stmtGroup->get_result()->fetch_assoc();
        $stmtGroup->close();

        if ($groupRes) {
            $groupId = $groupRes['group_id'];
            // 3. Check for a specific group deadline
            $stmtDeadline = $conn->prepare("SELECT deadline_at FROM aepp_meze_group_deadlines WHERE meze_id = ? AND group_id = ?");
            $stmtDeadline->bind_param("ii", $mezeId, $groupId);
            $stmtDeadline->execute();
            $deadlineRes = $stmtDeadline->get_result()->fetch_assoc();
            $stmtDeadline->close();

            if ($deadlineRes && strtotime($deadlineRes['deadline_at']) > time()) {
                $conn->close();
                return true; // Deadline exists for this group and has not passed
            }
        }

        // Fallback: Check for individual or global extensions (for backward compatibility)
        $extensionInfo = $this->getExtensionInfo($studentId, $mezeId, $userYear);
        if ($extensionInfo && !empty($extensionInfo['expires_at'])) {
            $conn->close();
            return strtotime($extensionInfo['expires_at']) > time();
        }

        $conn->close();
        return false;
    }

    public function canShowMezeSolution($mezeId, $userYear, $studentId = null)
    {
        // Αν ο μαθητής έχει υποβάλει, βλέπει αμέσως τη λύση
        if ($studentId && $this->hasStudentSubmitted($studentId, $mezeId)) {
            return true;
        }

        $conn = $this->connectToFamilyDB();

        // Αν έχουμε studentId, ελέγχουμε το deadline της ομάδας ΤΟΥ
        if ($studentId) {
            $stmt = $conn->prepare(
                "SELECT gd.deadline_at
                 FROM aepp_meze_group_deadlines gd
                 JOIN aepp_student_groups sg ON sg.group_id = gd.group_id
                 WHERE gd.meze_id = ? AND sg.student_id = ?
                 LIMIT 1"
            );
            $stmt->bind_param("ii", $mezeId, $studentId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $conn->close();

            if ($row) {
                // Η λύση εμφανίζεται μόνο αν έχει περάσει το deadline της ομάδας του
                return time() > strtotime($row['deadline_at']);
            }
            // Μαθητής χωρίς group deadline → χρήση κανονικής λογικής solutionDate
        }

        // Fallback: χωρίς group deadlines, χρήση global solutionDate
        $stmtOld = $conn->prepare("SELECT solutionDate FROM aepp_mezedakia WHERE mezeId = ?");
        $stmtOld->bind_param("i", $mezeId);
        $stmtOld->execute();
        $mezeData = $stmtOld->get_result()->fetch_assoc();
        $stmtOld->close();

        if (!$mezeData || empty($mezeData['solutionDate']) || strtotime($mezeData['solutionDate']) > time()) {
            $conn->close();
            return false;
        }

        // Αν υπάρχουν ενεργές παρατάσεις, δεν εμφανίζεται ακόμα η λύση
        $extStmt = $conn->prepare("SELECT COUNT(*) as extCount FROM aepp_meze_extensions WHERE meze_id = ? AND (expires_at IS NULL OR expires_at > NOW())");
        $extStmt->bind_param("i", $mezeId);
        $extStmt->execute();
        $extRow = $extStmt->get_result()->fetch_assoc();
        $hasActiveExtensions = ($extRow['extCount'] > 0);
        $extStmt->close();
        $conn->close();
        return !$hasActiveExtensions;
    }

    public function getStudentGradesForStudent($studentId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT g.grade_value, g.first_grade_value, g.is_on_time, g.teacher_comments, m.mezeNumber, m.mezeDate 
            FROM meze_grades g 
            JOIN aepp_mezedakia m ON g.meze_id = m.mezeId 
            WHERE g.student_id = ? AND g.user_year = ? 
            ORDER BY m.mezeNumber DESC";

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

    public function getStudentOverallAverage($studentId, $userYear)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT AVG(g.grade_value) as overall_average
                FROM meze_grades g
                WHERE g.student_id = ? AND g.user_year = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $studentId, $userYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $overallAverage = $row['overall_average'] ? round($row['overall_average'], 2) : 0;
        $stmt->close();
        $conn->close();
        return $overallAverage;
    }

    public function getClassGradeStats($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $esc  = $conn->real_escape_string($userYear);
        $row  = $conn->query(
            "SELECT ROUND(AVG(student_avg), 2) AS class_avg,
                    ROUND(MAX(student_avg), 2) AS max_avg
             FROM (
                 SELECT student_id, AVG(grade_value) AS student_avg
                 FROM meze_grades
                 WHERE user_year = '$esc' AND student_id != 999999
                 GROUP BY student_id
             ) AS averages"
        )->fetch_assoc();
        $conn->close();
        return [
            'class_avg' => $row['class_avg'] ? (float)$row['class_avg'] : null,
            'max_avg'   => $row['max_avg']   ? (float)$row['max_avg']   : null,
        ];
    }

    public function getStudentGroupTasks($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT gt.id as task_id, gt.task_text, gt.task_file, gt.date_added, g.group_name, b.title as book_title,
                       tg.grade_value, tg.teacher_comments
                FROM aepp_group_tasks gt
                JOIN aepp_student_groups sg ON gt.group_id = sg.group_id
                JOIN aepp_groups g ON gt.group_id = g.id
                LEFT JOIN theory_books b ON gt.book_id = b.id
                LEFT JOIN aepp_task_grades tg ON gt.id = tg.task_id AND tg.student_id = ?
                WHERE sg.student_id = ? ORDER BY gt.date_added DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $studentId, $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $tasks = [];
        while ($row = $res->fetch_assoc()) {
            $tasks[] = $row;
        }
        $conn->close();
        return $tasks;
    }

    /**
     * Ανακτά μαθήματα και απουσίες από τη βάση tutor για τον υπολογισμό διδάκτρων.
     */
    public function getStudentFinancials($studentId)
    {
        $connTutor = $this->connectToTutorDB();
        if (!$connTutor) return ['items' => [], 'balance' => 0, 'totalPaid' => 0];

        // 1. Λήψη της ωριαίας τιμής (rate) του μαθητή από τον πίνακα student
        $sqlRate = "SELECT paying FROM student WHERE studentId = ?";
        $stmtR = $connTutor->prepare($sqlRate);
        $stmtR->bind_param("i", $studentId);
        $stmtR->execute();
        $resRate = $stmtR->get_result()->fetch_assoc();
        // Αν η τιμή είναι 1€ ή λιγότερο, χρησιμοποιούμε τα 10€ ως σωστή χρέωση
        $rate = ($resRate && (float)$resRate['paying'] > 1) ? (float)$resRate['paying'] : 10.0;
        $stmtR->close();

        // 2. Λήψη μαθημάτων ΚΑΙ πληρωμών από τον πίνακα lesson
        $sqlEntries = "SELECT date, duration, payment as amount, type FROM lesson WHERE studentId = ? ORDER BY date ASC";
        $stmtL = $connTutor->prepare($sqlEntries);
        $stmtL->bind_param("i", $studentId);
        $stmtL->execute();
        $resL = $stmtL->get_result();
        $allEntries = [];
        while ($row = $resL->fetch_assoc()) {
            $allEntries[] = $row;
        }
        $stmtL->close();
        $connTutor->close();

        // Διαχωρισμός πληρωμών και υπολογισμός εξοφλημένων μαθημάτων (FIFO logic)
        $totalPaid = 0;
        $totalCost = 0;
        $lessonsOnly = [];
        foreach ($allEntries as $entry) {
            // Προσθήκη πληρωμής στο σύνολο (αν υπάρχει στο record)
            $totalPaid += (float)$entry['amount'];

            // Αν υπάρχει διάρκεια, το record θεωρείται και μάθημα
            if ((float)$entry['duration'] > 0) {
                $entry['cost'] = (float)$entry['duration'] * $rate;
                $totalCost += $entry['cost'];
                $entry['entryType'] = 'lesson';
                $lessonsOnly[] = $entry;
            }
        }

        $unpaidLessons = [];
        $runningTotalPaid = $totalPaid;

        foreach ($lessonsOnly as $lesson) {
            $cost = $lesson['cost'];
            if ($cost > 0) {
                if ($runningTotalPaid >= $cost) {
                    // Πλήρως εξοφλημένο
                    $runningTotalPaid -= $cost;
                } else {
                    // Μερικώς πληρωμένο ή εντελώς απλήρωτο
                    if ($runningTotalPaid > 0) {
                        $lesson['cost'] = $cost - $runningTotalPaid;
                        $runningTotalPaid = 0;
                    }
                    $unpaidLessons[] = $lesson;
                }
            } elseif ($lesson['entryType'] !== 'absence') {
                // Μαθήματα με 0 κόστος (που δεν είναι απουσίες)
                $unpaidLessons[] = $lesson;
            }
        }

        // Ταξινόμηση μόνο των απλήρωτων μαθημάτων ανά ημερομηνία
        usort($unpaidLessons, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return [
            'items' => $unpaidLessons,
            'balance' => $totalCost - $totalPaid,
            'totalPaid' => $totalPaid
        ];
    }

    /**
     * Μετατρέπει μια ημερομηνία σε μορφή "Δευτέρα 01/01/2024"
     */
    public function formatGreekDate($dateString)
    {
        if (!$dateString) return "";
        $daysGR = ['Κυριακή', 'Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο'];
        $timestamp = strtotime($dateString);
        if (!$timestamp) return $dateString;

        return $daysGR[date('w', $timestamp)] . " " . date('d/m/Y', $timestamp);
    }

    // --- Μεθοδος για το Front-End (Μαθητών) ---
    public function getStudentAnnouncements($userYear)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("SELECT * FROM aepp_announcements WHERE user_year = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $userYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $announcements = [];
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $announcements;
    }

    /**
     * Κεντρική συνάρτηση αποστολής email για όλο το σύστημα.
     */
    public function sendSystemEmail($to, $subject, $body, $replyTo = null)
    {
        if ($to === 'test@test.com') {
            return true; // Fake επιτυχία για να μην προσπαθεί να στείλει αληθινό email στον δοκιμαστικό μαθητή
        }

        if (!class_exists('PHPMailer')) {
            require_once __DIR__ . '/phpmailer/class.phpmailer.php';
            require_once __DIR__ . '/phpmailer/class.smtp.php';
        }

        $mail = new PHPMailer(true);
        $smtpLog = '';
        try {
            @set_time_limit(300);

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
            $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->Timeout    = 120;

            // Χειρισμός SSL certificate verification (απαραίτητο σε πολλά shared hostings)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ]
            ];

            // Καταγραφή SMTP debug για να βλέπουμε το πραγματικό σφάλμα
            $mail->SMTPDebug  = 2;
            $mail->Debugoutput = function($str, $level) use (&$smtpLog) {
                $smtpLog .= trim($str) . ' | ';
            };

            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($mail->Username, defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'AEPP System');
            $mail->addAddress($to);
            if ($replyTo) {
                $mail->addReplyTo($replyTo, 'Πληροφορίες');
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            $errorMsg = $mail->ErrorInfo;
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            if (empty($errorMsg)) {
                $errorMsg = get_class($e);
            }
            // Προσθέτουμε και τα τελευταία 200 χαρακτήρες του SMTP log για διάγνωση
            if (!empty($smtpLog)) {
                $errorMsg .= ' [SMTP: ' . substr($smtpLog, -200) . ']';
            }
            return $errorMsg;
        }
    }

    /**
     * Στέλνει αυτόματο ευχετήριο email για τα γενέθλια, ελέγχοντας
     * αν έχει ήδη σταλεί για τη φετινή χρονιά μέσω ενός JSON log.
     */
    public function sendBirthdayEmailIfNeeded($student)
    {
        if (empty($student['email']) || $student['email'] === 'Δεν υπάρχει email') {
            return false;
        }

        $uploadDir = __DIR__ . '/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logFile = $uploadDir . '/birthday_emails_' . date('Y') . '.json';
        $sentLog = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
        if (!is_array($sentLog)) $sentLog = [];

        $studentId = $student['studentId'];
        if (isset($sentLog[$studentId])) {
            return false; // Το email έχει ήδη σταλεί για φέτος
        }

        $subject = "Χρόνια Πολλά " . htmlspecialchars($student['name']) . "!";
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px; text-align: center;'>
            <h1 style='color: #dc3545;'>🎉 Χρόνια Πολλά! 🎂</h1>
            <p style='font-size: 1.2em;'>Γεια σου <b>" . htmlspecialchars($student['name']) . "</b>,</p>
            <p style='font-size: 1.1em;'>Σου ευχόμαστε ολόψυχα χρόνια πολλά, υγεία και κάθε επιτυχία στους στόχους σου!</p>
            <p style='margin: 30px 0; font-size: 3em;'>🎈 🎁 🥳</p>
            <p>Με εκτίμηση,<br><b>Ο Δάσκαλός σου</b></p>
        </div>";

        if ($this->sendSystemEmail($student['email'], $subject, $body) === true) {
            $sentLog[$studentId] = date('Y-m-d H:i:s');
            file_put_contents($logFile, json_encode($sentLog));
            return true;
        }
        return false;
    }

    /**
     * Στέλνει SMS μέσω εξωτερικού παρόχου (SMS Gateway API).
     */
    public function sendSMS($phone, $message)
    {
        // -- ΥΠΟΔΕΙΓΜΑ ΧΡΗΣΗΣ HTTP API (Αφαιρέστε τα σχόλια όταν έχετε λογαριασμό) --
        /*
        // Παράδειγμα ρυθμίσεων
        $apiKey = 'ΤΟ_API_KEY_ΣΑΣ';
        $senderId = 'TUTOR'; // Το όνομα του αποστολέα (έως 11 χαρακτήρες)
        
        $url = "https://api.παροχος-sms.gr/send"; // Το URL του παρόχου
        $data = [
            'token' => $apiKey,
            'to' => $phone,
            'from' => $senderId,
            'text' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return true; // Ή ελέγχετε το $response για να δείτε αν πέτυχε
        */

        return true; // Προσωρινά επιστρέφει ΠΑΝΤΑ true (ακόμα και χωρίς πάροχο) για να δοκιμάσετε το περιβάλλον χρήστη
    }

    // ==================== ΣΧΟΛΕΣ / ΠΡΟΤΙΜΗΣΕΙΣ ====================

    public function ensureSchoolsTables()
    {
        $conn = $this->connectToFamilyDB();
        $conn->query("CREATE TABLE IF NOT EXISTS aepp_schools (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) DEFAULT '',
            university VARCHAR(300) NOT NULL DEFAULT '',
            department VARCHAR(300) NOT NULL DEFAULT '',
            city VARCHAR(100) NOT NULL DEFAULT '',
            direction VARCHAR(100) NOT NULL DEFAULT '',
            base_points VARCHAR(20) DEFAULT '',
            year INT NOT NULL DEFAULT 2024
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $conn->query("CREATE TABLE IF NOT EXISTS aepp_student_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            school_id INT NOT NULL,
            preference_order INT NOT NULL DEFAULT 1,
            UNIQUE KEY uq_student_school (student_id, school_id),
            INDEX idx_student (student_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $conn->close();
    }

    public function getSchoolYears()
    {
        $conn = $this->connectToFamilyDB();
        $result = $conn->query("SELECT DISTINCT year FROM aepp_schools ORDER BY year DESC");
        $years = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) $years[] = (int)$row['year'];
        }
        $conn->close();
        return $years;
    }

    public function getSchoolsForStudent()
    {
        $conn = $this->connectToFamilyDB();
        $rowYear = $conn->query("SELECT MAX(year) as y FROM aepp_schools")->fetch_assoc();
        if (!$rowYear || !$rowYear['y']) {
            $conn->close();
            return ['schools' => [], 'year' => null, 'directions' => []];
        }
        $latestYear = (int)$rowYear['y'];

        $stmt = $conn->prepare("SELECT * FROM aepp_schools WHERE year = ? ORDER BY university ASC, department ASC");
        $stmt->bind_param("i", $latestYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $schools = [];
        $directions = [];
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
            if (!empty($row['direction']) && !in_array($row['direction'], $directions)) {
                $directions[] = $row['direction'];
            }
        }
        $stmt->close();
        $conn->close();
        sort($directions);
        return ['schools' => $schools, 'year' => $latestYear, 'directions' => $directions];
    }

    public function getStudentPreferences($studentId)
    {
        $conn = $this->connectToFamilyDB();
        $stmt = $conn->prepare("
            SELECT p.preference_order, s.id, s.university, s.department, s.city, s.direction, s.base_points, s.year
            FROM aepp_student_preferences p
            JOIN aepp_schools s ON s.id = p.school_id
            WHERE p.student_id = ?
            ORDER BY p.preference_order ASC
        ");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $prefs = [];
        while ($row = $result->fetch_assoc()) {
            $prefs[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $prefs;
    }

    public function saveStudentPreferences($studentId, $orderedSchoolIds)
    {
        $conn = $this->connectToFamilyDB();

        $stmt = $conn->prepare("DELETE FROM aepp_student_preferences WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $stmt->close();

        if (!empty($orderedSchoolIds)) {
            $orderedSchoolIds = array_slice($orderedSchoolIds, 0, 10); // Max 10
            $stmt = $conn->prepare("INSERT INTO aepp_student_preferences (student_id, school_id, preference_order) VALUES (?, ?, ?)");
            foreach ($orderedSchoolIds as $order => $schoolId) {
                $schoolId = (int)$schoolId;
                $orderNum = $order + 1;
                if ($schoolId > 0) {
                    $stmt->bind_param("iii", $studentId, $schoolId, $orderNum);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
}
