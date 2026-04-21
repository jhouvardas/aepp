<?php
include_once 'AdminFormMaker.php';

class TheoryAdminFormMaker extends AdminFormMaker
{
    public function addTheoryForm($booksResult)
    {
?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3><i class="fa fa-plus-circle"></i> Εισαγωγή Νέας Ερώτησης</h3>
            <form action="index.php?action=save_theory" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control">
                            <?php if ($booksResult) while ($row = $booksResult->fetch_assoc()) echo "<option value='{$row['id']}'>{$row['title']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label>Κεφάλαιο</label><input type="text" name="chapter_num" class="form-control" required></div>
                </div>
                <div class="mb-3"><label>Ερώτηση</label><textarea name="question_text" id="editor_question_add" class="form-control"></textarea></div>
                <div class="mb-3"><label>Απάντηση</label><textarea name="answer_text" id="editor_answer_add" class="form-control"></textarea></div>
                <button type="submit" class="btn btn-danger w-100 btn-lg">Αποθήκευση</button>
            </form>
        </div>
        <script>
            ClassicEditor.create(document.querySelector('#editor_question_add')).catch(e => console.error(e));
            ClassicEditor.create(document.querySelector('#editor_answer_add')).catch(e => console.error(e));
        </script>
    <?php
    }

    public function manageBooksForm($booksResult)
    {
    ?>
        <div class="container mt-4">
            <h3>Διαχείριση Βιβλίων</h3>
            <form action="index.php?action=save_book" method="post" class="d-flex mb-4">
                <input type="text" name="book_title" class="form-control me-2" placeholder="Τίτλος" required>
                <button type="submit" class="btn btn-success">Προσθήκη</button>
            </form>
            <table class="table table-bordered">
                <?php while ($row = $booksResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['title']; ?></td>
                        <td><a href="index.php?action=delete_book&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Διαγραφή</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php
    }

    public function editTheoryForm($questionData, $booksResult)
    {
    ?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3>Επεξεργασία Ερώτησης</h3>
            <form action="index.php?action=update_theory" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $questionData['id']; ?>">
                <div class="mb-3"><label>Ερώτηση</label><textarea name="question_text" id="editor_question_edit" class="form-control"><?php echo $questionData['question_text']; ?></textarea></div>
                <div class="mb-3"><label>Απάντηση</label><textarea name="answer_text" id="editor_answer_edit" class="form-control"><?php echo $questionData['answer_text']; ?></textarea></div>
                <button type="submit" class="btn btn-primary w-100">Ενημέρωση</button>
            </form>
        </div>
        <script>
            ClassicEditor.create(document.querySelector('#editor_question_edit')).catch(e => console.error(e));
            ClassicEditor.create(document.querySelector('#editor_answer_edit')).catch(e => console.error(e));
        </script>
    <?php
    }

    public function listTheoryQuestions($questionsResult)
    {
    ?>
        <div class="container mt-4">
            <h3>Λίστα Ερωτήσεων Θεωρίας</h3>
            <table class="table table-bordered bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Βιβλίο</th>
                        <th>Κεφ.</th>
                        <th>Ερώτηση</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $questionsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['book_title']; ?></td>
                            <td><?php echo $row['chapter_num']; ?></td>
                            <td><?php echo strip_tags($row['question_text']); ?></td>
                            <td>
                                <a href="index.php?action=edit_theory&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="index.php?action=delete_theory&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Del</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function listTheoryQuestionsForTests($questionsResult)
    {
    ?>
        <div class="container mt-4">
            <form action="index.php?action=create_exam" method="post">
                <table class="table table-bordered bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Επιλογή</th>
                            <th>Κεφ.</th>
                            <th>Ερώτηση</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $questionsResult->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_questions[]" value="<?php echo $row['id']; ?>"></td>
                                <td><?php echo $row['chapter_num']; ?></td>
                                <td><?php echo strip_tags($row['question_text']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success btn-lg w-100 sticky-top">Δημιουργία Τεστ (PDF)</button>
            </form>
        </div>
    <?php
    }

    public function previewExam($questions)
    {
    ?>
        <div class="container mt-4 bg-white p-5 shadow border" id="printableArea">
            <h2 class="text-center underline">ΔΙΑΓΩΝΙΣΜΑ ΑΕΠΠ</h2>
            <div class="mt-5">
                <?php $i = 1;
                while ($row = $questions->fetch_assoc()): ?>
                    <div class="mb-4">
                        <strong>Θέμα <?php echo $i++; ?>:</strong>
                        <div><?php echo $row['question_text']; ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
            <button onclick="window.print();" class="btn btn-success d-print-none mt-4">Εκτύπωση</button>
        </div>
<?php
    }
}
