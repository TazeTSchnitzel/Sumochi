<?php

require_once 'secrets.php';

$achievement_types = json_decode(file_get_contents('../include/achievements.json'), true)['achievements'];

function user_filename($username) {
    return '../users/' . hash('sha256', $username);
}

function user_get_object($username) {
    $fn = user_filename($username);
    if (file_exists($fn)) {
        return json_decode(file_get_contents($fn));
    } else {
        return NULL;
    }
}

// checks user existence
function user_exists($username) {
    return (user_get_object($username) !== NULL);
}

function user_set_object($username, $obj) {
    $fn = user_filename($username);
    file_put_contents($fn, json_encode($obj));
}

// gets user display name
function user_get_displayname($username) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        if (property_exists($obj, 'displayName')) {
            return $obj->displayName;
        }
    }
    return NULL;
}

// gets list of user achievements
function user_get_achievements($username) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        return $obj->achievements;
    } else {
        return NULL;
    }
}

// checks if a key is valid
function user_validate_key($server_key) {
    global $permissable_keys;
    
    // valid key
    if (array_key_exists($server_key, $permissable_keys)) {
        return TRUE;
    }
    return FALSE;
}

// checks if an achievement is valid
function user_validate_achievement($server_key, $achievement_id) {
    global $permissable_keys, $achievement_types;

    // existent achievement ID
    if (!array_key_exists($achievement_id, $achievement_types)) {
        return FALSE;
    }
    
    // valid key
    if (array_key_exists($server_key, $permissable_keys)) {
        // valid key for achievement
        $ids = $permissable_keys[$server_key];
        if (array_search($achievement_id, $ids) !== FALSE) {
            return TRUE;
        }
    }
    return FALSE;
}

// generates a user login token (and stores it)
function user_gen_logintoken($username, $PHPSESSID) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        $token = hash('sha256', $username + $PHPSESSID);
        $obj->lastLoginToken = $token;
        user_set_object($username, $obj);
        return $token;
    } else {
        return NULL;
    }
}

// checks a user's login token
function user_check_logintoken($username, $token) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        if ($obj->lastLoginToken === $token) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        return NULL;
    }
}

// checks if a user has an achievement
function user_has_achievement($username, $a_id) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        foreach ($obj->achievements as $achievement) {
            if ($achievement->id == $a_id && user_validate_achievement($achievement->key, $achievement->id)) {
                return TRUE;
            }
        }
        return FALSE;
    } else {
        return NULL;
    }
}

// checks if a user has some achievements
function user_has_achievements($username, $a_ids) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        $results = [];
        foreach ($a_ids as $a_id) {
            $result = FALSE;
            foreach ($obj->achievements as $achievement) {
                if ($achievement->id == $a_id && user_validate_achievement($achievement->key, $achievement->id)) {
                    $result = TRUE;
                }
            }
            $results[] = $result;
        }
        return $results;
    } else {
        return NULL;
    }
}

// gives user achievement
function user_give_achievement($username, $a_id, $a_key) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        foreach ($obj->achievements as $achievement) {
            if ($achievement->id == $a_id) {
                return FALSE;
            }
        }
        $achievement = [
            'key' => $a_key,
            'id' => $a_id,
            'timestamp' => time()
        ];
        array_unshift($obj->achievements, $achievement);
        user_set_object($username, $obj);
        return TRUE;
    } else {
        return NULL;
    }
}

// creates user
function user_create_or_update($username, $displayname) {
    $obj = user_get_object($username);
    if ($obj === NULL) {
        $fn = user_filename($username);
        file_put_contents(user_filename($username), json_encode([
            'achievements' => [],
            'lastLoginToken' => NULL,
            'displayName' => $displayname
        ]));
        return true;
    } else {
        $obj->displayName = $displayname;
        user_set_object($username, $obj);
        return false;
    }
}
