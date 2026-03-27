<?php

class DbHandler
{

    public function connectToFamilyDB()
    {
        $servername = "jhouv.eu";
        $username = "familyUser";
        $password = "Geo@1994!";
        $dbname = "familyDB";
        $conn = new mysqli($servername, $username, $password, $dbname);
        mysqli_set_charset($conn, "utf8");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            //echo 'welcome ha';
        }
        return $conn;
    }

    public function login($username, $password)
    {
        $conn = $this->connectToFamilyDB();
        $sql = "SELECT * FROM italiano_user WHERE username = '" . $username . "' AND password = '" . $password . "'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            return true;
        } else {
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
        $sql = "INSERT INTO aepp_themata (school,year,thema,file,period,section,type) VALUES ('$school',$year,'$thema','$file','$period','$section','$type')";
        if ($conn->query($sql) === TRUE) {
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }

    public function addNote()
    {
        $conn = $this->connectToFamilyDB();
        $note = $_POST['note'];
        $date = $_POST['date'];
        if (isset($_POST['submitNote'])) {
            //echo 'eeeeeeeeeeeeeeeeeeee';
            $sql = "INSERT INTO italiano_note (note,date) VALUES ('" . $note . "','" . $date . "')";
            if ($conn->query($sql) === TRUE) {
                echo "Η σημείωση αποθηκεύτηκε";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
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
        $sql = "SELECT DISTINCT chapter_num FROM theory_questions ORDER BY chapter_num ASC";
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
            ORDER BY b.id ASC, q.chapter_num ASC, q.id ASC";
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

    public function getAllMezedakia()
    {
        $conn = $this->connectToFamilyDB();
        $today = date('Y-m-d');
        // Φέρνει μόνο τα μεζεδάκια που η ημερομηνία τους έχει φτάσει ή περάσει
        $sql = "SELECT * FROM aepp_mezedakia 
            WHERE mezeDate <= '$today' 
            ORDER BY mezeDate DESC, mezeNumber DESC";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }
}
