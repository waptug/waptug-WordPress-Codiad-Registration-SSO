<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////////
// Paths
//////////////////////////////////////////////////////////////////////
$path = $_POST['path'];

$rel = str_replace('/plugin/RegistrationApi/process.php', '', $_SERVER['REQUEST_URI']);

$workspace = $path . "/workspace";
$users = $path . "/data/users.php";
$projects = $path . "/data/projects.php";
$active = $path . "/data/active.php";
$config = $path . "/config.php";

//////////////////////////////////////////////////////////////////////
// Functions
//////////////////////////////////////////////////////////////////////

function saveFile($file, $data)
{
    $write = fopen($file, 'w') or die("can't open file");
    fwrite($write, $data);
    fclose($write);
}

function getJSON($file)
{
    $json = file_get_contents($file);
    $json = str_replace(["\n\r", "\r", "\n"], "", $json);
    $json = str_replace("|*/?>","",str_replace("<?php/*|","",$json));
    $json = json_decode($json,true);
    return $json;
}

function saveJSON($file, $data)
{
    $data = "<?php/*|\r\n" . json_encode($data) . "\r\n|*/?>";
    saveFile($file, $data);
}

function encryptPassword($p)
{
    return sha1(md5($p));
}

function cleanUsername($username)
{
    return preg_replace('#[^A-Za-z0-9'.preg_quote('-_@. ').']#', '', $username);
}

function isAbsPath($path)
{
    return $path[0] === '/';
}

function cleanPath($path)
{

    // prevent Poison Null Byte injections
    $path = str_replace(chr(0), '', $path);

    // prevent go out of the workspace
    while (strpos($path, '../') !== false) {
        $path = str_replace('../', '', $path);
    }

    return $path;
}

    //////////////////////////////////////////////////////////////////
    // Get POST responses
    //////////////////////////////////////////////////////////////////

    $username = cleanUsername($_POST['username']);
    $password = encryptPassword($_POST['password']);
    $project_name = $_POST['project_name'];
    if (isset($_POST['project_path'])) {
        $project_path = $_POST['project_path'];
    } else {
        $project_path = $project_name;
    }
    $timezone = $_POST['timezone'];

    //////////////////////////////////////////////////////////////////
    // Create Projects files
    //////////////////////////////////////////////////////////////////

    $project_path = cleanPath($project_path);
    //echo "path is : ".$workspace; die;
    ///var/www/html/Codiad
    if (!isAbsPath($project_path)) {
        $project_path = str_replace(" ", "_", preg_replace('/[^\w-\.]/', '', $project_path));
        mkdir($workspace . "/" . $project_path);
    } else {
        $project_path = cleanPath($project_path);
        if (substr($project_path, -1) == '/') {
            $project_path = substr($project_path, 0, strlen($project_path)-1);
        }
        if (!file_exists($project_path)) {
            if (!mkdir($project_path.'/', 0755, true)) {
                die("Unable to create Absolute Path");
            }
        } else {
            if (!is_writable($project_path) || !is_readable($project_path)) {
                die("No Read/Write Permission");
            }
        }
    }

    $project_data = getJSON($projects);
    $project_data[] = array("name"=>$project_name,"path"=>$project_path);

    saveJSON($projects, $project_data);

    
    $user_data = getJSON($users);
    $user_data[] = array("username"=>$username,"password"=>$password,"project"=>$project_path);

    saveJSON($users, $user_data);

    echo("success");
