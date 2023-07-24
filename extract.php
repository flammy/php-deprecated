<?php

if(count($argv) < 2){
    echo 
    "usage: php extract.php path\n".
    "example: php scan.php /tmp/php-chunked-xhtml/\n".
    "\n";
    exit(1);
}


/* Directory with extracted php-docs
 * from http://php.net/download-docs.php (Many HTML files tar.gz)
 */

$dir_name = $argv[1];
$deprecated_functions = array();
if ($handle = opendir($dir_name)) {
    while (false !== ($entry = readdir($handle))) {
        $file_content = "";
        $deprecated = array();
        $removed = array();
        if (!is_dir($dir_name.$entry) && (preg_match('/function.*php$/',$entry) !== false)) {
			$file_content = file_get_contents($dir_name.$entry);
            $file_content = str_replace("\n", "", $file_content);
            $file_content = str_replace("\r", "", $file_content);
            $deprecated = array();
            if(preg_match("/Warning\s*This\s*(function|extension)\s*(was|has been)\s*DEPRECATED\s*(in|as of) PHP ([0-9].[0-9].[0-9])/i", strip_tags($file_content),$deprecated) !== false){
                 $function = array();
                 if(preg_match('/<h1 class="refname">([a-zA-Z:_-]*)<\/h1>/', $file_content,$function) !== false){
                    if(!isset($function[1])){
                        continue;
                    }
                 }
                 //print_r($deprecated);
                if(isset($deprecated[4])){
                    $deprecated_functions[$function[1]]['deprecated'] = $deprecated[4];
                }
                $removed = array();
                if(preg_match("/Warning\s*This\s*(function|extension).*REMOVED\s*(in|as of) PHP\s+([0-9].[0-9].[0-9])/i", strip_tags($file_content),$removed) !== false){
                    if(isset($removed[3])){
                        $deprecated_functions[$function[1]]['removed'] = $removed[3];
                    }
                }
            }
        }
        unset($file_content);
        unset($deprecated);
        unset($removed);
    }
    closedir($handle);
}
print_r($deprecated_functions);
file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."data.json", json_encode($deprecated_functions));
