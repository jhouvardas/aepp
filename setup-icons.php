<?php
function makeIcon($size)
{
    $s   = $size / 512;
    $img = imagecreatetruecolor($size, $size);

    $bg       = imagecolorallocate($img, 22, 25, 28);
    $yellow   = imagecolorallocate($img, 255, 193, 7);
    $offwhite = imagecolorallocate($img, 222, 226, 230);
    $divider  = imagecolorallocate($img, 60, 65, 72);

    imagefilledrectangle($img, 0, 0, $size - 1, $size - 1, $bg);

    imagesetthickness($img, max(1, (int)(8 * $s)));
    imagerectangle($img, 4, 4, $size - 5, $size - 5, imagecolorallocate($img, 80, 65, 10));

    imagesetthickness($img, max(2, (int)(28 * $s)));
    imageline($img, (int)(188*$s), (int)(118*$s), (int)(42*$s),  (int)(252*$s), $yellow);
    imageline($img, (int)(42*$s),  (int)(252*$s), (int)(188*$s), (int)(386*$s), $yellow);
    imageline($img, (int)(230*$s), (int)(390*$s), (int)(282*$s), (int)(118*$s), $offwhite);
    imageline($img, (int)(324*$s), (int)(118*$s), (int)(470*$s), (int)(252*$s), $yellow);
    imageline($img, (int)(470*$s), (int)(252*$s), (int)(324*$s), (int)(386*$s), $yellow);

    imagesetthickness($img, 1);
    imageline($img, (int)(80*$s), (int)(426*$s), (int)(432*$s), (int)(426*$s), $divider);

    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
    ];
    $font = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) { $font = $path; break; }
    }
    if ($font) {
        $fs   = (int)(52 * $s);
        $text = 'ΑΕΠΠ';
        $bbox = imagettfbbox($fs, 0, $font, $text);
        $tx   = ($size - ($bbox[2] - $bbox[0])) / 2;
        imagettftext($img, $fs, 0, (int)$tx, (int)(488 * $s), $offwhite, $font, $text);
    } else {
        imagestring($img, 5, (int)(185 * $s), (int)(450 * $s), 'AEPP', $offwhite);
    }

    return $img;
}

$results = [];
foreach ([192, 512] as $size) {
    $path = __DIR__ . '/images/pwa-icon-' . $size . '.png';
    $img  = makeIcon($size);
    $ok   = imagepng($img, $path);
    imagedestroy($img);
    $results[$size] = $ok;
}

$allOk = $results[192] && $results[512];
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="utf-8"><title>PWA Icons Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white p-4">
<div class="container" style="max-width:500px">
    <h4 class="mb-4">Δημιουργία PWA Εικονιδίων</h4>
    <?php foreach ($results as $size => $ok): ?>
        <div class="alert <?php echo $ok ? 'alert-success' : 'alert-danger'; ?>">
            <strong><?php echo $size; ?>x<?php echo $size; ?>:</strong>
            <?php echo $ok ? 'Δημιουργήθηκε' : 'Αποτυχία — έλεγξε δικαιώματα φακέλου images/'; ?>
        </div>
    <?php endforeach; ?>
    <?php if ($allOk): ?>
        <p class="text-muted small">Μπορείς τώρα να διαγράψεις το setup-icons.php από τον server.</p>
        <div class="text-center mt-3">
            <img src="images/pwa-icon-192.png" class="border border-warning rounded me-2" style="width:96px">
            <img src="images/pwa-icon-512.png" class="border border-warning rounded" style="width:96px">
        </div>
    <?php endif; ?>
</div>
</body>
</html>
