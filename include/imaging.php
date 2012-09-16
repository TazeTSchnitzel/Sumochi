<?php

// Renders a profile, durp.
function render_profile($achievements) {
    $im = imageCreateFromPNG('../media/bg.png');
    imageSaveAlpha($im, 1);
    
    $white = imageColorAllocate($im, 255, 255, 255);
    $red = imageColorAllocate($im, 255, 0, 0);
    
    imageString($im, 2, 4, 4, 'sumochi', $white);
    
    $count = count($achievements);
    imageString($im, 4, 4, 20, "Most Recent Achievements ($count total)", $white);
    
    if ($count === 0) {
        imageString($im, 3, 8, 36, 'none', $red);
    } else {
        $y = 36;
        // limit to six
        $achievements = array_slice($achievements, 0, 6);
        foreach ($achievements as $obj) {
            if (property_exists($obj, 'icon')) {
                $icon = imageCreateFromPNG('../media/icons/' . basename($obj->icon));
                imageSaveAlpha($icon, 1);
                imageString($im, 3, 16, $y, $obj->name, $white);
                
                imageCopy($im, $icon, 2, $y, 0, 0, imageSX($icon), imageSY($icon));
                
                imageDestroy($icon);
            } else {
                imageString($im, 3, 4, $y, $obj->name, $white);
            }
            $y += 14;
        }
    }
    
    header('Content-type: image/png');
    imagePNG($im);
    imageDestroy($im);
}
