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
            <h3 class="mb-4"><i class="fa fa-list"></i> Διαχείριση Μεζεδακίων</h3>
            <table class="table table-bordered table-striped shadow-sm">
                <thead class="thead-dark text-center">
                    <tr>
                        <th style="width: 8%">#</th>
                        <th style="width: 15%">Ημερομηνία</th>
                        <th style="width: 40%">Εκφώνηση (Preview)</th>
                        <th style="width: 37%">Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?php echo $row['mezeNumber']; ?></td>
                                <td class="text-center"><?php echo date('d/m/Y', strtotime($row['mezeDate'])); ?></td>
                                <td><?php echo mb_substr(strip_tags($row['mezeText']), 0, 60) . "..."; ?></td>
                                <td class="text-center">
                                    <a href="index.php?action=manageGrades&id=<?php echo $row['mezeId']; ?>"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-graduation-cap"></i> Βαθμοί
                                    </a>

                                    <a href="index.php?action=editMezedaki&id=<?php echo $row['mezeId']; ?>"
                                        class="btn btn-info btn-sm">
                                        <i class="fa fa-edit"></i> Διόρθωση
                                    </a>

                                    <a href="index.php?action=deleteMezedaki&id=<?php echo $row['mezeId']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Σίγουρα διαγραφή του μεζεδακίου #<?php echo $row['mezeNumber']; ?>;')">
                                        <i class="fa fa-trash"></i> Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Δεν βρέθηκαν μεζεδάκια.</td>
                        </tr>
                    <?php endif; ?>
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
                        // Μετατρέπουμε την τιμή από τη βάση σε timestamp
                        $timestamp = (!empty($row['solutionDate'])) ? strtotime($row['solutionDate']) : false;

                        // Αν το timestamp είναι άκυρο, ή η ημερομηνία είναι η "μηδενική" της MySQL, ή είναι πριν το 1980
                        if (!$timestamp || $timestamp <= 0 || date('Y', $timestamp) < 1980) {
                            // Προτείνουμε την τρέχουσα ημερομηνία και ώρα
                            $currentVal = date('Y-m-d\TH:i');
                        } else {
                            // Αλλιώς κρατάμε αυτή που έχει η βάση
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

                <div class="form-group card p-3 bg-white">
                    <label class="font-weight-bold">Εικόνα Εκφώνησης</label>
                    <?php if (!empty($row['mezeImage'])): ?>
                        <div class="mb-2">
                            <small>Τρέχουσα εικόνα:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeImage']; ?>" width="100" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="mezeImage" class="form-control">
                </div>

                <div class="form-group card p-3 bg-white border-success">
                    <label class="font-weight-bold text-success">Λύση (mezeSolution)</label>
                    <textarea name="mezeSolution" class="form-control" rows="5"><?php echo $row['mezeSolution']; ?></textarea>

                    <label class="mt-3">Εικόνα Λύσης (Προαιρετικά)</label>
                    <?php if (!empty($row['mezeSolutionImage'])): ?>
                        <div class="mb-2">
                            <small>Τρέχουσα εικόνα λύσης:</small><br>
                            <img src="../images/mezedakia/<?php echo $row['mezeSolutionImage']; ?>" width="100" class="img-thumbnail border-success">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="mezeSolutionImage" class="form-control">
                </div>

                <div class="mt-4 row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                            <i class="fa fa-save"></i> Ενημέρωση Μεζεδακίου
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="index.php?action=listMezedakia" class="btn btn-secondary btn-block">
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
                            <th style="width: 200px;">Βαθμός (0-20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student):
                            // Βρίσκουμε αν υπάρχει ήδη βαθμός για αυτόν τον μαθητή
                            $currentGrade = isset($existingGrades[$student['studentId']]) ? $existingGrades[$student['studentId']] : "";
                        ?>
                            <tr>
                                <td><?php echo $student['name'] . " " . $student['lastName']; ?></td>
                                <td>
                                    <input type="number"
                                        name="grades[<?php echo $student['studentId']; ?>]"
                                        class="form-control"
                                        step="0.1" min="0" max="20"
                                        value="<?php echo $currentGrade; ?>"
                                        placeholder="-">
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
}
