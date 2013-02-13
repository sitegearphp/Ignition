<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Sitegear Ignition main closure.  This is the whole ignition sequence, when this is done the website skeleton will be
 * constructed and the baseline configuration and data files will be in place.
 */
call_user_func(function() {

	echo 'Sitegear Ignition: commencing...', PHP_EOL;
	$exception = null;

	try {
		// Resource paths.
		$resourcesRootUrl = 'http://sitegear.org/ignition/resources/intermediate';
		$targetResourcesRootUrl = 'http://sitegear.org/ignition/resources/target';
		$localResourcesCache = __DIR__ . '/.ignition-cache';
		$localStaging = __DIR__ . '/.ignition-staging';
		$requirements = array( 'questions', 'data', 'structure', 'functions' );

		// Setup file system.
		echo 'Setting up file system...', PHP_EOL;
		if (!mkdir($localResourcesCache)) {
			throw new RuntimeException(sprintf('Cannot create local cache directory "%s" for ignition requirements.', $localResourcesCache));
		}
		if (!mkdir($localStaging)) {
			throw new RuntimeException(sprintf('Cannot create local staging directory "%s".', $localStaging));
		}

		// Download and process each of the requirements in sequence.
		echo 'Downloading resources...', PHP_EOL;
		foreach ($requirements as $requirement) {
			// Determine the source URL and target filename.
			$url = sprintf('%s/%s.php', $resourcesRootUrl, $requirement);
			$target = sprintf('%s/%s.php', $localResourcesCache, $requirement);
			// Download the file.
			echo sprintf('Downloading required script "%s" from "%s" to "%s"', $requirement, $url, $target), PHP_EOL;
			if (!copy($url, $target) || !file_exists($target)) {
				// We can't continue if all the files don't download.
				throw new RuntimeException(sprintf('Could not copy required script "%s" from source "%s" to local file "%s"; check your internet connection.', $requirement, $url, $target));
			}
			// TODO Checksum or some other validation on the script for security.
			// Create a variable named by $item; assign it the value returned by including the target file.
			echo sprintf('Executing downloaded script "%s"', $target);
			$$requirement = require $target;
			echo 'Done', PHP_EOL;
		}

		// The three data arrays are defined by the included files; if all is well then generate the skeleton site.
		if (isset($questions) && isset($data) && isset($structure)) {
			echo 'Requirements fulfilled, generating site skeleton...', PHP_EOL;

			// TODO Ask questions

			// TODO Amend data structures

			echo 'Building the file system structure in the local staging area', PHP_EOL;
			buildStructure($structure, $data, $localStaging, $targetResourcesRootUrl);

			echo 'Moving file system structure to the website root', PHP_EOL;
			moveAll($localStaging, __DIR__);

			echo 'Processing dependencies using composer', PHP_EOL;
			// TODO This is hardcoded --dev, it should be based on answer to relevant question
			composerInstall(__DIR__, $localResourcesCache, array( '--dev' ));
		} else {
			// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
			throw new RuntimeException('Requirements unfulfilled.');
		}
	} catch (Exception $e) {
		// Save the exception to throw later, after we've cleaned up.
		$exception = $e;
	}

	// Cleanup file system.
	echo 'Cleaning up file system; removing local resources cache...', PHP_EOL;
	if (!recursiveRemoveDirectory($localResourcesCache)) {
		throw new Exception(sprintf('Couldn\'t clean up local resources cache "%s"', $localResourcesCache));
	} elseif (!is_null($exception)) {
		throw $exception;
	} else {
		echo PHP_EOL, 'Sitegear Ignition is done.  Your site is ready to design and populate.', PHP_EOL, PHP_EOL;
	}

});
