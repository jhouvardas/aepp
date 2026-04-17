<?php
include_once '../FormMaker.php';

class AdminFormMaker extends FormMaker
{

    public function addTheoryForm($booksResult)
    {
?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-plus-circle"></i> Εισαγωγή Νέας Ερώτησης</h3>
            <hr>
            <form action="index.php?action=save_theory" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control">
                            <?php
                            if ($booksResult) {
                                while ($row = $booksResult->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['title'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Κεφάλαιο</label>
                        <input type="text" name="chapter_num" class="form-control" placeholder="π.χ. 2.1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ερώτηση (Κείμενο & Formatting)</label>
                    <textarea name="question_text" id="editor_question_add" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Εικόνα Ερώτησης (Upload)</label>
                    <input type="file" name="q_image" class="form-control-file border p-1 w-100 bg-white">
                    <small class="form-text text-muted">Επιλέξτε εικόνα αν η ερώτηση απαιτεί διάγραμμα ή σχήμα.</small>
                </div>

                <div class="form-group">
                    <label>Απάντηση (Κείμενο & Formatting)</label>
                    <textarea name="answer_text" id="editor_answer_add" class="form-control" rows="6"></textarea>
                </div>

                <div class="form-group">
                    <label>Εικόνα Απάντησης (Upload)</label>
                    <input type="file" name="a_image" class="form-control-file border p-1 w-100 bg-white">
                    <small class="form-text text-muted">Επιλέξτε εικόνα αν η απάντηση περιέχει διάγραμμα ροής ή πίνακα τιμών.</small>
                </div>

                <div class="form-group">
                    <label>Σελίδα Βιβλίου</label>
                    <input type="number" name="page_number" class="form-control" placeholder="π.χ. 42">
                </div>

                <button type="submit" name="submitTheory" class="btn btn-danger btn-block btn-lg shadow-sm">
                    <i class="fa fa-save"></i> Αποθήκευση στη Βάση
                </button>
            </form>
        </div>

        <script>
            // Ενεργοποίηση Editor για την Ερώτηση
            ClassicEditor
                .create(document.querySelector('#editor_question_add'), {
                    toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
                })
                .catch(error => {
                    console.error('Error in Question Editor:', error);
                });

            // Ενεργοποίηση Editor για την Απάντηση
            ClassicEditor
                .create(document.querySelector('#editor_answer_add'), {
                    toolbar: [
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'blockQuote', 'insertTable', 'undo', 'redo'
                    ]
                })
                .catch(error => {
                    console.error('Error in Answer Editor:', error);
                });
        </script>
    <?php
    }

    public function manageBooksForm($booksResult)
    {
    ?>
        <div class="container mt-4">
            <h3>Διαχείριση Βιβλίων</h3>
            <div class="card p-3 mb-4">
                <h5>Προσθήκη Νέου Βιβλίου</h5>
                <form action="index.php?action=save_book" method="post" class="form-inline">
                    <input type="text" name="book_title" class="form-control mr-2" placeholder="Τίτλος Βιβλίου" required>
                    <button type="submit" class="btn btn-success">Προσθήκη</button>
                </form>
            </div>

            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Τίτλος Βιβλίου</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $booksResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td>
                                <a href="index.php?action=delete_book&id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Σίγουρα διαγραφή;');">Διαγραφή</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function editTheoryForm($questionData, $booksResult)
    {
    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-edit"></i> Επεξεργασία Ερώτησης</h3>
            <hr>
            <form action="index.php?action=update_theory" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $questionData['id']; ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control">
                            <?php while ($row = $booksResult->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $questionData['book_id']) echo 'selected'; ?>>
                                    <?php echo $row['title']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Κεφάλαιο</label>
                        <input type="text" name="chapter_num" class="form-control" value="<?php echo htmlspecialchars($questionData['chapter_num']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ερώτηση (Κείμενο & Formatting)</label>
                    <textarea name="question_text" id="editor_question_edit" class="form-control" rows="3"><?php echo $questionData['question_text']; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Εικόνα Ερώτησης (Upload)</label>
                    <?php if (!empty($questionData['question_image'])): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">Τρέχουσα εικόνα:</small>
                            <img src="../uploads/<?php echo $questionData['question_image']; ?>" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="q_image" class="form-control-file border p-1 w-100 bg-white shadow-sm">
                    <small class="text-muted">Ανεβάστε νέα εικόνα μόνο αν θέλετε να την αλλάξετε.</small>
                </div>

                <div class="form-group">
                    <label>Απάντηση (Κείμενο & Formatting)</label>
                    <textarea name="answer_text" id="editor_answer_edit" class="form-control"><?php echo $questionData['answer_text']; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Εικόνα Απάντησης (Upload)</label>
                    <?php if (!empty($questionData['answer_image'])): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">Τρέχουσα εικόνα:</small>
                            <img src="../uploads/<?php echo $questionData['answer_image']; ?>" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="a_image" class="form-control-file border p-1 w-100 bg-white shadow-sm">
                    <small class="text-muted">Ανεβάστε νέα εικόνα μόνο αν θέλετε να την αλλάξετε.</small>
                </div>

                <div class="form-group">
                    <label>Σελίδα</label>
                    <input type="number" name="page_number" class="form-control" value="<?php echo $questionData['page_number']; ?>">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-block btn-lg shadow">
                        <i class="fa fa-save"></i> Ενημέρωση Αλλαγών
                    </button>
                    <a href="index.php?action=list_theory" class="btn btn-secondary btn-block">Ακύρωση</a>
                </div>
            </form>
        </div>

        <script>
            // Ενεργοποίηση Editor για την Ερώτηση
            ClassicEditor
                .create(document.querySelector('#editor_question_edit'), {
                    toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
                })
                .catch(error => {
                    console.error('Error in Question Editor:', error);
                });

            // Ενεργοποίηση Editor για την Απάντηση
            ClassicEditor
                .create(document.querySelector('#editor_answer_edit'), {
                    toolbar: [
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'blockQuote', 'insertTable', 'undo', 'redo'
                    ]
                })
                .catch(error => {
                    console.error('Error in Answer Editor:', error);
                });
        </script>
    <?php
    }

    public function listTheoryQuestions($questionsResult)
    {
    ?>
        <div class="container mt-4">
            <h3>Λίστα Ερωτήσεων Θεωρίας</h3>
            <table class="table table-bordered table-hover shadow-sm bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>Βιβλίο</th>
                        <th>Κεφ.</th>
                        <th>Ερώτηση</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($questionsResult && $questionsResult->num_rows > 0) {
                        while ($row = $questionsResult->fetch_assoc()) {
                            // ΕΛΕΓΧΟΣ: Αν δεν υπάρχει κείμενο, βάλε μια ένδειξη
                            $displayText = strip_tags($row['question_text']);
                            if (empty(trim($displayText))) {
                                $displayText = '<i class="text-muted">[Μόνο Εικόνα]</i>';
                            }
                    ?>
                            <tr>
                                <td><small><?php echo htmlspecialchars($row['book_title']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['chapter_num']); ?></td>
                                <td><?php echo $displayText; ?></td>
                                <td>
                                    <a href="index.php?action=edit_theory&id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info">Διόρθωση</a>

                                    <a href="index.php?action=delete_theory&id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Προσοχή! Θέλετε σίγουρα να διαγράψετε αυτή την ερώτηση;');">Διαγραφή</a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>Δεν υπάρχουν καταχωρημένες ερωτήσεις.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function listTheoryQuestionsForTests($questionsResult)
    {
    ?>
        <div class="container mt-4">
            <div class="alert alert-info shadow-sm">
                <i class="fa fa-info-circle"></i> Επιλέξτε τις ερωτήσεις που επιθυμείτε και πατήστε το κουμπί στο τέλος της σελίδας για να δημιουργήσετε το αρχείο PDF.
            </div>

            <form action="index.php?action=create_exam" method="post">
                <table class="table table-bordered table-hover shadow-sm bg-white">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 50px;" class="text-center">Επιλογή</th>
                            <th>Βιβλίο / Κεφάλαιο</th>
                            <th>Ερώτηση</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($questionsResult && $questionsResult->num_rows > 0) {
                            while ($row = $questionsResult->fetch_assoc()) {
                                $displayText = strip_tags($row['question_text']);
                                if (empty(trim($displayText))) {
                                    $displayText = '<i class="text-muted">[Μόνο Εικόνα]</i>';
                                }
                        ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected_questions[]" value="<?php echo $row['id']; ?>" style="transform: scale(1.5);">
                                    </td>
                                    <td>
                                        <small class="d-block text-muted"><?php echo htmlspecialchars($row['book_title']); ?></small>
                                        <strong>Κεφ: <?php echo htmlspecialchars($row['chapter_num']); ?></strong>
                                    </td>
                                    <td><?php echo $displayText; ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <div class="sticky-top bg-light p-3 border-bottom shadow-sm text-right" style="top: 0; z-index: 1020;">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-file-pdf-o"></i> Δημιουργία Εργασίας (PDF)
                    </button>
                </div>
            </form>
        </div>
    <?php
    }

    public function previewExam($questions)
    {
    ?>
        <div class="container mt-4 bg-white p-5 shadow border exam-sheet" id="printableArea">
            <div class="text-center mb-4">
                <h2 class="exam-title-underline">ΦΥΛΛΟ ΕΡΓΑΣΙΑΣ / ΔΙΑΓΩΝΙΣΜΑ</h2>
                <h4>Μάθημα: ΑΕΠΠ</h4>
                <div class="d-flex justify-content-between mt-3">
                    <span>Ονοματεπώνυμο: .......................................................................</span>
                    <span>Ημερομηνία: ...... / ...... / 202...</span>
                </div>
                <hr class="mt-3 exam-hr-bold">
            </div>

            <div class="exam-content">
                <?php
                $i = 1;
                while ($row = $questions->fetch_assoc()): ?>
                    <div class="question-block mb-2">
                        <div class="question-block-text">
                            <div class="mb-1">
                                <strong class="mr-1">Θέμα <?php echo $i++; ?>:</strong>
                                <?php echo $row['question_text']; ?>
                            </div>
                        </div>

                        <?php if (!empty($row['question_image'])): ?>
                            <div class="text-center mt-2 mb-2">
                                <img src="../uploads/<?php echo $row['question_image']; ?>" class="img-fluid border exam-img">
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            </div>

            <div class="no-print mt-5 text-center border-top pt-4">
                <button onclick="window.print();" class="btn btn-success btn-lg">
                    <i class="fa fa-print"></i> Εκτύπωση / PDF
                </button>
                <a href="index.php?action=list_for_test" class="btn btn-secondary btn-lg ml-2">
                    Επιστροφή
                </a>
            </div>
        </div>
    <?php
    }

    // Μέσα στην AdminFormMaker.php
    public function displayKenaForm()
    {
    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-code"></i> Εισαγωγή Νέας Άσκησης (HTML ή Εικόνα)</h3>
            <hr>
            <form action="index.php?action=saveKena" method="post" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Έτος</label>
                        <input type="number" name="exerciseYear" class="form-control" min="2000" max="2030" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Είδος</label>
                        <select name="examType" class="form-control">
                            <option value="Κανονικές">Κανονικές</option>
                            <option value="Επαναληπτικές">Επαναληπτικές</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Σχολείο</label>
                        <select name="schoolType" class="form-control">
                            <option value="Ημερήσια">Ημερήσια</option>
                            <option value="Εσπερινά">Εσπερινά</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label>Περιγραφή (Προαιρετικά)</label>
                    <textarea name="exerciseDescription" class="form-control" rows="1"></textarea>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-success font-weight-bold">Κύρια Επιλογή: Επικόλληση HTML Κώδικα (από το Gemini)</label>
                        <textarea
                            name="exerciseHtml"
                            class="form-control text-monospace"
                            rows="20"
                            style="font-size: 0.9rem;"
                            placeholder="Κάνε επικόλληση τον κώδικα HTML εδώ... (Μην ξεχάσεις να χρησιμοποιήσεις την κλάση .aepp-code στο <pre>)"></textarea>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <label class="text-primary font-weight-bold">Εναλλακτική Επιλογή: Ανέβασμα Εικόνας (αντί για HTML)</label>
                        <input type="file" name="exerciseImage" class="form-control" accept="image/*">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4 btn-block shadow-sm btn-lg">Αποθήκευση Άσκησης</button>
            </form>
        </div>
    <?php
    }

    public function listKenaExercises($result)
    {
    ?>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Διαχείριση Ασκήσεων (Κενά)</h3>
                <a href="index.php?action=addKena" class="btn btn-primary">Νέα Καταχώρηση</a>
            </div>
            <hr>
            <table class="table table-bordered table-hover bg-white shadow-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>Έτος</th>
                        <th>Είδος</th>
                        <th>Σχολείο</th>
                        <th>Πληροφορίες / Εικόνα</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="align-middle"><?php echo $row['exerciseYear']; ?></td>
                            <td class="align-middle"><?php echo $row['examType']; ?></td>
                            <td class="align-middle"><?php echo $row['schoolType']; ?></td>
                            <td class="align-middle" style="max-width: 200px;">
                                <?php if (!empty($row['imageName'])): ?>
                                    <a href="../images/themata/kenaNew/<?php echo $row['imageName']; ?>" target="_blank">
                                        <img src="../images/themata/kenaNew/<?php echo $row['imageName']; ?>" width="100" class="img-thumbnail">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">
                                        <i class="fa fa-info-circle"></i>
                                        <?php echo !empty($row['exerciseDescription']) ? $row['exerciseDescription'] : "Χωρίς περιγραφή"; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <a href="index.php?action=deleteKena&id=<?php echo $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Σίγουρα διαγραφή;');">
                                    <i class="fa fa-trash"></i> Διαγραφή
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function displayThemaGForm()
    {
    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-code"></i> Εισαγωγή Θέματος Γ</h3>
            <hr>
            <form action="index.php?action=saveThemaG" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Τύπος Θέματος</label>
                        <select name="thema_type" class="form-control">
                            <option value="G">Θέμα Γ</option>
                            <option value="D">Θέμα Δ</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Έτος</label>
                        <input type="number" name="etos" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Τύπος Σχολείου</label>
                        <select name="typosSxoleiou" class="form-control">
                            <option value="Hmerisio">Ημερήσιο</option>
                            <option value="Esperino">Εσπερινό</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Τύπος Εξετάσεων</label>
                        <select name="typosEksetaseon" class="form-control">
                            <option value="Kanonikes">Κανονικές</option>
                            <option value="Epanaliptikes">Επαναληπτικές</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Εκφώνηση (Paste κείμενο ή κώδικα)</label>
                    <textarea name="ekfonisi" class="form-control" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <label>Λύση (Προαιρετικά)</label>
                    <textarea name="lysi" class="form-control" rows="6" style="font-family: monospace;"></textarea>
                </div>
                <div class="form-group">
                    <label>Εικόνα Εκφώνησης (αν υπάρχει)</label>
                    <input type="file" name="eikona" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Αποθήκευση Θέματος</button>
            </form>
        </div>
    <?php
    }

    public function listThemataG($result)
    {
    ?>
        <div class="container mt-4">
            <h3>Λίστα Θεμάτων Γ</h3>
            <table class="table table-bordered table-striped mt-3">
                <thead class="thead-dark">
                    <tr>
                        <th>Έτος</th>
                        <th>Τύπος</th>
                        <th>Εκφώνηση</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['etos']; ?></td>
                            <td><?php echo $row['typosSxoleiou'] . " (" . $row['typosEksetaseon'] . ")"; ?></td>
                            <td><?php echo mb_substr(strip_tags($row['ekfonisi']), 0, 100) . "..."; ?></td>
                            <td>
                                <a href="index.php?action=deleteThemaG&id=<?php echo $row['id']; ?>"
                                    class="btn btn-danger btn-sm" onclick="return confirm('Διαγραφή;')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php
    }
    public function listMezedakia($result, $dbHandler) // Προσθήκη $dbHandler
    {
    ?>
        <div class="container mt-4">
            <h3 class="mb-4"><i class="fa fa-list text-primary"></i> Διαχείριση Μεζεδακίων</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped shadow-sm">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 12%">Ημερομηνία</th>
                            <th style="width: 35%">Εκφώνηση (Preview)</th>
                            <th style="width: 48%">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Επαναφορά του δείκτη στην αρχή του αποτελέσματος
                        if ($result && $result->num_rows > 0) {
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()):
                                $mezeId = $row['mezeId'];
                                $mTimestamp = strtotime($row['mezeDate']);
                                $solTimestamp = strtotime($row['solutionDate'] ?? '');
                                $currentTimestamp = time();
                                $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : '';

                                // Αν η ημερομηνία είναι μετά την τρέχουσα στιγμή
                                $isFuture = ($mTimestamp > $currentTimestamp);
                                $isLocked = (isset($row['isLocked']) && $row['isLocked'] == 1);

                                // Έλεγχος για λήξη προθεσμίας και απουσία παρατάσεων
                                $isExpired = ($solTimestamp > 0 && $solTimestamp < $currentTimestamp);
                                $hasExtensions = (!empty($userYear)) ? $dbHandler->hasAnyExtension($mezeId, $userYear) : false;
                                $isClosed = $isLocked || ($isExpired && !$hasExtensions);

                                // Στυλ για μελλοντικά: Απαλό γκρι φόντο
                                $rowStyle = '';
                                $badgeHtml = '';

                                if ($isLocked) {
                                    $badgeHtml .= '<br><span class="badge badge-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-lock"></i> Κλειδωμένο (Manual)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                } elseif ($isClosed) {
                                    $badgeHtml .= '<br><span class="badge badge-secondary mt-1" style="font-size: 0.75rem; opacity: 0.8;"><i class="fa fa-lock"></i> Λήξη (Χωρίς Ext)</span>';
                                    $rowStyle = 'style="background-color: #f2f2f2; color: #777;"';
                                }

                                // Ειδική σήμανση αν υπάρχει ενεργή παράταση σε κάποιον μαθητή
                                if ($hasExtensions && !$isLocked) {
                                    $badgeHtml .= '<br><span class="badge badge-warning mt-1" style="font-size: 0.75rem;"><i class="fa fa-clock-o"></i> Ενεργή Παράταση</span>';
                                    if (empty($rowStyle) && !$isFuture) {
                                        $rowStyle = 'style="background-color: #fffde7;"'; // Πολύ απαλό κίτρινο για να ξεχωρίζει από τα λευκά "ανοιχτά"
                                    }
                                }

                                // Έλεγχος για μεζεδάκια χωρίς λύση
                                $noSolution = (empty(trim($row['mezeSolution'] ?? '')) && empty($row['mezeSolutionImage']));
                                if ($noSolution) {
                                    $badgeHtml .= '<br><span class="badge badge-danger mt-1" style="font-size: 0.75rem;"><i class="fa fa-warning"></i> Χωρίς Λύση</span>';
                                }

                                // Έλεγχος για μη βαθμολογημένες υποβολές
                                $ungradedCount = 0;
                                $notSubmittedCount = 0;
                                if (!empty($userYear)) {
                                    $ungradedCount = $dbHandler->getUngradedSubmissionsCountForMeze($mezeId, $userYear);
                                    $notSubmittedCount = $dbHandler->getNotSubmittedCount($mezeId, $userYear);
                                }
                                $hasUngradedSubmissions = ($ungradedCount > 0);

                                if ($hasUngradedSubmissions) {
                                    $badgeHtml .= '<br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.75rem;"><i class="fa fa-exclamation-triangle"></i> ' . $ungradedCount . ' προς Βαθμολόγηση</span>';
                                    // Αν υπάρχουν μη βαθμολογημένες υποβολές και δεν είναι μελλοντικό, χρωμάτισε τη γραμμή
                                    if (!$isFuture) {
                                        $rowStyle = 'style="background-color: #fff3cd;"'; // Bootstrap warning background color
                                    }
                                }

                                if ($notSubmittedCount > 0 && !$isFuture) {
                                    $badgeHtml .= '<br><span class="badge bg-light text-muted border mt-1" style="font-size: 0.75rem;"><i class="fa fa-hourglass-o"></i> ' . $notSubmittedCount . ' δεν απάντησαν</span>';
                                }

                                // Έλεγχος για SOS
                                if (isset($row['isSos']) && $row['isSos'] == 1) {
                                    $badgeHtml .= '<br><span class="badge badge-danger mt-1" style="font-size: 0.75rem;"><i class="fa fa-fire"></i> SOS</span>';
                                }

                                // Εφάρμοσε το στυλ για μελλοντικά τελευταίο, για να υπερισχύει
                                if ($isFuture) {
                                    $rowStyle = 'style="background-color: #f8f9fa; color: #9c9c9c; font-style: italic;"';
                                }
                        ?>
                                <tr <?php echo $rowStyle; ?>>
                                    <td class="text-center font-weight-bold" style="white-space: nowrap;">
                                        <?php if ($isFuture): ?>
                                            <i class="fa fa-clock-o text-muted" title="Προγραμματισμένο"></i>
                                        <?php endif; ?>
                                        <?php echo $row['mezeNumber']; ?>
                                    </td>
                                    <td class="text-center small" style="white-space: nowrap;"><?php echo date('d/m/Y', $mTimestamp); ?></td>
                                    <td class="small">
                                        <?php echo mb_substr(strip_tags($row['mezeText']), 0, 60) . "..."; ?>
                                        <?php echo $badgeHtml; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap justify-content-center">
                                            <?php if ($isLocked): ?>
                                                <a href="index.php?action=toggleMezeLock&id=<?php echo $mezeId; ?>&status=0"
                                                    class="btn btn-outline-success btn-sm m-1" title="Άνοιγμα Υποβολών">
                                                    <i class="fa fa-unlock"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?action=toggleMezeLock&id=<?php echo $mezeId; ?>&status=1"
                                                    class="btn btn-outline-secondary btn-sm m-1" title="Χειροκίνητο Κλείδωμα"
                                                    onclick="return confirm('Θέλετε να κλειδώσετε χειροκίνητα τις υποβολές για αυτό το μεζεδάκι;')">
                                                    <i class="fa fa-lock"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="index.php?action=previewMeze&id=<?php echo $row['mezeId']; ?>"
                                                target="_blank"
                                                class="btn btn-dark btn-sm m-1"
                                                title="Προεπισκόπηση">
                                                <i class="fa fa-search"></i>
                                            </a>

                                            <a href="index.php?action=viewSubmissions&id=<?php echo $row['mezeId']; ?>"
                                                class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-info'; ?> btn-sm m-1" title="Λύσεις">
                                                <i class="fa fa-eye"></i> <span class="d-none d-lg-inline">Λύσεις</span>
                                            </a>

                                            <a href="index.php?action=manageGrades&id=<?php echo $row['mezeId']; ?>"
                                                class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-primary'; ?> btn-sm m-1" title="Βαθμοί">
                                                <i class="fa fa-graduation-cap"></i> <span class="d-none d-lg-inline">Βαθμοί</span>
                                            </a>

                                            <!-- Γρήγορη Παράταση σε όλους -->
                                            <?php
                                            $isGlobal = $dbHandler->hasGlobalExtension($mezeId, $userYear);
                                            ?>
                                            <form action="index.php?action=extendMezeForAll" method="post" class="form-inline m-1">
                                                <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                                <div class="input-group input-group-sm">
                                                    <?php if (!$isGlobal): ?>
                                                        <input type="number" name="hours" class="form-control" style="width: 45px;" value="24" title="Ώρες παράτασης για όλους">
                                                    <?php endif; ?>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn <?php echo $isGlobal ? 'btn-warning' : 'btn-outline-warning'; ?> btn-sm"
                                                            title="<?php echo $isGlobal ? 'Αφαίρεση Καθολικής Παράτασης' : 'Παράταση σε ΟΛΟΥΣ'; ?>"
                                                            onclick="return confirm('<?php echo $isGlobal ? 'Θέλετε να αναιρέσετε την καθολική παράταση;' : 'Θέλετε να δώσετε παράταση σε ΟΛΟΥΣ τους μαθητές;'; ?>')">
                                                            <i class="fa fa-users"></i> <?php echo $isGlobal ? '-' : '+'; ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <a href="index.php?action=editMezedaki&id=<?php echo $row['mezeId']; ?>"
                                                class="btn btn-warning btn-sm m-1" title="Διόρθωση">
                                                <i class="fa fa-edit"></i> <span class="d-none d-lg-inline">Διόρθωση</span>
                                            </a>

                                            <a href="index.php?action=deleteMezedaki&id=<?php echo $row['mezeId']; ?>"
                                                class="btn btn-danger btn-sm m-1"
                                                onclick="return confirm('Σίγουρα διαγραφή;')" title="Διαγραφή">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } else { ?>
                            <tr>
                                <td colspan="4" class="text-center p-4 text-muted">Δεν βρέθηκαν μεζεδάκια στη βάση.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }
    public function addMezedakiForm($exerciseTypes = [])
    {
        if (empty($exerciseTypes)) {
            $db = new AdminDbHandler();
            $exerciseTypes = $db->getExerciseTypes();
        }
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
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-left bg-white" type="button" id="typeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                    <label>Κείμενο / Κώδικας (HTML/Bootstrap)</label>
                    <textarea name="mezeText" class="form-control" rows="6" placeholder="Γράψε την εκφώνηση εδώ..."></textarea>
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
            <h3><i class="fa fa-edit"></i> Επεξεργασία Μεζεδακίου #<?php echo $row['mezeNumber']; ?></h3>
            <hr>
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
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-left bg-white" type="button" id="editTypeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                    <label>Εκφώνηση (Κείμενο ή HTML)</label>
                    <textarea name="mezeText" class="form-control" rows="5"><?php echo $row['mezeText']; ?></textarea>
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
        </script>
    <?php
    }
    public function showGradesForm($students, $mezeId, $displayNumber, $existingGrades = [])
    {
    ?>
        <div class="container mt-4 border p-4 bg-white shadow-sm">
            <h3 class="text-primary"><i class="fa fa-pencil"></i> Βαθμολόγιο για το Μεζεδάκι #<?php echo $displayNumber; ?></h3>
            <hr>
            <form action="index.php?action=saveGrades" method="post">
                <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Ονοματεπώνυμο Μαθητή</th>
                            <th style="width: 250px;">Βαθμός (0-20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student):
                            $stId = $student['studentId'];
                            $currentGrade = (isset($existingGrades[$stId]) && $existingGrades[$stId] !== null) ? $existingGrades[$stId] : "";
                        ?>
                            <tr>
                                <td><?php echo $student['name'] . " " . $student['lastName']; ?></td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="grades[<?php echo $stId; ?>]"
                                            class="form-control" step="0.1" min="0" max="20"
                                            value="<?php echo $currentGrade; ?>" placeholder="-">

                                        <?php if ($currentGrade !== ""): ?>
                                            <div class="input-group-append">
                                                <a href="index.php?action=deleteSpecificGrade&studentId=<?php echo $stId; ?>&mezeId=<?php echo $mezeId; ?>"
                                                    class="btn btn-outline-danger"
                                                    onclick="return confirm('Διαγραφή βαθμού για τον μαθητή <?php echo $student['name']; ?>;')"
                                                    title="Διαγραφή βαθμού">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success btn-lg shadow-sm">
                        <i class="fa fa-save"></i> Ενημέρωση Βαθμών
                    </button>
                    <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-lg ml-2">Επιστροφή</a>
                </div>
            </form>
        </div>
    <?php
    }

    public function showFullGradesTable($students, $gradesReport)
    {
    ?>
        <div class="container-fluid mt-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fa fa-table"></i> Συγκεντρωτικό Βαθμολόγιο</h3>
                    <button onclick="window.print();" class="btn btn-light btn-sm font-weight-bold d-print-none">
                        <i class="fa fa-print"></i> Εκτύπωση / PDF
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-left">Ονοματεπώνυμο Μαθητή</th>
                                    <?php
                                    $allMezeNumbers = [];
                                    foreach ($gradesReport as $stGrades) {
                                        foreach ($stGrades as $num => $val) {
                                            $allMezeNumbers[$num] = true;
                                        }
                                    }
                                    ksort($allMezeNumbers);
                                    foreach (array_keys($allMezeNumbers) as $mNum): ?>
                                        <th>Μ<?php echo $mNum; ?></th>
                                    <?php endforeach; ?>
                                    <th class="bg-warning">Μ.Ο.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student):
                                    $stId = $student['studentId'];
                                    $fullName = $student['name'] . " " . $student['lastName'];
                                    $sum = 0;
                                    $count = 0;
                                ?>
                                    <tr>
                                        <td class="text-left font-weight-bold">
                                            <?php echo $fullName; ?>
                                            <a href="index.php?action=viewStudentProfile&studentId=<?php echo $stId; ?>"
                                                class="ml-2 d-print-none text-info" title="Ατομική Καρτέλα">
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                        </td>
                                        <?php foreach (array_keys($allMezeNumbers) as $mNum):
                                            $grade = isset($gradesReport[$stId][$mNum]) ? $gradesReport[$stId][$mNum] : "-";
                                            if (is_numeric($grade)) {
                                                $sum += $grade;
                                                $count++;
                                            }
                                        ?>
                                            <td><?php echo $grade; ?></td>
                                        <?php endforeach; ?>
                                        <td class="font-weight-bold text-danger">
                                            <?php echo ($count > 0) ? number_format($sum / $count, 2) : "-"; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    // ΠΡΟΣΘΕΣΕ ΚΑΙ ΑΥΤΗ ΤΗ ΣΥΝΑΡΤΗΣΗ ΣΤΟ ΤΕΛΟΣ ΤΟΥ ΑΡΧΕΙΟΥ (πριν το τελευταίο })
    public function showStudentReport($studentName, $grades)
    {
    ?>
        <div class="container mt-5 p-4 bg-white shadow border">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-dark">Ατομική Καρτέλα Μαθητή</h2>
                    <h4 class="text-secondary"><?php echo $studentName; ?></h4>
                </div>
                <button onclick="window.print();" class="btn btn-outline-dark d-print-none">
                    <i class="fa fa-print"></i> Εκτύπωση Καρτέλας
                </button>
            </div>
            <hr>
            <table class="table table-bordered table-striped mt-3 text-center">
                <thead class="thead-light">
                    <tr>
                        <th># Μεζεδάκι</th>
                        <th>Ημερομηνία</th>
                        <th>Βαθμός (0-20)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum = 0;
                    $count = 0;
                    foreach ($grades as $row):
                        $sum += $row['grade_value'];
                        $count++;
                    ?>
                        <tr>
                            <td>Μεζεδάκι #<?php echo $row['mezeNumber']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['mezeDate'])); ?></td>
                            <td class="font-weight-bold"><?php echo number_format($row['grade_value'], 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="2" class="text-right">Γενικός Μέσος Όρος:</th>
                        <th class="text-danger h5">
                            <?php echo ($count > 0) ? number_format($sum / $count, 2) : "-"; ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php
    }

    public function showStudentSelectionList($students)
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fa fa-users"></i> Επιλογή Μαθητή για Πλήρη Εικόνα (360°)</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Επιλέξτε έναν μαθητή για να δείτε συγκεντρωμένα: Βαθμούς (Μεζεδάκια), Εργασίες Ομάδας και Οικονομική Κατάσταση (FIFO).</p>
                    <div class="list-group shadow-sm">
                        <?php if (empty($students)): ?>
                            <div class="alert alert-warning text-center">Δεν βρέθηκαν μαθητές. Βεβαιωθείτε ότι έχετε ορίσει το σωστό <b>Username</b> στο μενού.</div>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <a href="index.php?action=viewStudentProfile&studentId=<?php echo $student['studentId']; ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-user-circle text-success mr-2" style="font-size: 1.2rem;"></i>
                                        <span class="font-weight-bold"><?php echo $student['name'] . " " . $student['lastName']; ?></span>
                                    </div>
                                    <div>
                                        <span class="badge badge-light border px-3 py-2">
                                            Άνοιγμα Καρτέλας <i class="fa fa-chevron-right ml-1"></i>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary shadow-sm"><i class="fa fa-home"></i> Επιστροφή στο Dashboard</a>
            </div>
        </div>
    <?php
    }

    public function showFullStudentProfile($student, $grades, $tasks, $financials, $average)
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fa fa-user-circle"></i> Καρτέλα Μαθητή: <?php echo $student['name'] . " " . $student['lastName']; ?></h3>
                    <div class="d-print-none">
                        <button onclick="window.print();" class="btn btn-light btn-sm"><i class="fa fa-print"></i> Εκτύπωση</button>
                        <a href="index.php?action=viewStudentProfile" class="btn btn-dark btn-sm">Επιστροφή</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center">
                                <h6 class="text-muted mb-1">Μέσος Όρος (Μεζεδάκια)</h6>
                                <h2 class="text-danger font-weight-bold mb-0"><?php echo number_format($average, 2); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center">
                                <h6 class="text-muted mb-1">Εκκρεμή Μαθήματα</h6>
                                <h2 class="<?php echo $financials['balance'] > 0 ? 'text-warning' : 'text-success'; ?> font-weight-bold mb-0"><?php echo count($financials['items']); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light border rounded text-center">
                                <h6 class="text-muted mb-1">Στοιχεία Επικοινωνίας</h6>
                                <div class="small">
                                    <i class="fa fa-envelope"></i> <?php echo $student['email']; ?><br>
                                    <i class="fa fa-phone"></i> <?php echo $student['phone']; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs d-print-none" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="meze-tab" data-toggle="tab" href="#meze" role="tab">Μεζεδάκια</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab">Εργασίες Ομάδας</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="finance-tab" data-toggle="tab" href="#finance" role="tab">Μαθήματα / Πληρωμές</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="profileTabsContent">
                        <!-- Μεζεδάκια -->
                        <div class="tab-pane fade show active" id="meze" role="tabpanel">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Ημερομηνία</th>
                                        <th>Βαθμός</th>
                                        <th>Σχόλια</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $g): ?>
                                        <tr>
                                            <td>#<?php echo $g['mezeNumber']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($g['mezeDate'])); ?></td>
                                            <td class="font-weight-bold"><?php echo $g['grade_value']; ?></td>
                                            <td class="small italic text-muted"><?php echo $g['teacher_comments']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Εργασίες -->
                        <div class="tab-pane fade" id="tasks" role="tabpanel">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Εργασία</th>
                                        <th>Βαθμός</th>
                                        <th>Σχόλια</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $t): ?>
                                        <tr>
                                            <td class="small"><?php echo date('d/m/Y', strtotime($t['date_added'])); ?></td>
                                            <td><?php echo htmlspecialchars($t['task_text']); ?></td>
                                            <td><?php echo $t['grade_value'] ?? '-'; ?></td>
                                            <td class="small"><?php echo $t['teacher_comments']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Οικονομικά -->
                        <div class="tab-pane fade" id="finance" role="tabpanel">
                            <div class="alert alert-info py-2">
                                <i class="fa fa-info-circle"></i> Εμφανίζονται τα μαθήματα που δεν έχουν ακόμα εξοφληθεί (FIFO).
                            </div>
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Τύπος</th>
                                        <th class="text-right">Ποσό Εκκρεμότητας</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($financials['items'])): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-success">Όλα εξοφλημένα!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($financials['items'] as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($item['date'])); ?></td>
                                                <td><?php echo ($item['entryType'] == 'absence' ? 'Απουσία' : 'Μάθημα'); ?></td>
                                                <td class="text-right font-weight-bold text-danger">
                                                    <?php echo isset($item['cost']) ? number_format($item['cost'], 2) . " €" : "-"; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="bg-dark text-white">
                                    <tr>
                                        <th colspan="2" class="text-right">Τρέχον Υπόλοιπο:</th>
                                        <th class="text-right"><?php echo number_format($financials['balance'], 2); ?> €</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function showSubmissionsForGrading($submissions, $students, $mezeData, $allMezedakia, $existingGrades = [])
    {
        $db = new DbHandler(); // Για να ελέγχουμε αν επιτρέπεται η υποβολή
        $mezeNumber = (int)$mezeData['mezeNumber'];
        $isSos = (isset($mezeData['isSos']) && $mezeData['isSos'] == 1);

        // Παίρνουμε το ID από τα δεδομένα που μας ήρθαν (mezeData)
        $mezeId = $mezeData['mezeId'];

        $userYear = isset($_SESSION['tutor_user']) ? $_SESSION['tutor_user'] : "";

        $pendingSubmissions = [];
        $gradedSubmissions = [];

        // 1. Προετοιμασία ευρετηρίων
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

        // 2. Διαχωρισμός μαθητών
        foreach ($students as $student) {
            $stId = (int)$student['studentId'];
            $subData = isset($submissionsByStudent[$stId]) ? $submissionsByStudent[$stId] : null;
            $gradeData = isset($gradesByStudent[$stId]) ? $gradesByStudent[$stId] : null;

            $isGraded = false;
            if ($gradeData) {
                $isGraded = true;
                // Αν υπάρχει υποβολή και είναι πιο πρόσφατη από την τελευταία βαθμολόγηση, 
                // τότε ο μαθητής επιστρέφει στη λίστα προς βαθμολόγηση.
                if ($subData && strtotime($subData['submission_date']) > strtotime($gradeData['updated_at'] ?? '2000-01-01')) {
                    $isGraded = false;
                }
            }

            if ($isGraded) {
                $gradedSubmissions[] = ['sub' => $subData, 'grade' => $gradeData, 'student' => $student];
            } else {
                $pendingSubmissions[] = ['sub' => $subData, 'student' => $student];
            }
        }
    ?>
        <div class="container mt-4">
            <h3 class="text-primary mb-4">
                <i class="fa fa-mortar-board"></i> Απαντήσεις για το Μεζεδάκι #<?php echo $mezeNumber; ?>
                <?php if ($isSos): ?>
                    <span class="badge badge-danger ml-2 shadow-sm"><i class="fa fa-fire"></i> SOS</span>
                <?php endif; ?>
            </h3>

            <div class="mb-5">
                <h5 class="text-danger font-weight-bold"><i class="fa fa-clock-o"></i> Προς Βαθμολόγηση (<?php echo count($pendingSubmissions); ?>)</h5>
                <hr class="border-danger">
                <?php foreach ($pendingSubmissions as $item):
                    $sub = $item['sub'];
                    $st = $item['student'];
                    $stId = $st['studentId'];
                    $studentName = $st['name'] . " " . $st['lastName'];

                    // ΕΛΕΓΧΟΣ ΛΟΥΚΕΤΟΥ: Είναι ήδη ανοιχτή η φόρμα γι' αυτόν;
                    $isAllowed = $db->isSubmissionAllowed($stId, $mezeId, $userYear);
                    $lockIcon = $isAllowed ? "fa-unlock text-success" : "fa-lock text-muted";
                    $lockTitle = $isAllowed ? "Κλείσιμο φόρμας / Αφαίρεση Παράτασης" : "Άνοιγμα φόρμας (Ορίστε ώρες)";
                ?>
                    <div class="card mb-4 shadow-sm border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                            <strong><?php echo $studentName; ?></strong>
                            <small><?php echo ($sub) ? date('d/m/Y H:i', strtotime($sub['submission_date'])) : '<span class="badge badge-warning text-dark">Δεν δόθηκε απάντηση</span>'; ?></small>
                        </div>
                        <div class="card-body">
                            <?php if ($sub): ?>
                                <p class="small bg-light p-2 border rounded"><?php echo nl2br($sub['student_text'] ?: "<i>Χωρίς σχόλια.</i>"); ?></p>
                                <div class="row mb-3">
                                    <?php
                                    $fileFields = ['file1', 'file2', 'file3'];
                                    foreach ($fileFields as $f):
                                        if (!empty($sub[$f])): ?>
                                            <div class="col-md-4 mb-2">
                                                <a href="../uploads/submissions/<?php echo $sub[$f]; ?>" target="_blank" class="btn btn-sm btn-block btn-outline-primary text-truncate">
                                                    <i class="fa fa-file-image-o"></i> <?php echo $sub[$f]; ?>
                                                </a>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-1 small d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-exclamation-triangle"></i> Ο μαθητής δεν έχει υποβάλει λύση.</span>

                                    <form action="index.php?action=giveExtension" method="post" class="form-inline m-0">
                                        <?php if ($isAllowed):
                                            $extInfo = $db->getExtensionInfo($stId, $mezeId, $userYear);
                                            if ($extInfo && $extInfo['expires_at']): ?>
                                                <span class="badge badge-success mr-2" style="font-size: 0.7rem;"><i class="fa fa-clock-o"></i> Λήγει: <?php echo date('d/m H:i', strtotime($extInfo['expires_at'])); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                        <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                        <div class="input-group input-group-sm">
                                            <?php if (!$isAllowed): ?>
                                                <input type="number" name="hours" class="form-control" style="width: 45px; font-size: 0.7rem;" value="24" title="Ώρες παράτασης">
                                            <?php endif; ?>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-xs <?php echo $isAllowed ? 'btn-outline-success' : 'btn-danger'; ?> py-0 px-2"
                                                    style="font-size:0.7rem;" title="<?php echo $lockTitle; ?>">
                                                    <i class="fa <?php echo $lockIcon; ?>"></i>
                                                    <?php echo $isAllowed ? "Ανοιχτή" : "Άνοιγμα"; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <form action="index.php?action=quickGrade" method="post" class="bg-light p-2 rounded border">
                                <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                <input type="hidden" name="meze_id" value="<?php echo ($sub) ? $sub['meze_id'] : $mezeId; ?>">
                                <div class="d-flex align-items-start">
                                    <input type="number" name="grade" step="0.5" class="form-control form-control-sm mr-2" style="width:80px" placeholder="Βαθμός" required>
                                    <textarea name="teacher_comments" class="form-control form-control-sm mr-2" rows="2" style="flex:1; min-height: 60px;" placeholder="Σχόλια παρότρυνσης..."></textarea>
                                    <button type="submit" class="btn btn-sm btn-success px-4 text-white font-weight-bold shadow-sm">OK</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-5">
                <h5 class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> Ολοκληρωμένες (<?php echo count($gradedSubmissions); ?>)</h5>
                <hr class="border-success">
                <div class="list-group shadow-sm">
                    <?php foreach ($gradedSubmissions as $item):
                        $sub = $item['sub'];
                        $grade = $item['grade'];
                        $st = $item['student'];
                        $stId = $st['studentId'];

                        // Έλεγχος αν επιτρέπεται η υποβολή (παράταση) ακόμα και αν έχει βαθμολογηθεί
                        $isAllowed = $db->isSubmissionAllowed($stId, $mezeId, $userYear);
                        $lockIcon = $isAllowed ? "fa-unlock text-success" : "fa-lock text-muted";
                        $lockTitle = $isAllowed ? "Κλείσιμο φόρμας / Αφαίρεση Παράτασης" : "Άνοιγμα φόρμας (Ορίστε ώρες)";
                    ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" style="cursor: pointer; border-left: 5px solid #28a745;" onclick="$(this).next('.edit-row').toggle();">
                            <div>
                                <span class="font-weight-bold mr-3"><?php echo $st['name'] . " " . $st['lastName']; ?></span>
                                <span class="badge badge-success px-2 py-1">Βαθμός: <?php echo $grade['grade_value']; ?></span>
                                <?php if ($isAllowed): ?>
                                    <span class="badge badge-info ml-2" title="Η φόρμα υποβολής είναι ανοιχτή για αυτόν τον μαθητή"><i class="fa fa-unlock"></i> Ανοιχτή</span>
                                <?php endif; ?>
                            </div>
                            <i class="fa fa-chevron-down text-muted"></i>
                        </div>
                        <div class="edit-row p-3 border border-top-0 mb-2 bg-light shadow-sm" style="display:none;">
                            <!-- Δυνατότητα παράτασης και για ήδη βαθμολογημένους μαθητές -->
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-white border rounded shadow-sm">
                                <span class="small font-weight-bold"><i class="fa fa-info-circle text-info"></i> Κατάσταση Φόρμας:</span>
                                <form action="index.php?action=giveExtension" method="post" class="form-inline m-0">
                                    <?php if ($isAllowed):
                                        $extInfo = $db->getExtensionInfo($stId, $mezeId, $userYear);
                                        if ($extInfo && $extInfo['expires_at']): ?>
                                            <span class="badge badge-success mr-2" style="font-size: 0.75rem;"><i class="fa fa-clock-o"></i> Λήγει: <?php echo date('d/m H:i', strtotime($extInfo['expires_at'])); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                    <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                                    <div class="input-group input-group-sm">
                                        <?php if (!$isAllowed): ?>
                                            <input type="number" name="hours" class="form-control" style="width: 45px; font-size: 0.75rem;" value="24" title="Ώρες παράτασης">
                                        <?php endif; ?>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-xs <?php echo $isAllowed ? 'btn-outline-success' : 'btn-danger'; ?> py-1 px-3"
                                                style="font-size:0.75rem;" title="<?php echo $lockTitle; ?>">
                                                <i class="fa <?php echo $lockIcon; ?>"></i>
                                                <?php echo $isAllowed ? "Ανοιχτή" : "Άνοιγμα"; ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <?php if ($sub): ?>
                                <div class="submission-preview mb-3">
                                    <h6 class="font-weight-bold text-muted small text-uppercase">Απάντηση Μαθητή:</h6>
                                    <p class="small bg-white p-2 border rounded"><?php echo nl2br($sub['student_text'] ?: "<i>Χωρίς σχόλια.</i>"); ?></p>
                                    <div class="row">
                                        <?php
                                        $fileFields = ['file1', 'file2', 'file3'];
                                        foreach ($fileFields as $f):
                                            if (!empty($sub[$f])): ?>
                                                <div class="col-md-4 mb-2">
                                                    <a href="../uploads/submissions/<?php echo $sub[$f]; ?>" target="_blank" class="btn btn-sm btn-block btn-white border text-truncate">
                                                        <i class="fa fa-file-image-o"></i> <?php echo $sub[$f]; ?>
                                                    </a>
                                                </div>
                                        <?php endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                                <hr>
                            <?php endif; ?>

                            <form action="index.php?action=quickGrade" method="post">
                                <input type="hidden" name="student_id" value="<?php echo $st['studentId']; ?>">
                                <input type="hidden" name="meze_id" value="<?php echo ($sub) ? $sub['meze_id'] : $mezeId; ?>">
                                <div class="d-flex align-items-start">
                                    <input type="number" name="grade" step="0.5" class="form-control form-control-sm mr-2" style="width:70px" value="<?php echo $grade['grade_value']; ?>">
                                    <textarea name="teacher_comments" class="form-control form-control-sm mr-2" rows="1" style="flex:1; min-height: 31px;"><?php echo $grade['teacher_comments']; ?></textarea>
                                    <button type="submit" class="btn btn-sm btn-info px-3 shadow-sm">Ενημέρωση</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php
    }
    public function showPrintableReport($studentName, $mezeNumber, $grade, $comments, $average, $mezeId, $studentData = null)
    {
    ?>
        <div id="printableReport" class="container mt-5 p-5 shadow border report-container">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold">Αναφορά Προόδου Μαθητή</h2>
                <h4>Μάθημα: ΑΕΠΠ - Μεζεδάκια</h4>
                <hr class="hr-light-grey">
            </div>

            <div class="mb-4">
                <p class="report-info-text"><strong>Μαθητής:</strong> <?php echo $studentName; ?></p>
                <p class="report-info-text"><strong>Ημερομηνία:</strong> <?php echo date('d/m/Y'); ?></p>
            </div>

            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Δραστηριότητα</th>
                        <th class="text-center">Βαθμός</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Μεζεδάκι #<?php echo $mezeNumber; ?></td>
                        <td class="text-center"><strong><?php echo $grade; ?> / 20</strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <h5 class="font-weight-bold">Σχόλια Δασκάλου:</h5>
                <div class="p-3 border rounded bg-light report-comments-box">
                    <?php echo nl2br($comments); ?>
                </div>
            </div>

            <?php
            // 1. Μήνυμα για WhatsApp (Text with Markdown)
            $msg_wa = " ΕΝΗΜΕΡΩΣΗ ΠΡΟΟΔΟΥ: ΑΕΠΠ \n";
            $msg_wa .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $msg_wa .= "Γεια σου " . $studentName . "! \n\n";
            $msg_wa .= "Μόλις βαθμολογήθηκε το Μεζεδάκι #" . $mezeNumber . ". \n\n";
            $msg_wa .= "Βαθμός: " . $grade . "/20 \n";
            $msg_wa .= "Συνολικός Μ.Ο.: " . number_format($average, 2) . "/20 \n\n";
            if (!empty($comments)) {
                $msg_wa .= "💬 *Σχόλια:* \n";
                $msg_wa .= "_" . html_entity_decode(strip_tags($comments), ENT_QUOTES, 'UTF-8') . "_ \n\n";
            }
            $msg_wa .= "🔗 Δες την αναλυτική σου καρτέλα: \n";
            $msg_wa .= "https://jhouv.eu/aepp/index.php?action=myGrades";

            // 2. Μήνυμα για HTML Email (Πιο απλή μορφή που δούλευε)
            $msg_html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee;'>
                <h2 style='color: #dc3545;'>Ενημέρωση Προόδου: ΑΕΠΠ</h2>
                <p>Γεια σου <b>" . $studentName . "</b>,</p>
                <p>Μόλις βαθμολογήθηκε η εργασία σου:</p>
                <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                    <tr style='background: #f8f9fa;'>
                        <td><b>Δραστηριότητα</b></td>
                        <td><b>Βαθμός</b></td>
                    </tr>
                    <tr>
                        <td>Μεζεδάκι #" . $mezeNumber . "</td>
                        <td><b style='color: #28a745; font-size: 1.2em;'>" . $grade . " / 20</b></td>
                    </tr>
                    <tr>
                        <td>Συνολικός Μέσος Όρος</td>
                        <td><b>" . number_format($average, 2) . " / 20</b></td>
                    </tr>
                </table>";

            if (!empty($comments)) {
                $msg_html .= "
                    <p><b>Σχόλια Καθηγητή:</b><br>
                    <i>" . nl2br($comments) . "</i></p>";
            }

            $msg_html .= "
                <p><a href='https://jhouv.eu/aepp/index.php?action=myGrades'>Προβολή Αναλυτικής Καρτέλας</a></p>
                <p>Με εκτίμηση,<br><b>Αντώνης Χουβαρδάς</b></p>
            </div>";

            // Για το mailto: (ως fallback αν δεν χρησιμοποιηθεί η sendReportEmail)
            $msg_mail = "📚 ΕΝΗΜΕΡΩΣΗ ΠΡΟΟΔΟΥ: ΑΕΠΠ\n";
            $msg_mail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $msg_mail .= "Αγαπητέ/ή " . $studentName . ",\n\n";
            $msg_mail .= "Σου στέλνω τη βαθμολογία σου για τη δραστηριότητα: Μεζεδάκι #" . $mezeNumber . "\n\n";
            $msg_mail .= "🎯 Βαθμός: " . $grade . " / 20\n";
            $msg_mail .= "📈 Συνολικός Μέσος Όρος: " . number_format($average, 2) . " / 20\n\n";
            if (!empty($comments)) {
                $msg_mail .= "💬 Σχόλια Καθηγητή:\n";
                $msg_mail .= "\"" . html_entity_decode(strip_tags($comments), ENT_QUOTES, 'UTF-8') . "\"\n\n";
            }
            $msg_mail .= "🔗 Δες την αναλυτική καρτέλα σου στην πλατφόρμα:\n";
            $msg_mail .= "https://jhouv.eu/aepp/index.php?action=myGrades\n\n";
            $msg_mail .= "Με εκτίμηση,\nΑντώνης Χουβαρδάς";

            $wa_link = "";
            if (!empty($studentData['phone'])) {
                // Καθαρισμός τηλεφώνου (μόνο νούμερα)
                $phone = preg_replace('/[^0-9]/', '', $studentData['phone']);
                if (strlen($phone) == 10) $phone = "30" . $phone;
                $wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($msg_wa);
            }

            $mail_link = "";
            if (!empty($studentData['email'])) {
                $subject = "Vathmologia: Mezedaki #" . $mezeNumber . " (" . $studentName . ")";
                $mail_link = "mailto:" . $studentData['email'] . "?subject=" . urlencode($subject) . "&body=" . urlencode($msg_mail);
            }
            ?>

            <div class="mt-4 p-3 bg-dark text-white d-flex justify-content-between align-items-center rounded shadow">
                <h5 class="mb-0">Συνολικός Μέσος Όρος:</h5>
                <h4 class="mb-0 font-weight-bold"><?php echo number_format($average, 2); ?> / 20</h4>
            </div>

            <div class="mt-5 text-center d-print-none">
                <button onclick="window.print();" class="btn btn-secondary btn-lg shadow mr-2">
                    <i class="fa fa-print"></i> Εκτύπωση / PDF
                </button>

                <?php if ($wa_link): ?>
                    <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-success btn-lg shadow mr-2">
                        <i class="fa fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>

                <?php if ($mail_link): ?>
                    <form action="index.php?action=sendReportEmail" method="post" style="display:inline;">
                        <input type="hidden" name="email" value="<?php echo $studentData['email']; ?>">
                        <input type="hidden" name="subject" value="<?php echo $subject; ?>">
                        <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">
                        <input type="hidden" name="html_message" value="<?php echo htmlspecialchars($msg_html); ?>">
                        <button type="submit" class="btn btn-info btn-lg shadow mr-2">
                            <i class="fa fa-envelope"></i> Αποστολή HTML Email
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    public function manageGroupsForm($groups, $students, $db, $assignments = [])
    {
    ?>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">Δημιουργία Ομάδας</div>
                        <div class="card-body">
                            <form action="index.php?action=save_group" method="POST">
                                <input type="text" name="group_name" class="form-control mb-2" placeholder="Όνομα Ομάδας" required>
                                <button type="submit" class="btn btn-primary btn-block">Δημιουργία</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">Ανάθεση Μαθητή σε Ομάδα</div>
                        <div class="card-body">
                            <form action="index.php?action=add_student_to_group" method="POST">
                                <select name="student_id" class="form-control mb-2" required>
                                    <option value="">Επίλεξε Μαθητή</option>
                                    <?php foreach ($students as $s):
                                        // Αν ο μαθητής είναι ήδη σε ομάδα, τον προσπερνάμε
                                        if (array_key_exists($s['studentId'], $assignments)) continue;
                                    ?>
                                        <option value="<?php echo $s['studentId']; ?>"><?php echo "{$s['name']} {$s['lastName']}"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="group_id" class="form-control mb-2" required>
                                    <option value="">Επίλεξε Ομάδα</option>
                                    <?php foreach ($groups as $g) echo "<option value='{$g['id']}'>{$g['group_name']}</option>"; ?>
                                </select>
                                <button type="submit" class="btn btn-info btn-block">Ανάθεση</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h5>Υπάρχουσες Ομάδες & Μέλη</h5>
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 30%;">Ομάδα</th>
                            <th>Μέλη</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g):
                            // Βρίσκουμε ποιοι μαθητές ανήκουν σε αυτή την ομάδα
                            $members = [];
                            foreach ($students as $s) {
                                if (isset($assignments[$s['studentId']]) && $assignments[$s['studentId']] == $g['id']) {
                                    $members[] = $s;
                                }
                            }
                        ?>
                            <tr>
                                <td><strong><?php echo $g['group_name']; ?></strong></td>
                                <td>
                                    <?php if (empty($members)): ?>
                                        <span class="text-muted small">Κενή ομάδα</span>
                                    <?php else: ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($members as $m): ?>
                                                <li>
                                                    <?php echo "{$m['name']} {$m['lastName']}"; ?>
                                                    <a href="index.php?action=remove_student_from_group&student_id=<?php echo $m['studentId']; ?>" class="text-danger ml-1" onclick="return confirm('Αφαίρεση μαθητή από την ομάδα;')"><i class="fa fa-times-circle"></i></a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }

    public function listAllTasks($tasks)
    {
    ?>
        <div class="container mt-4">
            <h3 class="mb-4"><i class="fa fa-history text-primary"></i> Ιστορικό Αναθέσεων Ομάδων</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover shadow-sm bg-white">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 15%">Ημερομηνία</th>
                            <th style="width: 20%">Ομάδα</th>
                            <th style="width: 45%">Περιγραφή Εργασίας</th>
                            <th style="width: 20%">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="4" class="text-center p-4 text-muted">Δεν έχουν βρεθεί αναθέσεις εργασιών.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td class="align-middle small"><?php echo date('d/m/Y H:i', strtotime($task['date_added'])); ?></td>
                                    <td class="align-middle font-weight-bold"><?php echo htmlspecialchars($task['group_name']); ?></td>
                                    <td class="align-middle">
                                        <?php if ($task['book_title']): ?>
                                            <span class="badge badge-secondary mb-1"><?php echo htmlspecialchars($task['book_title']); ?></span><br>
                                        <?php endif; ?>
                                        <div class="small"><?php echo nl2br(htmlspecialchars($task['task_text'])); ?></div>
                                        <?php if (!empty($task['task_file'])): ?>
                                            <div class="mt-1 small"><i class="fa fa-paperclip"></i> <?php echo $task['task_file']; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="index.php?action=grade_task&task_id=<?php echo $task['id']; ?>" class="btn btn-outline-primary btn-sm btn-block shadow-sm mb-1">
                                            <i class="fa fa-pencil"></i> Βαθμολόγηση
                                        </a>
                                        <?php
                                        $graded = $task['graded_count'] ?? 0;
                                        $total = $task['total_students'] ?? 0;
                                        $badgeClass = ($graded > 0) ? (($graded >= $total) ? 'badge-success' : 'badge-warning') : 'badge-secondary';
                                        ?>
                                        <div style="font-size: 0.75rem;">
                                            <span class="badge <?php echo $badgeClass; ?> w-100 py-1">
                                                <?php echo "$graded / $total"; ?> βαθμολογημένοι
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="index.php?action=assign_tasks" class="btn btn-secondary shadow-sm"><i class="fa fa-arrow-left"></i> Επιστροφή</a>
            </div>
        </div>
    <?php
    }

    public function showTaskGradesForm($task, $students, $existingGrades)
    {
    ?>
        <div class="container mt-4 border-0 p-0">
            <form action="index.php?action=save_task_grades" method="post" class="bg-white shadow-sm rounded p-4">
                <div class="sticky-top bg-white border-bottom mb-3 py-3 d-flex justify-content-between align-items-center" style="top: -1px; z-index: 1000;">
                    <div>
                        <h3 class="mb-0 text-primary"><i class="fa fa-pencil"></i> Βαθμολόγηση Εργασίας</h3>
                        <small class="text-muted">Ομάδα: <strong><?php echo htmlspecialchars($task['group_name'] ?? ''); ?></strong></small>
                    </div>
                    <div>
                        <a href="index.php?action=list_all_tasks" class="btn btn-outline-secondary">Επιστροφή</a>
                        <button type="submit" class="btn btn-success shadow-sm ml-2"><i class="fa fa-save"></i> Αποθήκευση Όλων</button>
                    </div>
                </div>

                <div class="alert alert-info shadow-sm mb-4">
                    <strong>Περιγραφή Εργασίας:</strong><br>
                    <?php echo nl2br(htmlspecialchars($task['task_text'])); ?>
                </div>

                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 20%;">Ονοματεπώνυμο Μαθητή</th>
                            <th style="width: 85px;">Βαθμός</th>
                            <th>Σχόλια / Παρατηρήσεις (HTML)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s):
                            $stId = $s['studentId'];
                            $grade = isset($existingGrades[$stId]) ? $existingGrades[$stId]['grade_value'] : "";
                            $comments = isset($existingGrades[$stId]) ? $existingGrades[$stId]['teacher_comments'] : "";
                        ?>
                            <tr>
                                <td class="align-middle"><?php echo $s['name'] . " " . $s['lastName']; ?></td>
                                <td class="align-middle"><input type="number" name="grades[<?php echo $stId; ?>]" step="0.5" min="0" max="20" class="form-control px-2 text-center" value="<?php echo $grade; ?>" placeholder="-"></td>
                                <td>
                                    <textarea name="comments[<?php echo $stId; ?>]" class="form-control task-comment-editor" rows="3" placeholder="Σχόλια..."><?php echo !empty($comments) ? htmlspecialchars($comments) : "<html>\n<body>\n\n</body>\n</html>"; ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            document.querySelectorAll('.task-comment-editor').forEach((element) => {
                ClassicEditor
                    .create(element, {
                        toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', 'undo', 'redo']
                    })
                    .catch(error => {
                        console.error('Task Comment Editor error:', error);
                    });
            });
        </script>
    <?php
    }

    public function assignTasksForm($groups, $db, $booksResult = null)
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Ανάθεση Ασκήσεων σε Ομάδα</div>
                <div class="card-body">
                    <form action="index.php?action=save_group_task" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Επιλογή Ομάδας:</label>
                                <select name="group_id" class="form-control" required>
                                    <?php foreach ($groups as $g) echo "<option value='{$g['id']}'>{$g['group_name']}</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Σύνδεση με Βιβλίο (Προαιρετικά):</label>
                                <select name="book_id" class="form-control">
                                    <option value="">-- Χωρίς συγκεκριμένο βιβλίο --</option>
                                    <?php
                                    if ($booksResult) {
                                        $booksResult->data_seek(0);
                                        while ($b = $booksResult->fetch_assoc()) echo "<option value='{$b['id']}'>{$b['title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ασκήσεις / Περιγραφή:</label>
                            <textarea name="task_text" class="form-control" rows="3" placeholder="π.χ. Κοψίνης 2: 3.13, 3.15"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Επισύναψη Αρχείου (PDF/Εικόνα):</label>
                            <input type="file" name="task_file" class="form-control-file border p-1 bg-white w-100">
                        </div>
                        <button type="submit" class="btn btn-success">Αποστολή Ασκήσεων</button>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5>Τρέχουσες Ασκήσεις ανά Ομάδα</h5>
                    <a href="index.php?action=list_all_tasks" class="btn btn-outline-info btn-sm"><i class="fa fa-history"></i> Όλο το Ιστορικό</a>
                </div>
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 30%;">Ομάδα</th>
                            <th>Τελευταία Ανάθεση</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g):
                            $tasks = $db->getGroupTasks($g['id']);
                        ?>
                            <tr>
                                <td><strong><?php echo $g['group_name']; ?></strong></td>
                                <td>
                                    <?php if (empty($tasks)): ?>
                                        <span class="text-muted">Καμία ανάθεση</span>
                                    <?php else:
                                        $task = $tasks[0]; // Δείχνουμε την τελευταία για βαθμολόγηση
                                        $bookTitle = $task['book_title'];
                                        $taskDate = date('d/m/Y', strtotime($task['date_added']));
                                    ?>
                                        <?php if ($bookTitle): ?>
                                            <span class="badge badge-dark"><?php echo $bookTitle; ?></span><br>
                                        <?php endif; ?>
                                        <?php echo nl2br($task['task_text']); ?>
                                        <?php if (!empty($task['task_file'])): ?>
                                            <div class="mt-1"><small><i class="fa fa-paperclip"></i> <?php echo $task['task_file']; ?></small></div>
                                        <?php endif; ?>
                                        <?php if ($taskDate): ?>
                                            <div class="text-muted small mt-1">Ημ/νία: <?php echo $taskDate; ?></div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="index.php?action=grade_task&task_id=<?php echo $task['id']; ?>" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i> Βαθμολόγηση</a>
                                            <?php
                                            $graded = $task['graded_count'] ?? 0;
                                            $total = $task['total_students'] ?? 0;
                                            $badgeClass = ($graded > 0) ? (($graded >= $total) ? 'badge-success' : 'badge-warning') : 'badge-secondary';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> ml-1" style="font-size: 0.7rem;">
                                                <i class="fa fa-check"></i> <?php echo "$graded / $total"; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }

    public function manageExerciseTypesForm($types)
    {
    ?>
        <div class="container mt-4">
            <h3><i class="fa fa-tags text-info"></i> Διαχείριση Τύπων Ασκήσεων / Τεχνικών</h3>
            <hr>
            <div class="card p-4 mb-4 shadow-sm bg-light">
                <h5>Προσθήκη Νέου Τύπου</h5>
                <form action="index.php?action=save_exercise_type" method="post" class="form-inline">
                    <input type="text" name="type_name" class="form-control mr-2 w-50" placeholder="π.χ. Δυναμικός Προγραμματισμός, Στοίβα" required>
                    <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Προσθήκη Τύπου</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered bg-white shadow-sm">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th style="width: 10%">ID</th>
                            <th>Ονομασία Τύπου / Τεχνικής</th>
                            <th style="width: 20%">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $t): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $t['id']; ?></td>
                                <td class="align-middle"><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                                <td class="text-center">
                                    <a href="index.php?action=delete_exercise_type&id=<?php echo $t['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Προσοχή! Η διαγραφή θα επηρεάσει την κατηγοριοποίηση στα υπάρχοντα μεζεδάκια. Σίγουρα διαγραφή;');">
                                        <i class="fa fa-trash"></i> Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php
    }
}
