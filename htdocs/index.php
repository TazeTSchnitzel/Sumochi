<?php

require_once '../include/gg2f.php';
require_once '../include/imaging.php';
require_once '../include/user.php';
require_once '../include/misc.php';
require_once '../include/secrets.php';


$htmlhead = <<<EOT
<!doctype html>
<meta charset=utf-8>
<link rel=stylesheet href=style.css>
<div class=main>

EOT;

$htmlfoot = <<<EOT
</div>

EOT;

$loginform = <<<EOT
<form method=POST action=/>
    <h1>Welcome to sumochi</h1>
    <p>Sumochi uses your existing GG2 forum details. Log in with them below to add an achievements list to your forum signature. (If you remove it, just log in again - you won't lose your achievements)</p>
    <p>Once you've logged in for the first time, you will be able to use your forum details to earn achievements on approved GG2 servers.</p>
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
                if (isset($_GET['a_icon'])) {
                    $result = user_give_achievement($_GET['user'], $_GET['a_id'], $_GET['a_name'], $_GET['key'], $_GET['a_icon']);
                } else {
                    $result = user_give_achievement($_GET['user'], $_GET['a_id'], $_GET['a_name'], $_GET['key']);
                }
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
        
        // output signature image
        if ($achievements !== NULL) {
            render_profile($_GET['user'], $achievements);
        } else {
            header('Location: error.png');
            die();
        }
    break;
    case 'list':
        $achievements = user_get_achievements($_GET['user']);
        
        if ($achievements !== NULL) {
            echo $htmlhead;
            echo "<h1>sumochi - " . htmlspecialchars($_GET['user']) . "</h1>\n";
            
            $count = count($achievements);
            echo "<h2>Achievements ($count total)</h2>\n";
            
            if ($count === 0) {
                echo "<span class=none>none</span>";
                imageString($im, 3, 8, 36, 'none', $red);
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
                    if (property_exists($obj, 'icon')) {
                        $base64URL = 'data:image/png;base64,' . base64_encode(file_get_contents('../media/icons/' . basename($obj->icon)));
                        echo "<img src=\"$base64URL\" alt=\"icon\">\n";
                    }
                    echo $prepend . htmlspecialchars($obj->name) . "\n";
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
            
            // create user file if non-existant
            user_create($username);
            
            // generate (self-referential) signature image URL
            $img_url = where_am_i() . '?p=display&user=' . urlencode($username);
            
            // generate (self-referential) profile URL
            $url = where_am_i() . '?p=list&user=' . urlencode($username);
            
            // change signature
            $sig = '[url='.$url.'][img]'.$img_url.'[/img][/url]';
            gg2_change_signature($PHPSESSID, $sig);
            
            echo "Achievements list now in signature.<br>";
        } else {
            echo "Failed login!<br>";
        }
    break;
    case 'login':
    case '':
        echo $htmlhead;
        echo $loginform;
        echo $htmlfoot;
    default:
        header("HTTP/1.0 404 Not Found");
    break;
}
echo "\n";
