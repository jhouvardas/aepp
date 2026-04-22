<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'DbHandler.php';
$db = new DbHandler();

// Πάρε το studentId από το URL (π.χ. debug_financials.php?id=12)
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId === 0) {
    die("Παρακαλώ δώστε ένα studentId στο URL. Παράδειγμα: debug_financials.php?id=ΧΧ");
}

// Σύνδεση για έλεγχο ονόματος
$conn = new mysqli("jhouv.eu", "jhouvardas", "Jhouv@1957", "tutor");
mysqli_set_charset($conn, "utf8");

// 0. Επαλήθευση Μαθητή
$checkStudent = $conn->query("SELECT name, lastName, paying, user FROM student WHERE studentId = $studentId");
$studentData = $checkStudent->fetch_assoc();

if (!$studentData) {
    echo "<h2 style='color:red;'>ΣΦΑΛΜΑ: Ο μαθητής με ID $studentId ΔΕΝ υπάρχει στον πίνακα student!</h2>";
    echo "<h4>Διαθέσιμα IDs που έχουν μαθήματα στη βάση:</h4>";
    $getValid = $conn->query("SELECT DISTINCT studentId FROM lesson LIMIT 10");
    while ($v = $getValid->fetch_assoc()) echo $v['studentId'] . ", ";
    die();
}

echo "<h2>Debug: " . $studentData['lastName'] . " " . $studentData['name'] . " (ID: $studentId)</h2>";
$displayRate = ((float)$studentData['paying'] > 1) ? $studentData['paying'] : 10;
echo "Έτος Μαθητή: " . $studentData['user'] . " | Ωριαία χρέωση: " . $displayRate . " €<br>";

$financials = $db->getStudentFinancials($studentId);

echo "<h3>1. Γενική Εικόνα</h3>";
echo "Συνολικό Κόστος: " . number_format($financials['balance'] + $financials['totalPaid'], 2) . " €<br>";
echo "Συνολικές Πληρωμές: " . number_format($financials['totalPaid'], 2) . " €<br>";
echo "<b>Τρέχον Υπόλοιπο: " . number_format($financials['balance'], 2) . " €</b><br>";

echo "<h3>2. Ανάλυση FIFO (Πώς ξοδεύτηκαν οι πληρωμές)</h3>";

// Επειδή η getStudentFinancials είναι private, θα αναπαράγουμε το trace εδώ
// ή θα μπορούσαμε να τροποποιήσουμε προσωρινά την κλάση. 
// Για ευκολία, ας δούμε τι επέστρεψε η μέθοδος.

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #eee;'><th>Ημερομηνία</th><th>Τύπος</th><th>Περιγραφή</th><th>Κόστος</th></tr>";

if (empty($financials['items'])) {
    echo "<tr><td colspan='4'>Δεν βρέθηκαν εκκρεμότητες (ή όλα είναι πληρωμένα).</td></tr>";
} else {
    foreach ($financials['items'] as $item) {
        $style = ($item['entryType'] === 'absence') ? "style='color: #777;'" : "style='font-weight: bold; color: red;'";
        echo "<tr $style>";
        echo "<td>" . $db->formatGreekDate($item['date']) . "</td>";
        echo "<td>" . $item['entryType'] . "</td>";
        echo "<td>" . ($item['type'] ?: '-') . "</td>";
        echo "<td>" . (isset($item['cost']) ? $item['cost'] . " €" : "0.00 €") . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<h3>3. Raw Data από τη Βάση (Πίνακας lesson)</h3>";
$sql = "SELECT date, duration, payment, type FROM lesson WHERE studentId = $studentId ORDER BY date ASC";
$res = $conn->query($sql);

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #ddd;'><th>Date</th><th>Duration</th><th>Payment (€)</th><th>Type</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $db->formatGreekDate($row['date']) . "</td>";
    echo "<td>" . $row['duration'] . "</td>";
    echo "<td>" . $row['payment'] . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>4. Έλεγχος Απουσιών (Πίνακας apousia)</h3>";
$sqlA = "SELECT * FROM apousia WHERE studentId = $studentId";
$resA = $conn->query($sqlA);
echo "Βρέθηκαν " . $resA->num_rows . " απουσίες.<br>";

echo "<hr><p><i>Σημείωση: Αν το Raw Data παραμένει κενό, ο μαθητής " . $studentData['name'] . " δεν έχει καμία καταχώρηση στο 'tutor' -> 'lesson'.</i></p>";

$conn->close();
