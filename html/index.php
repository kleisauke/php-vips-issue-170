<?php

use Jcupitt\Vips\FFI;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;

require_once(__DIR__.'/../vendor/autoload.php');

$image = Image::thumbnail(
    __DIR__.'/photo.jpg', 600,
    ['height' => 10_000_000, 'export-profile' => Interpretation::SRGB]
)->writeToBuffer('.jpg', ['Q' => 75, 'strip' => TRUE, 'optimize_coding' => TRUE, 'profile'=>Interpretation::SRGB]);

echo '<img width=600 height=452 style="border:1px solid black" alt="Photo" src="data:image/jpg;base64,'.base64_encode($image).'">';

FFI::shutdown();
