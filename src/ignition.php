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
	 * @param $localCacheDir
	 * @param $localStagingDir
	 *
	 * @throws RuntimeException
	 */
	function setupFileSystem($localCacheDir, $localStagingDir) {
		output('Setting up file system... ', 'info', false);
		if (!is_dir($localCacheDir) && !mkdir($localCacheDir)) {
			throw new RuntimeException(sprintf('Cannot create local cache directory "%s" for ignition requirements.', $localCacheDir));
		}
		if (is_dir($localStagingDir) && !recursiveRemoveDirectory($localStagingDir)) {
			throw new RuntimeException(sprintf('Cannot remove previous local staging directory "%s", already exists but cannot be deleted.', $localStagingDir));
		}
		if (!mkdir($localStagingDir)) {
			throw new RuntimeException(sprintf('Cannot create local staging directory "%s".', $localStagingDir));
		}
		output('Done', 'success');
	}

	/**
	 * Download the resources specified by the given requirements.
	 *
	 * @param array $requirements
	 * @param string $resourcesRootUrl
	 * @param string $localCacheDir
	 *
	 * @throws RuntimeException
	 */
	function downloadIgnitionResources(array $requirements, $localCacheDir, $resourcesRootUrl) {
		// Download each of the requirements in sequence to the local cache.
		output('Downloading resources', 'info');
		foreach ($requirements as $requirement) {
			// Determine the source URL and target filename.
			$url = sprintf('%s/%s.php', $resourcesRootUrl, $requirement);
			$script = sprintf('%s/%s.php', $localCacheDir, $requirement);

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

	function processRequirement($requirement, $localCache) {
		$script = sprintf('%s/%s.php', $localCache, $requirement);
		// TODO Checksum or some other validation on the script for security.
		return require $script;
	}

	/**
	 * Main method.
	 *
	 * @throws RuntimeException
	 */
	function install() {
		// Initalise.
		output(PHP_EOL . 'Sitegear Ignition' . PHP_EOL, 'success');
		$exception = null;
		$ignitionResourcesRootUrl = 'http://sitegear.org/ignition/resources/ignition';
		$targetResourcesRootUrl = 'http://sitegear.org/ignition/resources/target';
		$localCacheDir = __DIR__ . '/.ignition-cache';
		$localStagingDir = __DIR__ . '/.ignition-staging';
		$requirements = array( 'questions', 'data', 'structure', 'functions' );
		$composerArguments = array( '--dev' );

		try {
			// Initialise the file system and populate the local cache, unless it already exists in which case use the
			// previously installed version.  Corrupted caches will have to be manually removed.
			$cacheExists = is_dir($localCacheDir);
			setupFileSystem($localCacheDir, $localStagingDir);
			if (!$cacheExists) {
				downloadIgnitionResources($requirements, $localCacheDir, $ignitionResourcesRootUrl);
			}

			// Process and check requirements.
			foreach ($requirements as $requirement) {
				$$requirement = processRequirement($requirement, $localCacheDir);
			}
			if (!(isset($questions) && isset($data) && isset($structure))) {
				// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
				throw new RuntimeException('Requirements unfulfilled.');
			}

			// Main processing sequence.
			output('Requirements fulfilled; please answer the following questions to customise your website...', 'success');
			askQuestions($questions, $structure, $data);
			buildStructure($structure, $data, $localStagingDir, $targetResourcesRootUrl);
			output('Website structure built in staging area; deploying site and dependencies...', 'success');
			deploy($localStagingDir, __DIR__);
			composerInstall(__DIR__, $localCacheDir, $composerArguments);
			output('Website and dependencies successfully deployed', 'success');
		} catch (Exception $e) {
			// Save the exception to throw later, after we've cleaned up.
			output('An error has occurred: ', $e->getMessage(), 'error');
			$exception = $e;
		}

		// Cleanup file system.
		output('Cleaning up file system; removing local resources cache... ', 'info');
		if (!recursiveRemoveDirectory($localCacheDir)) {
			// Falter at the finishing line.
			output(sprintf('An error occurred removing local cache directory "%s".  Everything else is fine.  The directory may be removed manually.', $localCacheDir), 'error');
		} elseif (!is_null($exception)) {
			// Report previously-caught exception during main processing.
			throw $exception;
		} else {
			// Success!  Sign-off message.
			output(PHP_EOL . 'Sitegear Ignition is done.  Your site is ready to design and populate.' . PHP_EOL, 'success');
		}
	}

	/* MAIN EXECUTION POINT */
	install();

});
