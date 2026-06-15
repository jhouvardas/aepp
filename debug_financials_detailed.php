<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ΑΣΦΑΛΕΙΑ: Απαγόρευση δημόσιας πρόσβασης σε ευαίσθητα δεδομένα.
// Διαγράψτε ή σχολιάστε την παρακάτω γραμμή ΜΟΝΟ όταν θέλετε να κάνετε debug.
die("Access Denied. Το αρχείο είναι κλειδωμένο για λόγους ασφαλείας.");

include_once 'DbHandler.php';
$db = new DbHandler();

// Πάρε το studentId από το URL (π.χ. debug_financials_detailed.php?id=108)
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId === 0) {
    die("Παρακαλώ δώστε ένα studentId στο URL. Παράδειγμα: debug_financials_detailed.php?id=XX");
}

// Απευθείας σύνδεση για το debug
$conn = new mysqli("jhouv.eu", "jhouvardas", "Jhouv@1957", "tutor");
mysqli_set_charset($conn, "utf8");

// 0. Επαλήθευση Μαθητή & Ωριαίας Χρέωσης
$checkStudent = $conn->query("SELECT name, lastName, paying, schoolYear FROM student WHERE studentId = $studentId");
$studentData = $checkStudent->fetch_assoc();

if (!$studentData) {
    die("<h2 style='color:red;'>ΣΦΑΛΜΑ: Ο μαθητής με ID $studentId δεν βρέθηκε!</h2>");
}

echo "<h2>Αναλυτικό Debug: " . $studentData['lastName'] . " " . $studentData['name'] . " (ID: $studentId)</h2>";
$displayRate = ((float)$studentData['paying'] > 1) ? $studentData['paying'] : 10;
echo "Έτος: " . $studentData['schoolYear'] . " | <b>Ωριαία χρέωση: " . number_format($displayRate, 2) . " €</b><br>";
echo "<hr>";

// 1. Ανάκτηση Δεδομένων
$sqlEntries = "SELECT date, duration, payment as amount, type FROM lesson WHERE studentId = $studentId ORDER BY date ASC";
$resEntries = $conn->query($sqlEntries);
$allEntries = $resEntries->fetch_all(MYSQLI_ASSOC);

$sqlAbsences = "SELECT date, reason as type FROM apousia WHERE studentId = $studentId ORDER BY date ASC";
$resAbsences = $conn->query($sqlAbsences);
$absences = $resAbsences->fetch_all(MYSQLI_ASSOC);

echo "<h3>1. Ανάλυση Εγγραφών (Βήμα-Βήμα)</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; font-family: monospace;'>";
echo "<tr style='background: #eee;'><th>Ημερομηνία</th><th>Περιγραφή</th><th>Διάρκεια</th><th>Κόστος (Διάρκεια x Rate)</th><th>Πληρωμή</th><th>Σωρευτικό Πληρωμών</th></tr>";

// Αν η τιμή στη βάση είναι 1 ή μικρότερη, χρησιμοποιούμε 10.0 ως προεπιλογή
$rate = ((float)$studentData['paying'] > 1) ? (float)$studentData['paying'] : 10.0;
$totalPaid = 0;
$totalCost = 0;
$lessonsOnly = [];

foreach ($allEntries as $entry) {
    $p = (float)$entry['amount'];
    $totalPaid += $p;

    $cost = 0;
    if ((float)$entry['duration'] > 0) {
        $cost = (float)$entry['duration'] * $rate;
        $totalCost += $cost;
        $entry['cost'] = $cost;
        $entry['entryType'] = 'lesson';
        $lessonsOnly[] = $entry;
    }

    echo "<tr>";
    echo "<td>" . $db->formatGreekDate($entry['date']) . "</td>";
    echo "<td>" . $entry['type'] . "</td>";
    echo "<td>" . $entry['duration'] . "</td>";
    echo "<td style='color:red'>" . ($cost > 0 ? number_format($cost, 2) . " €" : "-") . "</td>";
    echo "<td style='color:green'>" . ($p > 0 ? number_format($p, 2) . " €" : "-") . "</td>";
    echo "<td><b>" . number_format($totalPaid, 2) . " €</b></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h4>Σύνολα:</h4>";
echo "Συνολικό Κόστος Μαθημάτων: <b style='color:red'>" . number_format($totalCost, 2) . " €</b><br>";
echo "Συνολικές Πληρωμές: <b style='color:green'>" . number_format($totalPaid, 2) . " €</b><br>";
echo "Τρέχον Υπόλοιπο: <b>" . number_format($totalCost - $totalPaid, 2) . " €</b><br>";

echo "<hr><h3>2. Εφαρμογή FIFO (Ποια μαθήματα μένουν απλήρωτα)</h3>";
$runningTotalPaid = $totalPaid;
$unpaidLessons = [];

echo "<ul>";
foreach ($lessonsOnly as $lesson) {
    $cost = $lesson['cost'];
    if ($runningTotalPaid >= $cost) {
        echo "<li>Μάθημα " . $lesson['date'] . " ($cost €): <span style='color:green'>ΕΞΟΦΛΗΘΗΚΕ</span>. (Υπόλοιπο 'κουμπαρά' πληρωμών: " . number_format($runningTotalPaid - $cost, 2) . " €)</li>";
        $runningTotalPaid -= $cost;
    } else {
        if ($runningTotalPaid > 0) {
            echo "<li>Μάθημα " . $lesson['date'] . " ($cost €): <span style='color:orange'>ΜΕΡΙΚΩΣ ΠΛΗΡΩΜΕΝΟ</span>. Πληρώθηκαν τα " . number_format($runningTotalPaid, 2) . " €. Μένουν: <b>" . number_format($cost - $runningTotalPaid, 2) . " €</b></li>";
            $lesson['cost'] = $cost - $runningTotalPaid;
            $runningTotalPaid = 0;
        } else {
            echo "<li>Μάθημα " . $lesson['date'] . " ($cost €): <span style='color:red'>ΑΠΛΗΡΩΤΟ</span>.</li>";
        }
        $unpaidLessons[] = $lesson;
    }
}
echo "</ul>";

echo "<h3>3. Τελική Λίστα για την Καρτέλα Μαθητή</h3>";
$combined = array_merge($unpaidLessons, $absences);
usort($combined, function ($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 60%;'>";
echo "<tr style='background: #333; color: white;'><th>Ημερομηνία</th><th>Τύπος</th><th>Ποσό Εκκρεμότητας</th></tr>";
foreach ($combined as $item) {
    $isAbsence = !isset($item['entryType']);
    echo "<tr>";
    echo "<td>" . $db->formatGreekDate($item['date']) . "</td>";
    echo "<td>" . ($isAbsence ? "Απουσία (" . $item['type'] . ")" : "Μάθημα") . "</td>";
    echo "<td style='text-align:right; font-weight:bold; color:red;'>" . ($isAbsence ? "-" : number_format($item['cost'], 2) . " €") . "</td>";
    echo "</tr>";
}
if (empty($combined)) {
    echo "<tr><td colspan='3' style='text-align:center'>Όλα εξοφλημένα!</td></tr>";
}
echo "</table>";

echo "<p><br><a href='debug_financials_detailed.php?id=$studentId'>Ανανέωση (F5)</a></p>";

$conn->close();
