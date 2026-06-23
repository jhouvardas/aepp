<?php
switch ($action) {
        case 'export_google_contacts':
            $students = $db->getTutorStudents($userYear);

            // Εξαγωγή του έτους από το string (π.χ. από "jhouv2027" βγάζει "2027")
            $yearDigits = preg_replace('/[^0-9]/', '', $userYear);
            $groupName = !empty($yearDigits) ? "Μαθητές " . $yearDigits : "Μαθητές " . date('Y');

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="Google_Contacts_' . $yearDigits . '.csv"');

            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // Προσθήκη UTF-8 BOM για σωστή αναγνώριση Ελληνικών

            // Επικεφαλίδες που αναγνωρίζει το Google Contacts
            fputcsv($output, ['First Name', 'Last Name', 'E-mail 1 - Type', 'E-mail 1 - Value', 'Phone 1 - Type', 'Phone 1 - Value', 'Birthday', 'Organization 1 - Title', 'Group Membership']);

            foreach ($students as $student) {
                if ($student['studentId'] == 999999) continue; // Παράλειψη του δοκιμαστικού μαθητή

                $birthday = ($student['birthday'] != '0000-00-00' && $student['birthday'] != '-') ? $student['birthday'] : '';

                fputcsv($output, [
                    $student['name'],
                    $student['lastName'],
                    '*',
                    $student['email'],
                    'Mobile',
                    $student['phone'],
                    $birthday,
                    $student['school'],
                    $groupName
                ]);
            }
            fclose($output);
            exit();
            break;
}
