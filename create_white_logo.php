<?php
$source = imagecreatefrompng('public/Techub-Logo.png');
imagealphablending($source, false);
imagesavealpha($source, true);
$width = imagesx($source);
$height = imagesy($source);

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $color = imagecolorat($source, $x, $y);
        $alpha = ($color >> 24) & 0xFF;
        if ($alpha < 127) {
            $white = imagecolorallocatealpha($source, 255, 255, 255, $alpha);
            imagesetpixel($source, $x, $y, $white);
        }
    }
}
if (!is_dir('public/assets/images')) {
    mkdir('public/assets/images', 0777, true);
}
imagepng($source, 'public/assets/images/logo_putih.png');
imagedestroy($source);
echo "Success";
