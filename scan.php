<?php
if(count($argv) < 3){
	echo 
	"usage: php scan.php version path\n".
	"example: php scan.php 7.0.0 /var/www/project\n".
	"\n";
	exit(1);
}

if(!preg_match("/[0-9]\.[0-9]\.[0-9]/", $argv[1])){
	echo "Version number ".$argv[1]." does not match [0-9].[0-9].[0-9]\n";
	exit(1);
}

if(!is_dir($argv[2])){
	echo "Directory ".$argv[2]." not found\n";
	exit(1);
}

$datafile = dirname(__FILE__).DIRECTORY_SEPARATOR."data.json";
if(isset($argv[3])){
	$datafile = $argv[3];
}


if(!file_exists($datafile)){
	echo "Datafile ".$datafile." not found, please execute extract first.\n";
	exit(1);
}

$dir_name = $argv[2];
$check_version = $argv[1];

$count = array();
$deprecated_functions = file_get_contents($datafile);
$deprecated_functions = json_decode($deprecated_functions);
$count = checkdir($dir_name,$deprecated_functions,$check_version);

print_r($count);

function checkdir($dir_name,$deprecated_functions,$check_version,$count = array()) {
   $cdir = scandir($dir_name);
   foreach ($cdir as $key => $value){
      if (!in_array($value,array(".",".."))){
         if (is_dir($dir_name . DIRECTORY_SEPARATOR . $value)){
             $count = checkdir($dir_name . DIRECTORY_SEPARATOR . $value,$deprecated_functions,$check_version,$count);
         }
         elseif(preg_match('/php$/',$value) ==! false){
         	$file_contents = file_get_contents($dir_name . DIRECTORY_SEPARATOR . $value);
         	foreach($deprecated_functions as $function_name => $depend){
         		$function_name = preg_replace("/^.*::/",'', $function_name);
         		if(preg_match('/\b'.$function_name.'\s*\(/', $file_contents)){
         			if(isset($count[$function_name])){
         				$count[$function_name] ++;
         			}else{
         				$count[$function_name] = 1;
         			}
         			if (isset($depend->removed) && version_compare($depend->removed, $check_version,'<=')) {
         				echo "removed in PHP ".$depend->removed.": ".$function_name." \t\tin ".$dir_name . DIRECTORY_SEPARATOR . $value."\n" ;
         			}elseif (isset($depend->deprecated) && version_compare($depend->deprecated, $check_version,'<=')) {
         				echo "deprecated in PHP ".$depend->deprecated.": ".$function_name." \t\tin ".$dir_name . DIRECTORY_SEPARATOR . $value."\n" ;
         			}
         		}
         	}
         }
      }
   }
   return $count;
}
