<?php
header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800');

$size = isset($_GET['size']) && in_array((int)$_GET['size'], [192, 512]) ? (int)$_GET['size'] : 512;
$s    = $size / 512; // scale factor

$img      = imagecreatetruecolor($size, $size);
$bg       = imagecolorallocate($img, 22, 25, 28);
$yellow   = imagecolorallocate($img, 255, 193, 7);
$offwhite = imagecolorallocate($img, 222, 226, 230);
$divider  = imagecolorallocate($img, 60, 65, 72);

imagefilledrectangle($img, 0, 0, $size - 1, $size - 1, $bg);

// Λεπτό περίγραμμα
imagesetthickness($img, max(1, (int)(8 * $s)));
imagerectangle($img, 4, 4, $size - 5, $size - 5, imagecolorallocate($img, 80, 65, 10));

// Σύμβολο </>
imagesetthickness($img, max(2, (int)(28 * $s)));

imageline($img, (int)(188*$s), (int)(118*$s), (int)(42*$s),  (int)(252*$s), $yellow);
imageline($img, (int)(42*$s),  (int)(252*$s), (int)(188*$s), (int)(386*$s), $yellow);
imageline($img, (int)(230*$s), (int)(390*$s), (int)(282*$s), (int)(118*$s), $offwhite);
imageline($img, (int)(324*$s), (int)(118*$s), (int)(470*$s), (int)(252*$s), $yellow);
imageline($img, (int)(470*$s), (int)(252*$s), (int)(324*$s), (int)(386*$s), $yellow);

// Γραμμή διαχωρισμού
imagesetthickness($img, max(1, (int)(2 * $s)));
imageline($img, (int)(80*$s), (int)(426*$s), (int)(432*$s), (int)(426*$s), $divider);

// ΑΕΠΠ με TTF
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
    $fontSize = (int)(52 * $s);
    $text     = 'ΑΕΠΠ';
    $bbox     = imagettfbbox($fontSize, 0, $font, $text);
    $tx       = ($size - ($bbox[2] - $bbox[0])) / 2;
    imagettftext($img, $fontSize, 0, (int)$tx, (int)(488 * $s), $offwhite, $font, $text);
} else {
    imagesetthickness($img, 1);
    imagestring($img, 5, (int)(185 * $s), (int)(450 * $s), 'AEPP', $offwhite);
}

imagepng($img);
imagedestroy($img);
