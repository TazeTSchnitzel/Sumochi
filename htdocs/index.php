<?php

date_default_timezone_set('UTC');

require_once '../include/gg2f.php';
require_once '../include/imaging.php';
require_once '../include/user.php';
require_once '../include/misc.php';
require_once '../include/secrets.php';


$htmlhead = <<<EOT
<!doctype html>
<meta charset=utf-8>
<link rel=stylesheet href=style.css>
<link rel="shortcut icon" href=/favicon.ico>

<div class=main>

EOT;

$htmlfoot = <<<EOT
</div>

EOT;

$loginform = <<<EOT
<form method=POST action=/>
    <h1><img src=/favicon.ico alt=""> Welcome to sumochi</h1>
    <p>Sumochi uses your existing GG2 forum details.<p>
    <p>Logging in below will replace your forum signature with an achievements list and create (or update) your sumochi account. (If you remove the list, just log in again - you won't lose your achievements)</p>
    <p>Once you've logged in for the first time, which creates your sumochi account, you will be able to use your forum details to earn achievements on approved GG2 servers.</p>
    <input type=hidden name=p value=dologin>
    Username: <input type=text name=username><br>
    Password: <input type=password name=password><br>
    <input type=submit>
</form>

EOT;

switch (isset($_REQUEST['p']) ? $_REQUEST['p'] : '') {
    case 'api_give_achievement':
        if (user_check_logintoken($_GET['user'], $_GET['token'])) {
            if (user_validate_key($_GET['key'])) {
                $result = user_give_achievement($_GET['user'], $_GET['a_id'], $_GET['key']);
                if ($result === TRUE) {
                    echo 'SUCCESS';
                } else if ($result === FALSE) {
                    echo 'ERROR already_has_achievement';
                } else {
                    echo 'ERROR unknown_error';
                }
            } else {
                echo 'ERROR unknown_key';
            }
        } else {
            echo 'ERROR invalid_token';
        }
    break;
    case 'api_has_achievement':
        if (user_check_logintoken($_GET['user'], $_GET['token'])) {
            if (user_validate_key($_GET['key'])) {
                $result = user_has_achievement($_GET['user'], $_GET['a_id']);
                if ($result === TRUE) {
                    echo 'SUCCESS TRUE';
                } else if ($result === FALSE) {
                    echo 'SUCCESS FALSE';
                } else {
                    echo 'ERROR unknown_error';
                }
            } else {
                echo 'ERROR unknown_key';
            }
        } else {
            echo 'ERROR invalid_token';
        }    
    break;
    case 'api_has_achievements':
        if (user_check_logintoken($_GET['user'], $_GET['token'])) {
            if (user_validate_key($_GET['key'])) {
                $result = user_has_achievements($_GET['user'], explode(',', $_GET['a_ids']));
                if ($result) {
                    echo 'SUCCESS ';
                    $results = [];
                    foreach ($result as $i) {
                        if ($i === TRUE) {
                            $results[] = 'TRUE';
                        } else if ($i === FALSE) {
                            $results[] = 'FALSE';
                        }
                    }
                    echo implode(',', $results);
                } else {
                    echo 'ERROR unknown_error';
                }
            } else {
                echo 'ERROR unknown_key';
            }
        } else {
            echo 'ERROR invalid_token';
        }
    break;
    case 'api_login':
        if (($PHPSESSID = gg2_login($_GET['user'], $_GET['password'])) !== FALSE) {
            if (user_exists($_GET['user'])) {
                $token = user_gen_logintoken($_GET['user'], $PHPSESSID);
                echo "SUCCESS $token";
            } else {
                echo 'ERROR no_sumochi_user';
            }
        } else {
            echo 'ERROR gg2_login_failed';
        }
    break;
    case 'display':
        $achievements = user_get_achievements($_GET['user']);
        $displayname = user_get_displayname($_GET['user']);
        
        // output signature image
        if ($achievements !== NULL) {
            if ($displayname !== NULL) {
                render_profile($displayname, $achievements);
            } else {
                render_profile('[profile display name unknown]', $achievements);
            }
        } else {
            header('Location: error.png');
            die();
        }
    break;
    case 'list':
        $achievements = user_get_achievements($_GET['user']);
        $displayname = user_get_displayname($_GET['user']);
        
        if ($achievements !== NULL) {
            echo $htmlhead;
            if ($displayname !== NULL) {
                echo "<h1><img src=/favicon.ico alt=\"\"> sumochi - " . htmlspecialchars($displayname) . "</h1>\n";
            } else {
                echo "<h1><img src=/favicon.ico alt=\"\"> sumochi - [profile display name unknown]</h1>\n";
            }
            
            $count = count($achievements);
            echo "<h2>Achievements ($count total)</h2>\n";
            
            if ($count === 0) {
                echo "<span class=none>none</span>";
            } else {
                echo "<ul class=achievements>\n";
                foreach ($achievements as $obj) {
                    if (user_validate_achievement($obj->key, $obj->id)) {
                        echo "<li>\n";
                        $prepend = '';
                    } else {
                        echo "<li class=invalid>\n";
                        $prepend = '[INVALID] ';
                    }
                    $data = $achievement_types[$obj->id];
                    if (array_key_exists('icon', $data)) {
                        $base64URL = 'data:image/png;base64,' . base64_encode(file_get_contents('../media/icons/' . $data['icon']));
                        echo "<img src=\"$base64URL\" alt=\"icon\">\n";
                    }
                    echo '<span class=game>' . htmlspecialchars($data['game']) . '</span> ';
                    echo $prepend . htmlspecialchars($data['name']) . "\n";
                    echo '(<time>' . date('Y-m-d', $obj->timestamp) ."</time>)\n";
                    echo '<p>' . htmlspecialchars($data['description']) . '</p>' . "\n";
                    echo "</li>\n";
                }
                echo "</ul>\n";
            }
            echo $htmlfoot;
        } else {
            echo $htmlhead;
            echo "<h1>Error</h1>\n";
            echo $htmlfoot;
        }
    break;
    case 'dologin':
        echo $htmlhead;
        $username = $_POST['username'];
        $password = $_POST['password'];
        if (($PHPSESSID = gg2_login($username, $password)) !== FALSE) {
            echo "Successful login!<br>";

            // get profile name
            $profilename = gg2_get_profilename($PHPSESSID);
            
            // create user file if non-existant
            if (user_create_or_update($username, $profilename)) {
                echo "Created sumochi account.<br>";
            } else {
                echo "Updated existing sumochi account.<br>";
            }
            
            // generate (self-referential) signature image URL
            $img_url = where_am_i() . '?p=display&user=' . urlencode($username);
            
            // generate (self-referential) profile URL
            $url = where_am_i() . '?p=list&user=' . urlencode($username);
            
            // change signature
            $sig = '[url='.$url.'][img]'.$img_url.'[/img][/url]';
            gg2_change_signature($PHPSESSID, $sig);
            
            echo "Signature replaced with achievements list.<br>";
        } else {
            echo "Failed login!<br>";
        }
    break;
    case 'login':
    case '':
        echo $htmlhead;
        echo $loginform;
        echo $htmlfoot;
    break;
    default:
        header("HTTP/1.0 404 Not Found");
    break;
}
echo "\n";
