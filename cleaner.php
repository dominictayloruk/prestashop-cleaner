<?php

/*
* Prestashop Cleaner
*/

$version = '0';

ini_set('display_errors', 1);
error_reporting(E_ALL);
@date_default_timezone_set('Europe/London');
$root_path = getcwd() . '/';
$root_directory = basename($root_path);
$updating = false;

$arrContextOptions = array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
);
$admin_dir = false;
$found = 0;
$files = scandir($root_path);
$exclude_dirs = array('.', '..', 'Adapter', 'cache', 'classes', 'config', 'controllers', 'Core', 'css', 'docs', 'download', 'error', 'images', 'img', 'js', 'localization', 'log', 'mails', 'modules', 'override', 'pdf', 'stats', 'themes', 'tools', 'translations', 'upload', 'webservice');
foreach($files as $file) {    
    if(@is_dir($root_path.$file) && !in_array($root_path.$file, $exclude_dirs)) {
        $admin_files = scandir($root_path.$file);
        foreach($admin_files as $admin_file) {
            if($admin_file == 'get-file-admin.php'){
                $admin_dir = $file;
                $found++;
            }
        }
    }
} 
$html = '<html itemscope="" itemtype="https://schema.org/QAPage" lang="en">
        <head>
            <style>
                body{background:black;padding:15px 15px 30px;color:white;}.slide-out{display: none;position: absolute;background-color:white;color:black;padding:20px 30px;left:50%;top:20px;transform:translate(-50%);overflow:auto;z-index:2;word-break:break-word;white-space:break-spaces;min-width:60%;max-width:90%;}
            </style>
            <script text="javascript">
                function isHidden(el) {
                    return ((window.getComputedStyle(el).getPropertyValue("display") === "none") || (window.getComputedStyle(el).getPropertyValue("visibility") === "hidden"))
                }
                function toggleSideNav(el) {
                    var nextDiv = el.nextSibling;
                    if(isHidden(nextDiv)) {
                        var detailBlocks = document.getElementsByClassName("slide-out");
                        Array.prototype.forEach.call(detailBlocks, function(detailBlock) {
                            detailBlock.style.cssText = "display:none";
                            detailBlock.previousSibling.innerText = "View";
                        });
                        nextDiv.style.cssText = "display:block";
                        el.innerText = "Hide";
                        el.scrollIntoView({ 
                            behavior: "smooth", 
                            block: "center"
                        });
                    }
                    else {
                        nextDiv.style.cssText = "display:none";
                        el.innerText = "View";
                    }
                }
            </script>
        </head>
        <body>
        <pre><h3 style="font-size: 1.2em;">Prestashop Cleaner</h3>' . PHP_EOL;
if($found > 1)
    die($html.'<span style="color:red; font-size:1.5em;">Multiple /admin type directories were found.<br/> Please delete the unnecessary ones or the script will not know which one to analyze</span></pre></body></html>');
if(!$admin_dir) {
    die($html.'<span style="color:red">Unknown CMS. Script stopped</span></pre></body></html>');
}
// Protect script url
$encoded = substr(md5($root_path . $admin_dir), 0, 12);
$latest_filename = $encoded.'.php';
$current_url = strtok((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '?'); 
$current_script = basename($current_url);
$new_url = str_replace($current_script, $latest_filename, $current_url);
$suspicious_zip = new ZipArchive();
$old_zip_filename = $root_path.'suspicious.zip';
$zip_filename = $root_path.'suspicious_'.$encoded.'.zip';
if(file_exists($old_zip_filename))
    @unlink($zip_filename);
if(file_exists($zip_filename))
    @unlink($zip_filename);
// Get lastest version 
if(ini_get('allow_url_fopen')) { 
    if (!preg_match("/modules/i", $root_path) && file_exists('init.php')) {
        $fgc = file_get_contents('https://raw.githubusercontent.com/dominictayloruk/prestashop-cleaner/main/cleaner.txt', false, stream_context_create($arrContextOptions));
        if ($fgc && preg_match("/class_index\.php/", $fgc)) {
            if(!file_put_contents($latest_filename, $fgc . PHP_EOL . '/* Version downloaded from github.com/dominictayloruk/prestashop-cleaner - ' . date("Y-m-d H:i:s") . '*/'))
                die('<span style="color:red">Unable to update the file. Insufficient write permissions</span></pre></body></html>');
            else {
                if($current_script == 'cleaner.php')
                    file_put_contents('cleaner.php', $fgc);
            }
            if (file_exists($latest_filename)) {
                header('Refresh: 1; url='.$new_url);
                echo $html;
                echo '<span style="color:red">Your version needs to be updated. Downloading the latest version and executing...</span></pre>';
                sleep(2);
                $updating = true;
            }
        }
    }
    else
        die('<pre><span style="color:red">This script must be placed at the root of your site (where your Prestashop is installed on your ftp) and nowhere else</span></pre></body></html>');
} 
else {
    die('<pre><span style="color:red">The allow_url_fopen directive is disabled on your server. Please enable it to use this script.</span></pre></body></html>');
} 