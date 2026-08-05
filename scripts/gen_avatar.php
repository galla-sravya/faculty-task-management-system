<?php
$size = 200;
$img = imagecreatetruecolor($size, $size);
$bg = imagecolorallocate($img, 18, 39, 90);
imagefill($img, 0, 0, $bg);
$white = imagecolorallocate($img, 255, 255, 255);
imagefilledellipse($img, 100, 75, 60, 60, $white);
imagefilledellipse($img, 100, 180, 100, 80, $white);
imagepng($img, __DIR__ . '/../public/storage/faculty/default-avatar.png');
imagedestroy($img);
echo "Default avatar created.\n";
