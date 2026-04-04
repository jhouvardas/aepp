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
        <div class="container mt-4 bg-white p-5 shadow border" id="printableArea">
            <div class="text-center mb-4">
                <h2 style="text-decoration: underline;">ΦΥΛΛΟ ΕΡΓΑΣΙΑΣ / ΔΙΑΓΩΝΙΣΜΑ</h2>
                <h4>Μάθημα: ΑΕΠΠ</h4>
                <div class="d-flex justify-content-between mt-3">
                    <span>Ονοματεπώνυμο: .......................................................................</span>
                    <span>Ημερομηνία: ...... / ...... / 202...</span>
                </div>
                <hr class="mt-3" style="border-top: 2px solid #000;">
            </div>

            <div class="exam-content">
                <?php
                $i = 1;
                while ($row = $questions->fetch_assoc()): ?>
                    <div class="question-block mb-2" style="page-break-inside: avoid;">
                        <div class="d-flex align-items-baseline">
                            <strong class="mr-2" style="white-space: nowrap;">Θέμα <?php echo $i++; ?>:</strong>
                            <div style="font-size: 1.05rem;">
                                <?php echo $row['question_text']; ?>
                            </div>
                        </div>

                        <?php if (!empty($row['question_image'])): ?>
                            <div class="text-center mt-2 mb-2">
                                <img src="../uploads/<?php echo $row['question_image']; ?>" class="img-fluid border" style="max-height:280px;">
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

        <style>
            @media print {

                .navbar,
                .no-print,
                .btn,
                footer {
                    display: none !important;
                }

                body {
                    background: white !important;
                    margin: 0.5cm;
                }

                .container {
                    border: none !important;
                    box-shadow: none !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                #printableArea {
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                }

                .question-block {
                    page-break-inside: avoid;
                }
            }

            #printableArea {
                font-family: "Times New Roman", Times, serif;
                background-color: white;
            }

            .question-block img {
                max-width: 70%;
                height: auto;
            }
        </style>
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
    public function listMezedakia($result)
    {
    ?>
        <div class="container mt-4">
            <h3 class="mb-4"><i class="fa fa-list text-primary"></i> Διαχείριση Μεζεδακίων</h3>
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
                            $mTimestamp = strtotime($row['mezeDate']);
                            $currentTimestamp = time();
                            // Αν η ημερομηνία είναι μετά την τρέχουσα στιγμή
                            $isFuture = ($mTimestamp > $currentTimestamp);

                            // Στυλ για μελλοντικά: Απαλό γκρι φόντο
                            $rowStyle = $isFuture ? 'style="background-color: #f8f9fa; color: #9c9c9c; font-style: italic;"' : '';
                    ?>
                            <tr <?php echo $rowStyle; ?>>
                                <td class="text-center font-weight-bold">
                                    <?php if ($isFuture): ?>
                                        <i class="fa fa-clock-o text-muted" title="Προγραμματισμένο"></i>
                                    <?php endif; ?>
                                    <?php echo $row['mezeNumber']; ?>
                                </td>
                                <td class="text-center small"><?php echo date('d/m/Y', $mTimestamp); ?></td>
                                <td class="small"><?php echo mb_substr(strip_tags($row['mezeText']), 0, 60) . "..."; ?></td>
                                <td class="text-center">
                                    <a href="index.php?action=viewSubmissions&id=<?php echo $row['mezeId']; ?>"
                                        class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-info'; ?> btn-sm">
                                        <i class="fa fa-eye"></i> Λύσεις
                                    </a>

                                    <a href="index.php?action=manageGrades&id=<?php echo $row['mezeId']; ?>"
                                        class="btn <?php echo $isFuture ? 'btn-outline-secondary' : 'btn-primary'; ?> btn-sm">
                                        <i class="fa fa-graduation-cap"></i> Βαθμοί
                                    </a>

                                    <a href="index.php?action=editMezedaki&id=<?php echo $row['mezeId']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i> Διόρθωση
                                    </a>

                                    <a href="index.php?action=deleteMezedaki&id=<?php echo $row['mezeId']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Σίγουρα διαγραφή;')">
                                        <i class="fa fa-trash"></i>
                                    </a>
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
    <?php
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
                <div class="form-group col-md-6">
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
                    <textarea name="mezeHints" class="form-control" rows="3"><?php echo isset($row['mezeHints']) ? $row['mezeHints'] : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Λύση (Προαιρετικά - θα εμφανίζεται σε accordion)</label>
                    <textarea name="mezeSolution" class="form-control" rows="4" placeholder="Γράψε τη λύση εδώ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία Λύσης (Προαιρετικά)</label>
                    <input type="file" name="mezeSolutionImage" class="form-control">
                </div>
                <button type="submit" class="btn btn-warning btn-block font-weight-bold">Αποθήκευση Μεζεδακίου</button>
            </form>
        </div>
    <?php
    }

    public function editMezedakiForm($row)
    {
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
                                        <a href="index.php?action=studentReport&studentId=<?php echo $stId; ?>&name=<?php echo urlencode($fullName); ?>"
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
    public function showSubmissionsForGrading($submissions, $students, $mezeNumber, $allMezedakia, $existingGrades = [])
    {
        $db = new DbHandler(); // Για να ελέγχουμε αν επιτρέπεται η υποβολή
        $mezeNumber = (int)$mezeNumber;
        $mezeId = $_GET['id']; // Το τρέχον μεζεδάκι
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

            if (isset($gradesByStudent[$stId])) {
                $gradedSubmissions[] = ['sub' => $subData, 'grade' => $gradesByStudent[$stId], 'student' => $student];
            } else {
                $pendingSubmissions[] = ['sub' => $subData, 'student' => $student];
            }
        }
    ?>
        <div class="container mt-4">
            <h3 class="text-primary mb-4"><i class="fa fa-mortar-board"></i> Απαντήσεις για το Μεζεδάκι #<?php echo $mezeNumber; ?></h3>

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
                    $lockTitle = $isAllowed ? "Η φόρμα είναι ήδη ανοιχτή" : "Άνοιγμα φόρμας για τον μαθητή";
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

                                    <form action="index.php?action=giveExtension" method="post" class="m-0">
                                        <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                        <input type="hidden" name="meze_id" value="<?php echo $mezeId; ?>">

                                        <button type="submit" class="btn btn-xs <?php echo $isAllowed ? 'btn-outline-success' : 'btn-danger'; ?> py-0 px-2"
                                            style="font-size:0.7rem;" title="<?php echo $lockTitle; ?>">
                                            <i class="fa <?php echo $lockIcon; ?>"></i>
                                            <?php echo $isAllowed ? "Ανοιχτή" : "Άνοιγμα Φόρμας"; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <form action="index.php?action=quickGrade" method="post" class="bg-light p-2 rounded border">
                                <input type="hidden" name="student_id" value="<?php echo $stId; ?>">
                                <input type="hidden" name="meze_id" value="<?php echo ($sub) ? $sub['meze_id'] : $mezeId; ?>">
                                <div class="d-flex align-items-start">
                                    <input type="number" name="grade" step="0.5" class="form-control form-control-sm mr-2" style="width:80px" placeholder="Βαθμός" required>
                                    <textarea name="teacher_comments" class="form-control form-control-sm mr-2" rows="1" style="flex:1; min-height: 31px;" placeholder="Σχόλια παρότρυνσης..."></textarea>
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
                    ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" style="cursor: pointer; border-left: 5px solid #28a745;" onclick="$(this).next('.edit-row').toggle();">
                            <div>
                                <span class="font-weight-bold mr-3"><?php echo $st['name'] . " " . $st['lastName']; ?></span>
                                <span class="badge badge-success px-2 py-1">Βαθμός: <?php echo $grade['grade_value']; ?></span>
                            </div>
                            <i class="fa fa-chevron-down text-muted"></i>
                        </div>
                        <div class="edit-row p-3 border border-top-0 mb-2 bg-light shadow-sm" style="display:none;">
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
    public function showPrintableReport($studentName, $mezeNumber, $grade, $comments, $average, $mezeId)
    {
    ?>
        <div id="printableReport" class="container mt-5 p-5 shadow border" style="background: white; color: black; font-family: 'DejaVu Sans', sans-serif; border-radius: 15px;">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold">Αναφορά Προόδου Μαθητή</h2>
                <h4>Μάθημα: ΑΕΠΠ - Μεζεδάκια</h4>
                <hr style="border-top: 2px solid #eee;">
            </div>

            <div class="mb-4">
                <p style="font-size: 1.1rem;"><strong>Μαθητής:</strong> <?php echo $studentName; ?></p>
                <p style="font-size: 1.1rem;"><strong>Ημερομηνία:</strong> <?php echo date('d/m/Y'); ?></p>
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
                <div class="p-3 border rounded bg-light" style="min-height: 100px; font-style: italic;">
                    <?php echo nl2br($comments); ?>
                </div>
            </div>

            <div class="mt-4 p-3 bg-dark text-white d-flex justify-content-between align-items-center rounded shadow">
                <h5 class="mb-0">Συνολικός Μέσος Όρος:</h5>
                <h4 class="mb-0 font-weight-bold"><?php echo number_format($average, 2); ?> / 20</h4>
            </div>

            <div class="mt-5 text-center d-print-none">
                <button onclick="window.print();" class="btn btn-success btn-lg shadow mr-2">
                    <i class="fa fa-print"></i> Εκτύπωση / PDF
                </button>

                <a href="index.php?action=viewSubmissions&id=<?php echo $mezeId; ?>" class="btn btn-primary btn-lg shadow">
                    <i class="fa fa-arrow-left"></i> Πίσω στις Λύσεις (#<?php echo $mezeNumber; ?>)
                </a>
            </div>
        </div>

        <style>
            @media print {

                .navbar,
                .d-print-none,
                .btn {
                    display: none !important;
                }

                body {
                    background: white !important;
                }

                #printableReport {
                    border: none !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
            }
        </style>
<?php
    }
}
