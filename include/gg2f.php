<?php

// Tries to log in to GG2 Forums
// Returns session ID, or FALSE on failure
function gg2_login($username, $password) {
    // Prepares post data
    $fields = [
        'user' => $username,
        'passwrd' => $password,
        'cookieneverexp' => '1'
    ];
    $fields_str = '';
    foreach($fields as $key => $value) {
        $fields_str .= urlencode($key) . '=' . urlencode($value) . '&';
    }
    rtrim($fields_str, '&');
    
    $ch = curl_init();
    
    // The URL that the login form posts to
    curl_setopt($ch, CURLOPT_URL, 'http://ganggarrison.com/forums/?action=login2;wap2');
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_str);
    
    // Redirect checking
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    
    // Silence (don't want to output returned page html to end-user)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    $result = curl_exec($ch);
    
    // Find out what the resulting URL is
    $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    curl_close($ch);
    
    // Find PHPSESSID parameter in URL
    $needle = 'PHPSESSID=';
    if (($pos = strpos($url, $needle)) !== FALSE) {
        $pos2 = strpos($url, ';', $pos);
        // return PHPSESSID value
        return substr($url, $pos + strlen($needle), $pos2 - $pos - strlen($needle));
    } else {
        // If there's no PHPSESSID, it failed
        return FALSE;
    }
}

// Tries to grab someone's name from their profile
// Probably can't fail (?)
function gg2_get_profilename($PHPSESSID) {
    $ch = curl_init();

    // Profile page
    curl_setopt($ch, CURLOPT_URL, 'http://ganggarrison.com/forums/index.php?action=profile;PHPSESSID=' . $PHPSESSID);

    // Silence (don't want to output returned page html to end-user)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $result = curl_exec($ch);

    curl_close($ch);

    // Find profile name
    $needle = "\t\t\tSummary - ";
    $pos = strpos($result, $needle);
    $pos2 = strpos($result, "\n", $pos);
    $sc = substr($result, $pos + strlen($needle), $pos2 - $pos - strlen($needle));

    return $sc;
}

// Tries to change GG2 Forum signature
// Success is assumed (!)
function gg2_change_signature($PHPSESSID, $sig) {
    // First we need to make a GET request for the profile edit page
    
    $ch = curl_init();
    
    // Profile edit page
    curl_setopt($ch, CURLOPT_URL, 'http://ganggarrison.com/forums/index.php?action=profile;sa=forumProfile;PHPSESSID=' . $PHPSESSID);
    
    // Silence (don't want to output returned page html to end-user)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    $result = curl_exec($ch);
    
    curl_close($ch);
    
    // Find "sc" value
    $needle = '<input type="hidden" name="sc" value="';
    $pos = strpos($result, $needle);
    $pos2 = strpos($result, '" />', $pos);
    $sc = substr($result, $pos + strlen($needle), $pos2 - $pos - strlen($needle));
    
    // Find "userID" value
    $needle = '<input type="hidden" name="userID" value="';
    $pos = strpos($result, $needle);
    $pos2 = strpos($result, '" />', $pos);
    $userID = substr($result, $pos + strlen($needle), $pos2 - $pos - strlen($needle));

    // Now to perform changes

    // Prepares post data
    $fields = [
        'sc' => $sc,
        'userID' => $userID,
        'sa' => 'forumProfile',
        'signature' => $sig//,
        //'PHPSESSID' => $PHPSESSID
    ];
    $fields_str = '';
    foreach($fields as $key => $value) {
        $fields_str .= urlencode($key) . '=' . urlencode($value) . '&';
    }
    rtrim($fields_str, '&');
    
    $ch = curl_init();
    
    // The URL that the profile edit form POSTs to
    curl_setopt($ch, CURLOPT_URL, 'http://www.ganggarrison.com/forums/index.php?action=profile2;PHPSESSID=' . $PHPSESSID);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_str);
    
    // Silence (don't want to output returned page html to end-user)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    curl_exec($ch);
    
    curl_close($ch);
}
