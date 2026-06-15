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
                    <div class="col-md-4 mb-3">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control">
                            <?php if ($booksResult) while ($row = $booksResult->fetch_assoc()) echo "<option value='{$row['id']}'>{$row['title']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3"><label>Κεφάλαιο</label><input type="text" name="chapter_num" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label>Σελίδα</label><input type="number" name="page_number" class="form-control" value="0"></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Ερώτηση</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditorTheory('editor_question_add', 'questionEditorAdd', Base64UploadAdapterPluginTheory)" title="Εναλλαγή σε προβολή κώδικα HTML">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="question_text" id="editor_question_add" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Απάντηση</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditorTheory('editor_answer_add', 'answerEditorAdd', Base64UploadAdapterPluginTheory)" title="Εναλλαγή σε προβολή κώδικα HTML">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="answer_text" id="editor_answer_add" class="form-control"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Εικόνα Ερώτησης (Προαιρετικό)</label>
                        <input type="file" name="q_file" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Εικόνα Απάντησης (Προαιρετικό)</label>
                        <input type="file" name="a_file" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 btn-lg">Αποθήκευση</button>
            </form>
        </div>
        <script>
            class Base64UploadAdapterTheory {
                constructor(loader) {
                    this.loader = loader;
                }
                upload() {
                    return this.loader.file.then(file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const MAX_WIDTH = 800;
                                let width = img.width;
                                let height = img.height;
                                if (width > MAX_WIDTH) {
                                    height = Math.round((height * MAX_WIDTH) / width);
                                    width = MAX_WIDTH;
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, width, height);
                                ctx.drawImage(img, 0, 0, width, height);
                                resolve({
                                    default: canvas.toDataURL('image/jpeg', 0.85)
                                });
                            };
                            img.onerror = () => resolve({
                                default: e.target.result
                            });
                            img.src = e.target.result;
                        };
                        reader.onerror = error => reject(error);
                        reader.readAsDataURL(file);
                    }));
                }
                abort() {}
            }

            function Base64UploadAdapterPluginTheory(editor) {
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new Base64UploadAdapterTheory(loader);
            }

            window.questionEditorAdd = null;
            window.answerEditorAdd = null;

            ClassicEditor.create(document.querySelector('#editor_question_add'), {
                extraPlugins: [Base64UploadAdapterPluginTheory]
            }).then(editor => {
                window.questionEditorAdd = editor;
                editor.model.document.on('change:data', () => {
                    document.querySelector('#editor_question_add').value = editor.getData();
                });
            }).catch(e => console.error(e));

            ClassicEditor.create(document.querySelector('#editor_answer_add'), {
                extraPlugins: [Base64UploadAdapterPluginTheory]
            }).then(editor => {
                window.answerEditorAdd = editor;
                editor.model.document.on('change:data', () => {
                    document.querySelector('#editor_answer_add').value = editor.getData();
                });
            }).catch(e => console.error(e));

            function toggleCKEditorTheory(textareaId, editorVar, plugin) {
                if (window[editorVar]) {
                    document.querySelector('#' + textareaId).value = window[editorVar].getData();
                    window[editorVar].destroy().then(() => {
                        window[editorVar] = null;
                    });
                } else {
                    ClassicEditor.create(document.querySelector('#' + textareaId), {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            document.querySelector('#' + textareaId).value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }
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

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Βιβλίο</label>
                        <select name="book_id" class="form-control" required>
                            <?php if ($booksResult && $booksResult->num_rows > 0) {
                                mysqli_data_seek($booksResult, 0); // Επαναφορά του δείκτη
                                while ($row = $booksResult->fetch_assoc()) {
                                    $selected = ($row['id'] == $questionData['book_id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['title']}</option>";
                                }
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Κεφάλαιο</label>
                        <input type="text" name="chapter_num" class="form-control" value="<?php echo htmlspecialchars($questionData['chapter_num'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Σελίδα</label>
                        <input type="number" name="page_number" class="form-control" value="<?php echo htmlspecialchars($questionData['page_number'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Ερώτηση</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditorTheory('editor_question_edit', 'questionEditorEdit', Base64UploadAdapterPluginTheoryEdit)" title="Εναλλαγή σε προβολή κώδικα HTML">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="question_text" id="editor_question_edit" class="form-control"><?php echo $questionData['question_text']; ?></textarea>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Απάντηση</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="toggleCKEditorTheory('editor_answer_edit', 'answerEditorEdit', Base64UploadAdapterPluginTheoryEdit)" title="Εναλλαγή σε προβολή κώδικα HTML">
                            <i class="fa fa-code"></i> HTML
                        </button>
                    </div>
                    <textarea name="answer_text" id="editor_answer_edit" class="form-control"><?php echo $questionData['answer_text']; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Νέα Εικόνα Ερώτησης (Προαιρετικό)</label>
                        <?php if (!empty($questionData['question_image'])): ?>
                            <div class="mb-3 p-2 border rounded bg-white shadow-sm d-inline-block w-100">
                                <small class="text-muted d-block mb-2"><i class="fa fa-image"></i> Τρέχουσα εικόνα:</small>
                                <img src="../uploads/<?php echo $questionData['question_image']; ?>" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="q_file" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Νέα Εικόνα Απάντησης (Προαιρετικό)</label>
                        <?php if (!empty($questionData['answer_image'])): ?>
                            <div class="mb-3 p-2 border rounded bg-white shadow-sm d-inline-block w-100">
                                <small class="text-muted d-block mb-2"><i class="fa fa-image"></i> Τρέχουσα εικόνα απάντησης:</small>
                                <img src="../uploads/<?php echo $questionData['answer_image']; ?>" class="img-thumbnail border-success" style="max-height: 120px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="a_file" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Ενημέρωση</button>
            </form>
        </div>
        <script>
            class Base64UploadAdapterTheoryEdit {
                constructor(loader) {
                    this.loader = loader;
                }
                upload() {
                    return this.loader.file.then(file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const MAX_WIDTH = 800;
                                let width = img.width;
                                let height = img.height;
                                if (width > MAX_WIDTH) {
                                    height = Math.round((height * MAX_WIDTH) / width);
                                    width = MAX_WIDTH;
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, width, height);
                                ctx.drawImage(img, 0, 0, width, height);
                                resolve({
                                    default: canvas.toDataURL('image/jpeg', 0.85)
                                });
                            };
                            img.onerror = () => resolve({
                                default: e.target.result
                            });
                            img.src = e.target.result;
                        };
                        reader.onerror = error => reject(error);
                        reader.readAsDataURL(file);
                    }));
                }
                abort() {}
            }

            function Base64UploadAdapterPluginTheoryEdit(editor) {
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new Base64UploadAdapterTheoryEdit(loader);
            }

            window.questionEditorEdit = null;
            window.answerEditorEdit = null;

            function initSmartEditorTheoryEdit(textareaId, editorVar, plugin) {
                const textarea = document.querySelector('#' + textareaId);
                if (!textarea) return;

                const val = textarea.value;
                const cmVar = editorVar + '_cm';
                const formatBtnId = 'formatBtn_' + textareaId;
                const formatBtn = document.getElementById(formatBtnId);

                const hasRawHtml = val.includes('<pre') || val.includes('<table') || val.includes('<div');

                if (hasRawHtml) {
                    window[cmVar] = CodeMirror.fromTextArea(textarea, {
                        mode: "xml",
                        htmlMode: true,
                        lineNumbers: true,
                        lineWrapping: true,
                        viewportMargin: Infinity
                    });
                    window[cmVar].on('change', function(cm) {
                        textarea.value = cm.getValue();
                    });
                    if (formatBtn) formatBtn.classList.remove('d-none');
                } else {
                    ClassicEditor.create(textarea, {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            textarea.value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }

            initSmartEditorTheoryEdit('editor_question_edit', 'questionEditorEdit', Base64UploadAdapterPluginTheoryEdit);
            initSmartEditorTheoryEdit('editor_answer_edit', 'answerEditorEdit', Base64UploadAdapterPluginTheoryEdit);

            function formatCMTheory(cmVar) {
                if (window[cmVar] && typeof html_beautify !== 'undefined') {
                    const unformatted = window[cmVar].getValue();
                    const formatted = html_beautify(unformatted, {
                        indent_size: 4,
                        wrap_line_length: 0,
                        unformatted: ['pre', 'code']
                    });
                    window[cmVar].setValue(formatted);
                }
            }

            function toggleCKEditorTheory(textareaId, editorVar, plugin) {
                const cmVar = editorVar + '_cm';
                const formatBtnId = 'formatBtn_' + textareaId;
                const formatBtn = document.getElementById(formatBtnId);

                if (window[editorVar]) {
                    document.querySelector('#' + textareaId).value = window[editorVar].getData();
                    window[editorVar].destroy().then(() => {
                        window[editorVar] = null;

                        window[cmVar] = CodeMirror.fromTextArea(document.querySelector('#' + textareaId), {
                            mode: "xml",
                            htmlMode: true,
                            lineNumbers: true,
                            lineWrapping: true,
                            viewportMargin: Infinity
                        });
                        window[cmVar].on('change', function(cm) {
                            document.querySelector('#' + textareaId).value = cm.getValue();
                        });

                        if (formatBtn) formatBtn.classList.remove('d-none');
                    });
                } else {
                    if (window[cmVar]) {
                        document.querySelector('#' + textareaId).value = window[cmVar].getValue();
                        window[cmVar].toTextArea();
                        window[cmVar] = null;

                        if (formatBtn) formatBtn.classList.add('d-none');
                    }
                    ClassicEditor.create(document.querySelector('#' + textareaId), {
                        extraPlugins: [plugin]
                    }).then(editor => {
                        window[editorVar] = editor;
                        editor.model.document.on('change:data', () => {
                            document.querySelector('#' + textareaId).value = editor.getData();
                        });
                    }).catch(error => console.error(error));
                }
            }
        </script>
    <?php
    }

    public function listTheoryQuestions($questionsResult)
    {
    ?>
        <div class="container mt-4">
            <?php if (isset($_GET['action']) && in_array($_GET['action'], ['update_theory', 'save_theory']) || isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-4" role="alert">
                    <strong>Επιτυχία!</strong> Η ερώτηση αποθηκεύτηκε επιτυχώς.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ((isset($_GET['action']) && $_GET['action'] === 'delete_theory') || isset($_GET['deleted'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                    <strong>Διαγράφηκε!</strong> Η ερώτηση διαγράφηκε οριστικά.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
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
                                <a href="index.php?action=delete_theory&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Είστε σίγουροι για τη διαγραφή αυτής της ερώτησης;');">Del</a>
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
                <div class="sticky-top bg-light p-3 border-top shadow-sm d-flex gap-3 justify-content-center" style="z-index: 1000;">
                    <button type="submit" class="btn btn-success btn-lg w-50 shadow"><i class="fa fa-eye"></i> Προεπισκόπηση / PDF</button>
                    <button type="submit" formaction="index.php?action=export_word_exam" class="btn btn-primary btn-lg w-50 shadow"><i class="fa fa-file-word-o"></i> Εξαγωγή σε Word (.doc)</button>
                </div>
            </form>
        </div>
    <?php
    }

    public function previewExam($questions)
    {
    ?>
        <div class="container mt-4 bg-white p-5 shadow border" id="printableArea">
            <div id="examContent">
                <h2 class="text-center underline">ΔΙΑΓΩΝΙΣΜΑ ΑΕΠΠ</h2>
                <div class="mt-5">
                    <?php
                    $i = 1;
                    // Δημιουργία δυναμικού απόλυτου URL (βοηθάει και στην αντιγραφή στο Google Docs)
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $baseUploadUrl = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/uploads/';

                    while ($row = $questions->fetch_assoc()): ?>
                        <div class="mb-4">
                            <strong>Θέμα <?php echo $i++; ?>:</strong>
                            <div class="mt-2"><?php echo $row['question_text']; ?></div>
                            <?php if (!empty($row['question_image'])): ?>
                                <div class="mt-3 text-center" style="text-align: center;">
                                    <img src="<?php echo $baseUploadUrl . $row['question_image']; ?>" style="max-width: 100%; height: auto;" alt="Εικόνα Θέματος">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="d-print-none mt-5 pt-3 border-top text-center">
                <button onclick="window.print();" class="btn btn-success btn-lg shadow-sm mx-2"><i class="fa fa-print"></i> Εκτύπωση / PDF</button>
                <button onclick="copyForGoogleDocs();" class="btn btn-primary btn-lg shadow-sm mx-2"><i class="fa fa-google"></i> Αντιγραφή για Google Docs</button>
            </div>
        </div>
        <script>
            function copyForGoogleDocs() {
                var range = document.createRange();
                range.selectNode(document.getElementById("examContent"));
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                try {
                    document.execCommand('copy');
                    alert('✅ Το διαγώνισμα αντιγράφηκε με επιτυχία!\n\nΓια να το επεξεργαστείτε:\n1. Ανοίξτε ένα νέο έγγραφο στο Google Docs (ή γράψτε docs.new στον browser σας)\n2. Κάντε Επικόλληση (Ctrl+V ή Δεξί Κλικ -> Επικόλληση).');
                } catch (err) {
                    alert('Αποτυχία αντιγραφής. Παρακαλώ επιλέξτε το κείμενο με το ποντίκι σας και κάντε Αντιγραφή (Ctrl+C).');
                }
                window.getSelection().removeAllRanges();
            }
        </script>
<?php
    }
}
