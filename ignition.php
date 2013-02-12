<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Sitegear Ignition main closure.
 */
call_user_func(function() {

	try {
		echo 'Sitegear Ignition: commencing...', PHP_EOL;

		// Resource paths.
		$resourcesRoot = 'http://sitegear.org/ignition/resources/intermediate/';
		$localResourcesCache = 'ignition/';
		$requirements = array( 'questions', 'data', 'structure', 'functions' );

		// Setup file system.
		if (!mkdir($localResourcesCache)) {
			throw new RuntimeException(sprintf('ERROR: Cannot create local cache directory "%s" for ignition requirements.', $localResourcesCache));
		}

		// Download and process each of the requirements in sequence.
		foreach ($requirements as $requirement) {
			// Determine the source URL and target filename.
			$url = sprintf('%s%s.php', $resourcesRoot, $requirement);
			$target = sprintf('%s%s.php', $localResourcesCache, $requirement);
			// Download the file.
			echo sprintf('Downloading required script "%s" from "%s" to "%s"...', $requirement, $url, $target), PHP_EOL;
			if (!copy($url, $target) || !file_exists($target)) {
				// We can't continue if all the files don't download.
				throw new RuntimeException(sprintf('ERROR: Could not copy required script "%s" from source "%s" to local file "%s"; check your internet connection.', $requirement, $url, $target));
			}
			// Create a variable named by $item; assign it the value returned by including the target file.
			echo sprintf('Including required script "%s"...', $requirement, $target), PHP_EOL;
			$$requirement = require $target;
		}

		// The three data arrays are defined by the included files.
		if (isset($questions) && isset($data) && isset($structure)) {
			// Perform the main sequence.
			echo 'Proof of concept works!', PHP_EOL;
			print_r($questions); echo PHP_EOL, PHP_EOL;
			print_r($data); echo PHP_EOL, PHP_EOL;
			print_r($structure); echo PHP_EOL, PHP_EOL;
		} else {
			// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
			throw new RuntimeException('ERROR: Requirements unfulfilled.');
		}
	} catch (Exception $exception) {
		error_log(sprintf('ERROR: %s', $exception));
	}

	// Cleanup file system.
	rrmdir($localResourcesCache);

});
