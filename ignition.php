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

	// Welcome message.
	output(PHP_EOL . 'Sitegear Ignition' . PHP_EOL, 'success');

	// Compensate for lack of `finally` in PHP.
	$exception = null;

	try {
		// Resource paths.
		$resourcesRootUrl = 'http://sitegear.org/ignition/resources/intermediate';
		$targetResourcesRootUrl = 'http://sitegear.org/ignition/resources/target';
		$localCache = __DIR__ . '/.ignition-cache';
		$localStaging = __DIR__ . '/.ignition-staging';
		$requirements = array( 'questions', 'data', 'structure', 'functions' );
		$composerArguments = array( '--dev' );

		// Setup file system.
		output('Setting up file system... ', 'info', false);
		$cacheExists = is_dir($localCache);
		if (!$cacheExists && !mkdir($localCache)) {
			throw new RuntimeException(sprintf('Cannot create local cache directory "%s" for ignition requirements.', $localCache));
		}
		if (is_dir($localStaging) && !recursiveRemoveDirectory($localStaging)) {
			throw new RuntimeException(sprintf('Cannot remove previous local staging directory "%s", already exists but cannot be deleted.', $localStaging));
		}
		if (!mkdir($localStaging)) {
			throw new RuntimeException(sprintf('Cannot create local staging directory "%s".', $localStaging));
		}
		output('Done', 'success');

		// Only initialise the cache if it does not already exist.  Broken caches will have to be manually removed:
		// `rm -fR .ignition-cache`
		if (!$cacheExists) {
			// Download each of the requirements in sequence to the local cache.
			output('Downloading resources', 'info');
			foreach ($requirements as $requirement) {
				// Determine the source URL and target filename.
				$url = sprintf('%s/%s.php', $resourcesRootUrl, $requirement);
				$script = sprintf('%s/%s.php', $localCache, $requirement);

				// Download the file.
				output(sprintf('Downloading required script "%s" from "%s" to "%s"... ', $requirement, $url, $script), 'info', false);
				if (!copy($url, $script) || !file_exists($script)) {
					// We can't continue if all the files don't download.
					throw new RuntimeException(sprintf('Could not copy required script "%s" from source "%s" to local file "%s"; check your internet connection.', $requirement, $url, $script));
				}
				output('Done', 'success');
			}
			output('Finished downloading all resources', 'info');
		}

		// Create a variable named by each requirement; assign it the value returned by including the relevant target
		// script, which was previously downloaded (or carried over from previous ignition attempt).
		foreach ($requirements as $requirement) {
			$script = sprintf('%s/%s.php', $localCache, $requirement);
			// TODO Checksum or some other validation on the script for security.
			output(sprintf('Including downloaded script "%s"... ', $script), 'info', false);
			$$requirement = require $script;
			output('Done', 'success');
		}

		// The three data arrays are defined by the included files; if all is well then generate the skeleton site.
		if (!(isset($questions) && isset($data) && isset($structure))) {
			// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
			throw new RuntimeException('Requirements unfulfilled.');
		}

		output('Requirements fulfilled; performing main build process', 'success');
		askQuestions($questions, $structure, $data);
		buildStructure($structure, $data, $localStaging, $targetResourcesRootUrl);
		deploy($localStaging, __DIR__);
		composerInstall(__DIR__, $localCache, $composerArguments);

	} catch (Exception $e) {
		// Save the exception to throw later, after we've cleaned up.
		output('An error has occurred: ', $e->getMessage(), 'error');
		$exception = $e;
	}

	// Cleanup file system.
	output('Cleaning up file system; removing local resources cache... ', 'info');
	if (!recursiveRemoveDirectory($localCache)) {
		throw new Exception(sprintf('Couldn\'t clean up local resources cache "%s"', $localCache));
	} elseif (!is_null($exception)) {
		throw $exception;
	} else {
		// Sign-off message.
		output(PHP_EOL . 'Sitegear Ignition is done.  Your site is ready to design and populate.' . PHP_EOL, 'success');
	}

});
