<?php

class FormMaker
{

    public function addPanelliniesForm()
    {
?>
        <h1>Εισαγωγή Άσκησης Πανελληνίων</h1>
        <div class="container">
            <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" enctype="multipart/form-data">
                <?php
                $this->selectYear();
                $this->selectThema();
                $this->selectSchool();
                $this->selectPeriod();
                $this->selectSection();
                $this->selectType();
                $this->selectType2();
                ?>
                <div class="form-group">
                    Select image to upload:
                    <input type="file" name="fileToUpload" id="fileToUpload" required>
                </div>
                <button type="submit" class="btn btn-primary" value="uploadImage" name="submit">Υποβολή</button>
            </form>
        </div>
    <?php
    }

    public function getThemataForm()
    {
    ?>
        <h1>Αναζήτηση Θεμάτων</h1>
        <div class="container">
            <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']) ?> " method="post">
                <?php $this->selectYear(); ?>
                <?php $this->selectThema(); ?>
                <?php $this->selectSchool(); ?>
                <?php $this->selectType(); ?>

                <button type="submit" class="btn btn-primary" name="getThemata">Υποβολή</button>
            </form>
        </div>
    <?php
    }

    public function selectYear()
    {
        $currentYear = date("Y");
    ?>
        <select name="year" class="form-control">
            <option value="all">Όλα τα έτη</option> <?php
                                                    for ($i = $currentYear; $i >= 2000; $i--) {
                                                        echo "<option value='$i'>$i</option>";
                                                    }
                                                    ?>
        </select>
    <?php
    }
    public function selectThema()
    {
    ?>
        <div class="form-group">
            <lable for="thema">Θέμα:</lable>
            <select class="form-control" id="thema" name="thema">
                <option value="Α">Α</option>
                <option value="Β">Β</option>
                <option value="Γ">Γ</option>
                <option value="Δ">Δ</option>
            </select>
        </div>
    <?php
    }

    public function selectSection()
    {
    ?>
        <div class="form-group">
            <lable for="section">Ερώτημα:</lable>
            <select class="form-control" id="section" name="section">
                <option value=""></option>
                <option value="α">α</option>
                <option value="β">β</option>
                <option value="γ">γ</option>
                <option value="δ">δ</option>
                <option value="ε">ε</option>
            </select>
        </div>
    <?php
    }

    public function selectType()
    {
    ?>
        <div class="form-group">
            <label for="type">Τύπος</label>
            <select class="form-control" id="type" name="type">
                <option value=""></option>
                <option value="Αλγόριθμος - κενά">Αλγόριθμος - κενά</option>
                <option value="Ανάπτυξης">Ανάπτυξης</option>
                <option value="Αντιστοίχισης">Αντιστοίχισης</option>
                <option value="Διάγραμμα Ροής">Διάγραμμα Ροής</option>
                <option value="Πίνακας τιμών">Πίνακας τιμών</option>
                <option value="Σωστό - Λάθος">Σωστό - Λάθος</option>
            </select>
        </div>
    <?php
    }

    public function selectType2()
    {
    ?>
        <div class="form-group">
            <label for="type2">Τύπος</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="check1" name="pinakes" value="Πίνακες">
                <label class="form-check-label" for="check1">Πίνακες</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="check2" name="ypoprogrammata" value="Υποπρογράμματα">
                <label class="form-check-label" for="check2">Υποπρογράμματα</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="check2" name="akatallili" value="Ακατάλληλη τιμή">
                <label class="form-check-label" for="check2">Ακατάλληλη τιμή</label>
            </div>
        </div>

    <?php
    }

    public function selectType3()
    {
    ?>
        <div class="form-group">
            <label for="type">Πίνακες</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="radio1" name="optradio" value="yes" required>Ναι
                <label class="form-check-label" for="radio1"></label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="radio2" name="optradio" value="no">Όχι
                <label class="form-check-label" for="radio2"></label>
            </div>
        </div>
    <?php
    }

    public function selectSchool()
    {
    ?>
        <div class="form-group">
            <label for="school">Λύκειο</label>
            <select class="form-control" id="school" name="school">
                <option value="Ημερήσιο">Ημερήσιο</option>
                <option value="Εσπερινό">Εσπερινό</option>
            </select>
        </div>
    <?php
    }

    public function selectPeriod()
    {
    ?>
        <div class="form-group">
            <label for="period">Περίοδος</label>
            <select class="form-control" id="period" name="period">
                <option value="Απολυτήριες">Απολυτήριες</option>
                <option value="Επαναληπτικές">Επαναληπτικές</option>
            </select>
        </div>
    <?php
    }

    public function addTheoryForm($booksResult)
    {
    ?>
        <div class="container mt-4 border p-4 bg-light shadow-sm">
            <h3 class="mb-4">Νέα Ερώτηση Θεωρίας</h3>
            <form action="" method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control">
                            <?php
                            if ($booksResult) {
                                while ($row = $booksResult->fetch_assoc()) {
                            ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Κεφάλαιο</label>
                        <input type="text" name="chapter_num" class="form-control" placeholder="π.χ. 2.1">
                    </div>
                </div>
                <div class="form-group">
                    <label>Ερώτηση</label>
                    <input type="text" name="question_text" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Απάντηση</label>
                    <textarea name="answer_text" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Σελίδα Βιβλίου</label>
                    <input type="number" name="page_number" class="form-control">
                </div>
                <button type="submit" name="submitTheory" class="btn btn-success">Αποθήκευση στη Βάση</button>
            </form>
        </div>
    <?php
    }

    public function getThemataGDForm()
    {
    ?>
        <div class="container mt-4">
            <h2 class="text-center mb-4"><i class="fa fa-search"></i> Αναζήτηση Θεμάτων Γ & Δ</h2>
            <div class="card p-4 shadow-sm bg-light">
                <form action="index.php?action=viewThemaGD" method="post">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label><i class="fa fa-list-ol"></i> Επιλογή Θέματος</label>
                            <select name="thema_type" class="form-control">
                                <option value="G">Θέμα Γ (Πρόγραμμα)</option>
                                <option value="D">Θέμα Δ (Πρόγραμμα)</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label><i class="fa fa-calendar"></i> Έτος</label>
                            <?php $this->selectYear(); ?>
                        </div>

                        <div class="form-group col-md-3">
                            <label><i class="fa fa-university"></i> Τύπος Σχολείου</label>
                            <select name="typosSxoleiou" class="form-control">
                                <option value="Hmerisio">Ημερήσιο</option>
                                <option value="Esperino">Εσπερινό</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label><i class="fa fa-pencil-square-o"></i> Τύπος Εξετάσεων</label>
                            <select name="typosEksetaseon" class="form-control">
                                <option value="Kanonikes">Κανονικές</option>
                                <option value="Epanaliptikes">Επαναληπτικές</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-block" name="viewThemaGD">
                            <i class="fa fa-eye"></i> Εμφάνιση Θέματος
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    }

    public function studentSubmissionForm($studentsArray, $mezeId)
    {
    ?>
        <div class="card mt-4 shadow border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-upload"></i> Υποβολή Λύσης</h5>
            </div>
            <div class="card-body">
                <form action="index.php?action=submitMezeAnswer" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Επίλεξε το όνομά σου:</label>
                            <select name="student_id" class="form-control" required>
                                <option value="">-- Ποιος είσαι; --</option>
                                <?php
                                // Χρησιμοποιούμε foreach γιατί το $studentsArray είναι πλέον πίνακας
                                if (!empty($studentsArray)) {
                                    foreach ($studentsArray as $student) {
                                        echo "<option value='" . $student['studentId'] . "'>" . $student['name'] . " " . $student['lastName'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>6-ψήφιος Κωδικός:</label>
                            <input type="password" name="pass" class="form-control" maxlength="6" placeholder="******" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Σχόλια ή Απάντηση (αν θέλεις):</label>
                        <textarea name="student_text" class="form-control" rows="3" placeholder="Γράψε εδώ αν θέλεις να μου πεις κάτι..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ανέβασε τις φωτογραφίες σου (έως 3):</label>
                        <input type="file" name="files[]" class="form-control-file mb-2" multiple>
                        <small class="text-muted">Tip: Μπορείς να επιλέξετε 2 ή 3 αρχεία μαζί κρατώντας πατημένο το Ctrl.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block shadow">
                        <i class="fa fa-paper-plane"></i> Αποστολή στον Δάσκαλο
                    </button>
                </form>
            </div>
        </div>
<?php
    }
}
