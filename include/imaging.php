<?php

require_once 'user.php';

// Renders a profile, durp.
function render_profile($displayname, $achievements) {
    global $achievement_types;

    $im = imageCreateFromPNG('../media/bg.png');
    imageSaveAlpha($im, 1);
    
    $white = imageColorAllocate($im, 255, 255, 255);
    $red = imageColorAllocate($im, 255, 0, 0);
    $orange = imageColorAllocate($im, 255, 127, 0);

    $logo = imageCreateFromPNG('../media/logo.png');
    imageSaveAlpha($logo, 1);

    imageCopy($im, $logo, 2, 4, 0, 0, imageSX($logo), imageSY($logo));
    imageString($im, 2, 22, 4, "sumochi - $displayname", $white);
    
    $count = count($achievements);
    imageString($im, 4, 4, 20, "Most Recent Achievements ($count total)", $white);
    
    if ($count === 0) {
        imageString($im, 3, 8, 36, 'none', $red);
    } else {
        $y = 36;
        // limit to six
        $achievements = array_slice($achievements, 0, 6);
        foreach ($achievements as $obj) {
            if (user_validate_achievement($obj->key, $obj->id)) {
                $prepend = '';
                $color = $white;
            } else {
                $prepend = '[INVALID] ';
                $color = $red;
            }
            $data = $achievement_types[$obj->id];

            $game = $data['game'];
            $gameWidth = imageFontWidth(3) * (strlen($game) + 1);

            if (array_key_exists('icon', $data)) {
                $icon = imageCreateFromPNG('../media/icons/' . $data['icon']);
                imageSaveAlpha($icon, 1);

                imageString($im, 3, 16, $y, $game, $orange);
                imageString($im, 3, $gameWidth + 16, $y, $prepend . $data['name'], $color);
                
                imageCopy($im, $icon, 2, $y, 0, 0, imageSX($icon), imageSY($icon));
                
                imageDestroy($icon);
            } else {
                imageString($im, 3, 4, $y, $game, $orange);
                imageString($im, 3, $gameWidth + 4, $y, $prepend . $data['name'], $color);
            }
            $y += 14;
        }
    }

    header('Content-type: image/png');
    imagePNG($im);
    imageDestroy($im);
}
