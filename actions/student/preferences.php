<?php
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

$studentId = (int)$_SESSION['student_id'];

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pref_ids'])) {
    $ids = array_filter(array_map('intval', $_POST['pref_ids']));
    $db->saveStudentPreferences($studentId, array_values($ids));
    echo "<script>window.location.href='index.php?action=studentPreferences&saved=1';</script>";
    exit();
}

$data        = $db->getSchoolsForStudent();
$schools     = $data['schools'];
$schoolYear  = $data['year'];
$directions  = $data['directions'];
$currentPrefs = $db->getStudentPreferences($studentId);

// Build set of already-preferred school IDs for quick lookup
$prefIds = array_column($currentPrefs, 'id');

$savedMsg = isset($_GET['saved']) && $_GET['saved'] == '1';
?>
<div class="container mt-4 mb-5">

    <?php if ($savedMsg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check"></i> Οι προτιμήσεις σου αποθηκεύτηκαν!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0"><i class="fa fa-university text-warning"></i> Σχολές Προτίμησης</h4>
        <?php if ($schoolYear): ?>
            <span class="badge bg-secondary ms-2">Βάσεις <?php echo $schoolYear; ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($schools)): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Δεν υπάρχουν ακόμα δεδομένα σχολών. Ο καθηγητής θα τα ανεβάσει σύντομα.
        </div>
    <?php else: ?>

    <!-- Φόρμα αποθήκευσης (hidden inputs, συμπληρώνονται από JS) -->
    <form method="post" id="prefsForm">
        <div class="row g-3">

            <!-- Αριστερά: Λίστα Σχολών -->
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <i class="fa fa-search"></i> Αναζήτηση Σχολών
                    </div>
                    <div class="card-body pb-1">
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <input type="text" id="searchBox" class="form-control form-control-sm"
                                       placeholder="Αναζήτηση σχολή / ίδρυμα / πόλη...">
                            </div>
                            <div class="col-4">
                                <select id="dirFilter" class="form-select form-select-sm">
                                    <option value="">Όλες κατ/νσεις</option>
                                    <?php foreach ($directions as $d): ?>
                                        <option value="<?php echo htmlspecialchars($d); ?>">
                                            <?php echo htmlspecialchars($d); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <p class="text-muted small mb-1" id="schoolCount">
                            <?php echo count($schools); ?> σχολές
                        </p>
                    </div>
                    <div style="max-height:520px; overflow-y:auto;" id="schoolsContainer">
                        <table class="table table-sm table-hover mb-0" id="schoolsTable">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width:36px"></th>
                                    <th>Ίδρυμα / Τμήμα</th>
                                    <th style="width:70px">Κατ/νση</th>
                                    <th style="width:80px" class="text-end">Βαθμός Τελευταίου</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schools as $s): ?>
                                    <tr class="school-row"
                                        data-id="<?php echo (int)$s['id']; ?>"
                                        data-name="<?php echo htmlspecialchars(mb_strtolower($s['department'] . ' ' . $s['university'] . ' ' . $s['city'], 'UTF-8')); ?>"
                                        data-dir="<?php echo htmlspecialchars($s['direction']); ?>">
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-xs btn-success add-btn py-0 px-1"
                                                    onclick="addPref(<?php echo (int)$s['id']; ?>, <?php echo htmlspecialchars(json_encode($s['department']), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($s['university']), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($s['base_points']), ENT_QUOTES); ?>)"
                                                    title="Προσθήκη στις προτιμήσεις">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="fw-bold small"><?php echo htmlspecialchars($s['department']); ?></div>
                                            <div class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($s['university']); ?><?php if ($s['city']): ?> &middot; <?php echo htmlspecialchars($s['city']); ?><?php endif; ?></div>
                                        </td>
                                        <td><span class="badge bg-secondary" style="font-size:0.65rem;"><?php echo htmlspecialchars($s['direction']); ?></span></td>
                                        <td class="text-end fw-bold text-success small"><?php echo htmlspecialchars($s['base_points']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Δεξιά: Προτιμήσεις -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-star"></i> Προτιμήσεις μου</span>
                        <span class="badge bg-dark" id="prefCount">0</span><span class="text-dark small ms-1">/ 10</span>
                    </div>
                    <div class="card-body p-0" style="min-height:120px;">
                        <ol class="list-group list-group-flush" id="prefList">
                            <!-- Συμπληρώνεται από JS -->
                        </ol>
                        <div id="emptyPrefs" class="text-center text-muted py-4 px-3" style="display:none;">
                            <i class="fa fa-hand-o-left fa-2x mb-2"></i><br>
                            Επέλεξε σχολές από αριστερά
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="fa fa-save"></i> Αποθήκευση Προτιμήσεων
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <!-- Hidden inputs for pref_ids[] -->
        <div id="hiddenInputs"></div>
    </form>

    <?php endif; ?>
</div>

<style>
.btn-xs { font-size: 0.75rem; line-height: 1.2; }

/* Preference list item — mobile-first flex row */
.pref-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
    min-width: 0;          /* critical: allows flex children to shrink below content size */
    overflow: hidden;
}
.pref-item:last-child { border-bottom: none; }

.pref-num {
    font-weight: bold;
    color: #ffc107;
    min-width: 22px;
    flex-shrink: 0;
}
.pref-info {
    flex: 1 1 0;           /* grow AND shrink, basis 0 — lets it compress properly */
    min-width: 0;
    overflow: hidden;
}
.pref-info .dept {
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.pref-info .uni {
    font-size: 0.7rem;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.pref-points {
    font-size: 0.78rem;
    font-weight: bold;
    color: #28a745;
    white-space: nowrap;
    flex-shrink: 0;
    text-align: right;
    min-width: 44px;
}
.pref-btns {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}
.pref-btns button { font-size: 0.65rem; padding: 1px 4px; line-height: 1.2; }

/* Schools table */
.school-row.already-added td { opacity: 0.4; }
.school-row.already-added .add-btn { visibility: hidden; }

/* On small screens, stack the two panels */
@media (max-width: 991px) {
    #schoolsContainer { max-height: 340px; }
}
</style>

<script>
// Αρχική κατάσταση από PHP (τρέχουσες προτιμήσεις)
var prefs = <?php echo json_encode(array_map(function($p) {
    return [
        'id'     => (int)$p['id'],
        'dept'   => $p['department'],
        'uni'    => $p['university'],
        'points' => $p['base_points'],
    ];
}, $currentPrefs)); ?>;

function renderPrefs() {
    var list = document.getElementById('prefList');
    var empty = document.getElementById('emptyPrefs');
    var count = document.getElementById('prefCount');
    var hidden = document.getElementById('hiddenInputs');

    list.innerHTML = '';
    hidden.innerHTML = '';
    count.textContent = prefs.length;

    if (prefs.length === 0) {
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
        prefs.forEach(function(p, i) {
            var li = document.createElement('li');
            li.className = 'list-group-item p-0';
            li.style.overflow = 'hidden';
            li.setAttribute('data-id', p.id);
            li.innerHTML =
                '<div class="pref-item">' +
                    '<span class="pref-num">' + (i + 1) + '.</span>' +
                    '<div class="pref-info">' +
                        '<div class="dept" title="' + escHtml(p.dept) + '">' + escHtml(p.dept) + '</div>' +
                        '<div class="uni">' + escHtml(p.uni) + '</div>' +
                    '</div>' +
                    '<span class="pref-points">' + escHtml(p.points) + '</span>' +
                    '<div class="pref-btns">' +
                        (i > 0 ? '<button type="button" class="btn btn-outline-secondary btn-xs" onclick="movePref(' + i + ',-1)"><i class="fa fa-caret-up"></i></button>' : '<button type="button" class="btn btn-xs" disabled style="visibility:hidden"></button>') +
                        (i < prefs.length - 1 ? '<button type="button" class="btn btn-outline-secondary btn-xs" onclick="movePref(' + i + ',1)"><i class="fa fa-caret-down"></i></button>' : '<button type="button" class="btn btn-xs" disabled style="visibility:hidden"></button>') +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-xs ms-1" onclick="removePref(' + i + ')"><i class="fa fa-times"></i></button>' +
                '</div>';
            list.appendChild(li);

            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'pref_ids[]';
            inp.value = p.id;
            hidden.appendChild(inp);
        });
    }

    // Mark already-added rows in the table
    document.querySelectorAll('.school-row').forEach(function(row) {
        var id = parseInt(row.getAttribute('data-id'));
        var isAdded = prefs.some(function(p) { return p.id === id; });
        if (isAdded) {
            row.classList.add('already-added');
        } else {
            row.classList.remove('already-added');
        }
    });
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

var MAX_PREFS = 10;

function addPref(id, dept, uni, points) {
    if (prefs.some(function(p) { return p.id === id; })) return;
    if (prefs.length >= MAX_PREFS) {
        alert('Μπορείς να επιλέξεις έως ' + MAX_PREFS + ' σχολές.');
        return;
    }
    prefs.push({ id: id, dept: dept, uni: uni, points: points });
    renderPrefs();
}

function removePref(index) {
    prefs.splice(index, 1);
    renderPrefs();
}

function movePref(index, dir) {
    var newIndex = index + dir;
    if (newIndex < 0 || newIndex >= prefs.length) return;
    var tmp = prefs[index];
    prefs[index] = prefs[newIndex];
    prefs[newIndex] = tmp;
    renderPrefs();
}

// Search & filter
function filterSchools() {
    var q   = document.getElementById('searchBox').value.toLowerCase();
    var dir = document.getElementById('dirFilter').value;
    var rows = document.querySelectorAll('.school-row');
    var visible = 0;

    rows.forEach(function(row) {
        var nameMatch = !q || row.getAttribute('data-name').indexOf(q) !== -1;
        var dirMatch  = !dir || row.getAttribute('data-dir') === dir;
        if (nameMatch && dirMatch) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
    document.getElementById('schoolCount').textContent = visible + ' σχολές';
}

document.getElementById('searchBox').addEventListener('input', filterSchools);
document.getElementById('dirFilter').addEventListener('change', filterSchools);

// Initial render
renderPrefs();
</script>
