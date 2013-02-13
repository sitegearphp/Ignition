<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Ripped directly from the composer installer function `out()` with American spelling fixed (^_^).
 *
 * See https://getcomposer.org/installer
 *
 * @param string $text
 * @param string|null $colour
 * @param boolean $newLine
 *
 * @internal param $string |null $colour
 */
function output($text, $colour=null, $newLine=true) {
	if (DIRECTORY_SEPARATOR == '\\') {
		 $hasColourSupport = false !== getenv('ANSICON');
	 } else {
		 $hasColourSupport = true;
	 }

	 $styles = array(
		 'success' => "\033[0;32m%s\033[0m",
		 'error' => "\033[31;31m%s\033[0m",
		 'info' => "\033[33;33m%s\033[0m"
	 );

	 $format = '%s';
	 if (isset($styles[$colour]) && $hasColourSupport) {
		 $format = $styles[$colour];
	 }

	 if ($newLine) {
		 $format .= PHP_EOL;
	 }

	 printf($format, $text);
}

/**
 * Sitegear Ignition main closure.  This is the whole ignition sequence, when this is done the website skeleton will be
 * constructed and the baseline configuration and data files will be in place.
 */
call_user_func(function() {

	output(PHP_EOL . 'Sitegear Ignition' . PHP_EOL, 'success');
	$exception = null;

	try {
		// Resource paths.
		$resourcesRootUrl = 'http://sitegear.org/ignition/resources/intermediate';
		$targetResourcesRootUrl = 'http://sitegear.org/ignition/resources/target';
		$localResourcesCache = __DIR__ . '/.ignition-cache';
		$localStaging = __DIR__ . '/.ignition-staging';
		$requirements = array( 'questions', 'data', 'structure', 'functions' );

		// Setup file system.
		output('Setting up file system...', 'info', false);
		if (!mkdir($localResourcesCache)) {
			throw new RuntimeException(sprintf('Cannot create local cache directory "%s" for ignition requirements.', $localResourcesCache));
		}
		if (!mkdir($localStaging)) {
			throw new RuntimeException(sprintf('Cannot create local staging directory "%s".', $localStaging));
		}
		output('Done', 'success');

		// Download and process each of the requirements in sequence.
		output('Downloading resources', 'info');
		foreach ($requirements as $requirement) {
			// Determine the source URL and target filename.
			$url = sprintf('%s/%s.php', $resourcesRootUrl, $requirement);
			$target = sprintf('%s/%s.php', $localResourcesCache, $requirement);

			// Download the file.
			output(sprintf('Downloading required script "%s" from "%s" to "%s"...', $requirement, $url, $target), 'info', false);
			if (!copy($url, $target) || !file_exists($target)) {
				// We can't continue if all the files don't download.
				throw new RuntimeException(sprintf('Could not copy required script "%s" from source "%s" to local file "%s"; check your internet connection.', $requirement, $url, $target));
			}
			output('Done', 'success');

			// TODO Checksum or some other validation on the script for security.

			// Create a variable named by $item; assign it the value returned by including the target file.
			output(sprintf('Executing downloaded script "%s"... ', $target), 'info', false);
			$$requirement = require $target;
			output('Done', 'success');
		}
		output('Finished downloading all resources', 'info');

		// The three data arrays are defined by the included files; if all is well then generate the skeleton site.
		if (!(isset($questions) && isset($data) && isset($structure))) {
			// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
			throw new RuntimeException('Requirements unfulfilled.');
		}
		output('Requirements fulfilled', 'success');

		// TODO Ask questions and update data structures accordingly
//		output('Please answer the following questions to customise your Sitegear website.  You can accept the defaults for many of the questions if you wish.', 'info');

		output('Building the file system structure in the local staging area...', 'info');
		buildStructure($structure, $data, $localStaging, $targetResourcesRootUrl);
		output('File system structure created in local staging area', 'success');

		output('Moving file system structure to the website root...', 'info');
		moveAll($localStaging, __DIR__);
		output('File system structure deployed', 'success');

		output('Processing dependencies using Composer... ', 'info');
		// TODO This is hardcoded --dev, it should be based on answer to relevant question
		composerInstall(__DIR__, $localResourcesCache, array( '--dev' ));
		output('Dependencies deployed successfully by Composer', 'success');
	} catch (Exception $e) {
		// Save the exception to throw later, after we've cleaned up.
		output('An error has occurred: ', $e->getMessage(), 'error');
		$exception = $e;
	}

	// Cleanup file system.
	output('Cleaning up file system; removing local resources cache...', 'info');
	if (!recursiveRemoveDirectory($localResourcesCache)) {
		throw new Exception(sprintf('Couldn\'t clean up local resources cache "%s"', $localResourcesCache));
	} elseif (!is_null($exception)) {
		throw $exception;
	} else {
		output(PHP_EOL . 'Sitegear Ignition is done.  Your site is ready to design and populate.' . PHP_EOL, 'success');
	}

});
