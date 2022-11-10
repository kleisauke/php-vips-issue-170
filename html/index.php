<?php

use Jcupitt\Vips\FFI;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use Jcupitt\Vips\Utils;

require_once(__DIR__.'/../vendor/autoload.php');

Utils::debugLog('Begin thumbnail', []);

$image = Image::thumbnail(
    __DIR__.'/photo.jpg', 600,
    ['height' => 10_000_000, 'export-profile' => Interpretation::SRGB]
)->writeToBuffer('.jpg', ['Q' => 75, 'strip' => TRUE, 'optimize_coding' => TRUE, 'profile'=>Interpretation::SRGB]);

Utils::debugLog('Thumbnail done', []);

echo '<img width=600 height=452 style="border:1px solid black" alt="Photo" src="data:image/jpg;base64,'.base64_encode($image).'">';

$shutdown_behaviour = $_GET['shutdown_behaviour'] ?? 'vips_shutdown';
if ($shutdown_behaviour === 'vips_shutdown') {
    Utils::debugLog('Begin FFI::shutdown', []);

    FFI::shutdown();

    Utils::debugLog('FFI::shutdown - done', []);
} elseif ($shutdown_behaviour === 'vips_thread_shutdown') {
    Utils::debugLog('Begin FFI::thread_shutdown', []);

    FFI::vips()->vips_thread_shutdown();

    Utils::debugLog('FFI::thread_shutdown - done', []);
} elseif ($shutdown_behaviour !== 'no_shutdown') {
    throw new \InvalidArgumentException('Bad argument to ?shutdown_behaviour');
}
