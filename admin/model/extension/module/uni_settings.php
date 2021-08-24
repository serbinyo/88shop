<?php
	if(!defined('DIR_SYSTEM')) {
		die('Access denied');
	}

	ini_set('display_errors', 0);

	$version = explode('.', PHP_VERSION);
			
	if($version[0] >= 5 && $version[1] >= 6) {
		include_once('uni_part1.php');
	} elseif ($version[0] >= 7 && ($version[1] >= 1 && $version[1] <= 4)) {
		include_once('uni_part2.php');
	}
?>