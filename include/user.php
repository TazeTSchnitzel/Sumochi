<?php

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

// gets list of user achievements
function user_get_achievements($username) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        return $obj->achievements;
    } else {
        return NULL;
    }
}

// generates a user login token (and stores it)
function user_gen_logintoken($username, $PHPSESSID, $server_key) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        $token = hash('sha256', $username + $PHPSESSID + $server_key);
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

// gives user achievement
function user_give_achievement($username, $a_id, $a_name, $a_key, $a_icon=NULL) {
    $obj = user_get_object($username);
    if ($obj !== NULL) {
        foreach ($obj->achievements as $achievement) {
            if ($achievement->id == $a_id) {
                return FALSE;
            }
        }
        $achievement = [
            'name' => $a_name,
            'key' => $a_key,
            'id' => $a_id,
            'timestamp' => time()
        ];
        if ($a_icon !== NULL) {
            $achievement['icon'] = $a_icon;
        }
        array_unshift($obj->achievements, $achievement);
        user_set_object($username, $obj);
        return TRUE;
    } else {
        return NULL;
    }
}

// creates user
function user_create($username) {
    $fn = user_filename($username);
    if (!file_exists($fn)) {
        file_put_contents($fn, json_encode([
            'achievements' => [],
            'lastLoginToken' => NULL 
        ]));
    }
}
