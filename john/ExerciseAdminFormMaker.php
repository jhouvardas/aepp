<?php
include_once 'AdminFormMaker.php';

class ExerciseAdminFormMaker extends AdminFormMaker
{
    public function displayKenaForm()
    {
?>
        <div class="container mt-4 border p-4 bg-light shadow">
            <h3>Νέα Άσκηση Κενών</h3>
            <form action="index.php?action=saveKena" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4"><label>Έτος</label><input type="number" name="exerciseYear" class="form-control" required></div>
                    <div class="col-md-4"><label>Είδος</label><select name="examType" class="form-control">
                            <option>Κανονικές</option>
                            <option>Επαναληπτικές</option>
                        </select></div>
                    <div class="col-md-4"><label>Σχολείο</label><select name="schoolType" class="form-control">
                            <option>Ημερήσια</option>
                            <option>Εσπερινά</option>
                        </select></div>
                </div>
                <div class="mt-3"><label>HTML Κώδικας</label><textarea name="exerciseHtml" class="form-control" rows="15"></textarea></div>
                <button type="submit" class="btn btn-primary mt-3 w-100">Αποθήκευση</button>
            </form>
        </div>
    <?php
    }

    public function listKenaExercises($result)
    {
    ?>
        <div class="container mt-4">
            <h3>Διαχείριση Ασκήσεων (Κενά)</h3>
            <table class="table table-bordered bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Έτος</th>
                        <th>Είδος</th>
                        <th>Σχολείο</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['exerciseYear']; ?></td>
                            <td><?php echo $row['examType']; ?></td>
                            <td><?php echo $row['schoolType']; ?></td>
                            <td><a href="index.php?action=deleteKena&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a></td>
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
        <div class="container mt-4 border p-4 bg-light">
            <h3>Εισαγωγή Θέματος Γ/Δ</h3>
            <form action="index.php?action=saveThemaG" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Έτος</label><input type="number" name="etos" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Τύπος</label><select name="thema_type" class="form-control">
                            <option value="G">Θέμα Γ</option>
                            <option value="D">Θέμα Δ</option>
                        </select></div>
                </div>
                <div class="mb-3"><label>Εκφώνηση</label><textarea name="ekfonisi" class="form-control" rows="10"></textarea></div>
                <button type="submit" class="btn btn-primary w-100">Αποθήκευση</button>
            </form>
        </div>
    <?php
    }

    public function listThemataG($result)
    {
    ?>
        <div class="container mt-4">
            <h3>Λίστα Θεμάτων Γ/Δ</h3>
            <table class="table table-bordered">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['etos']; ?></td>
                        <td>Θέμα <?php echo $row['thema_type'] ?? 'Γ'; ?></td>
                        <td><a href="index.php?action=deleteThemaG&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Del</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php
    }

    public function assignTasksForm($groups, $db, $booksResult = null)
    {
    ?>
        <div class="container mt-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Ανάθεση Εργασίας σε Ομάδα</div>
                <div class="card-body">
                    <form action="index.php?action=save_group_task" method="POST" enctype="multipart/form-data">
                        <div class="mb-3"><label>Ομάδα</label><select name="group_id" class="form-control">
                                <?php foreach ($groups as $g) echo "<option value='{$g['id']}'>{$g['group_name']}</option>"; ?>
                            </select></div>
                        <div class="mb-3"><label>Περιγραφή</label><textarea name="task_text" class="form-control" rows="3"></textarea></div>
                        <div class="mb-3"><label>Συνημμένο Αρχείο (π.χ. PDF)</label><input type="file" name="task_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png"></div>
                        <button type="submit" class="btn btn-success w-100">Αποστολή</button>
                    </form>
                </div>
            </div>
        </div>
    <?php
    }

    public function listAllTasks($tasks)
    {
    ?>
        <div class="container mt-4">
            <h3>Ιστορικό Εργασιών</h3>
            <table class="table table-bordered bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Ημ/νία</th>
                        <th>Ομάδα</th>
                        <th>Εργασία</th>
                        <th>Αρχείο</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <?php
                        $ungradedCount = $task['total_students'] - $task['graded_count'];
                        $rowStyle = ($ungradedCount > 0) ? 'style="background-color: #f8d7da;"' : '';
                        ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td><?php echo date('d/m/Y', strtotime($task['date_added'])); ?></td>
                            <td>
                                <strong><?php echo $task['group_name']; ?></strong><br>
                                <span class="badge <?php echo ($ungradedCount > 0) ? 'bg-danger' : 'bg-success'; ?> mt-1"><?php echo $task['graded_count']; ?>/<?php echo $task['total_students']; ?> Βαθμολογήθηκαν</span>
                            </td>
                            <td><?php echo $task['task_text']; ?></td>
                            <td>
                                <?php if (!empty($task['task_file'])): ?>
                                    <a href="../uploads/tasks/<?php echo $task['task_file']; ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o"></i> Προβολή</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="index.php?action=grade_task&task_id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Βαθμολόγηση</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }
}
