<?php
switch ($action) {
        case 'group_email_form':
            $groups = $db->getGroups($userYear);
            $reportFm->groupEmailForm($groups);
            break;

        case 'send_group_email':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $groupId = $_POST['group_id'];
                $subject = $_POST['subject'];
                $messageHtml = $_POST['message'];

                $students = $db->getStudentsByGroupId($groupId, $userYear);
                $groupInfo = null;

                $groups = $db->getGroups($userYear);
                foreach ($groups as $g) {
                    if ($g['id'] == $groupId) {
                        $groupInfo = $g;
                        break;
                    }
                }

                $groupName = $groupInfo ? $groupInfo['group_name'] : "Ομάδα";

                // New arrays for results
                $successfulRecipients = [];
                $failedRecipients = [];

                if (!empty($students)) {
                    foreach ($students as $student) {
                        if (!empty($student['email'])) {
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #007bff;'>Ενημέρωση Ομάδας: $groupName</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;'>
                                        $messageHtml
                                    </div>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                            $res = $db->sendSystemEmail($student['email'], $subject, $body);
                            if ($res === true) {
                                $successfulRecipients[] = [
                                    'name' => $student['name'] . ' ' . $student['lastName'],
                                    'email' => $student['email']
                                ];
                            } else {
                                $failedRecipients[] = [
                                    'name' => $student['name'] . ' ' . $student['lastName'],
                                    'email' => $student['email'],
                                    'error' => $res
                                ];
                            }
                        } else {
                            $failedRecipients[] = [
                                'name' => $student['name'] . ' ' . $student['lastName'],
                                'email' => 'Δεν υπάρχει email',
                                'error' => 'Δεν έχει δηλωθεί διεύθυνση email για αυτόν τον μαθητή.'
                            ];
                        }
                    }
                }
                if (count($successfulRecipients) > 0) {
                    $db->logGroupEmail($groupId, $subject, $messageHtml, $userYear);
                }

                $_SESSION['email_results'] = [
                    'groupName' => $groupName,
                    'subject' => $subject,
                    'message' => $messageHtml,
                    'successful' => $successfulRecipients,
                    'failed' => $failedRecipients
                ];

                echo "<script>window.location.href='index.php?action=group_email_results';</script>";
            }
            break;

        case 'mass_sms_form':
            $groups = $db->getGroups($userYear);
            $reportFm->massSmsForm($groups);
            break;

        case 'send_mass_sms':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $groupId = $_POST['group_id'];
                $message = $_POST['message'];
                $sendMethod = $_POST['send_method'] ?? 'mobile';

                $students = [];
                if ($groupId == 'all') {
                    $students = $db->getTutorStudents($userYear);
                } else {
                    $students = $db->getStudentsByGroupId($groupId, $userYear);
                }

                if ($sendMethod === 'mobile') {
                    $isPersonalized = (strpos($message, '[ΟΝΟΜΑ]') !== false);
                    $individualLinks = [];
                    $noPhone = [];
                    foreach ($students as $student) {
                        if (!empty($student['phone']) && $student['phone'] !== '-') {
                            $phone = preg_replace('/[^0-9]/', '', $student['phone']);
                            if (strlen($phone) == 10) {
                                $phone = "+30" . $phone;
                            }
                            if (!empty($phone)) {
                                $personalizedMsg = $isPersonalized
                                    ? str_replace('[ΟΝΟΜΑ]', $student['name'], $message)
                                    : $message;
                                $individualLinks[] = [
                                    'name' => $student['name'] . ' ' . $student['lastName'],
                                    'link' => "sms:" . $phone . "?body=" . rawurlencode($personalizedMsg)
                                ];
                            }
                        } else {
                            $noPhone[] = $student['name'] . ' ' . $student['lastName'];
                        }
                    }

                    $html = "<div class='container mt-4'>
                                <div class='card shadow-sm border-success'>
                                    <div class='card-header bg-success text-white'>
                                        <h4 class='mb-0'><i class='fa fa-mobile-phone'></i> Αποστολή SMS από το Κινητό</h4>
                                    </div>
                                    <div class='card-body'>
                                        <p class='text-muted small mb-3'><i class='fa fa-info-circle'></i> Πατήστε το κουμπί κάθε μαθητή — θα ανοίξει το Google Messages με έτοιμο το μήνυμα. Στείλτε και επιστρέψτε για τον επόμενο.</p>
                                        <div class='list-group mb-3'>";

                    foreach ($individualLinks as $item) {
                        $html .= "<a href='" . htmlspecialchars($item['link']) . "' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                                    <span><i class='fa fa-user'></i> " . htmlspecialchars($item['name']) . "</span>
                                    <span class='badge bg-success'><i class='fa fa-paper-plane'></i> Αποστολή</span>
                                  </a>";
                    }

                    if (empty($individualLinks)) {
                        $html .= "<div class='alert alert-warning mb-0'>Κανένας μαθητής δεν έχει καταχωρημένο κινητό τηλέφωνο.</div>";
                    }

                    $html .= "</div>";

                    if (!empty($noPhone)) {
                        $html .= "<div class='alert alert-warning small mt-2'><i class='fa fa-exclamation-triangle'></i> Χωρίς τηλέφωνο: " . implode(', ', array_map('htmlspecialchars', $noPhone)) . "</div>";
                    }

                    $html .= "      <a href='index.php?action=mass_sms_form' class='btn btn-secondary w-100 mt-2'>Επιστροφή</a>
                                    </div>
                                </div>
                            </div>";

                    echo $html;
                } else {
                    $successCount = 0;
                    $failCount = 0;

                    foreach ($students as $student) {
                        if (!empty($student['phone']) && $student['phone'] !== '-') {
                            // Καθαρισμός τηλεφώνου (κρατάμε μόνο αριθμούς)
                            $phone = preg_replace('/[^0-9]/', '', $student['phone']);
                            if (strlen($phone) == 10) {
                                $phone = "30" . $phone; // Προσθήκη κωδικού Ελλάδας (απαιτείται από τα περισσότερα API)
                            }

                            $finalMessage = str_replace('[ΟΝΟΜΑ]', $student['name'], $message);
                            if ($db->sendSMS($phone, $finalMessage) === true) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        }
                    }
                    echo "<script>alert('Ολοκληρώθηκε! Επιτυχείς αποστολές: $successCount, Δεν είχαν δηλωμένο κινητό (ή σφάλμα): $failCount'); window.location.href='index.php?action=mass_sms_form';</script>";
                }
            }
            break;

        case 'group_email_history':
            $history = $db->getGroupEmailHistory($userYear);
            $reportFm->showGroupEmailHistory($history);
            break;

        case 'group_email_results':
            if (isset($_SESSION['email_results'])) {
                $results = $_SESSION['email_results'];
                $reportFm->showGroupEmailResults(
                    $results['groupName'],
                    $results['subject'],
                    $results['message'],
                    $results['successful'],
                    $results['failed']
                );
                unset($_SESSION['email_results']);
            } else {
                echo "<script>window.location.href='index.php?action=group_email_form';</script>";
            }
            break;

        case 'retry_failed_emails':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $subject = $_POST['subject'];
                $messageHtml = $_POST['message'];
                $failedRecipientsJson = $_POST['failed_recipients'];
                $recipientsToRetry = json_decode(htmlspecialchars_decode($failedRecipientsJson), true);

                $successfulRecipients = [];
                $failedRecipients = [];
                $groupName = $_POST['group_name'];

                if (!empty($recipientsToRetry)) {
                    foreach ($recipientsToRetry as $student) {
                        if (!empty($student['email']) && $student['email'] !== 'Δεν υπάρχει email') {
                            $body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                    <h2 style='color: #007bff;'>Ενημέρωση Ομάδας: $groupName</h2>
                                    <p>Γεια σου <b>{$student['name']}</b>,</p>
                                    <div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;'>
                                        $messageHtml
                                    </div>
                                    <p>Καλή συνέχεια!</p>
                                </div>";
                            $res = $db->sendSystemEmail($student['email'], $subject, $body);
                            if ($res === true) {
                                $successfulRecipients[] = $student;
                            } else {
                                $failedRecipients[] = ['name' => $student['name'], 'email' => $student['email'], 'error' => $res];
                            }
                        } else {
                            $failedRecipients[] = $student; // Keep them in the failed list
                        }
                    }
                }

                $_SESSION['email_results'] = [
                    'groupName' => $groupName . " (Επανάληψη)",
                    'subject' => $subject,
                    'message' => $messageHtml,
                    'successful' => $successfulRecipients,
                    'failed' => $failedRecipients
                ];

                echo "<script>window.location.href='index.php?action=group_email_results';</script>";
            }
            break;
}
