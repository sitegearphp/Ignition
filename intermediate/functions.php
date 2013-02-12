<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Recursive directory delete.
 *
 * @param $dir
 *
 * @return bool
 */
function rrmdir($dir) {
	$success = true;
	foreach (scandir($dir) as $file) {
		if (!in_array($file, array( '.', '..' ))) {
			$path = sprintf('%s/%s', $dir, $file);
			if (is_dir($path)) {
				$success = rrmdir($path) && $success;
			} else {
				$success = unlink($path) && $success;
			}
		}
	}
	return rmdir($dir) && $success;
}

return true;
