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
 *
 * This file contains the basic framework of Sitegear Ignition, and some support functions, but does not define the
 * main processing functionality; that is downloaded by this script and included dynamically.  This allows for easier
 * distribution of latest versions.
 */
call_user_func(function() {

	/**
	 * Ripped directly from the composer installer function `out()` with American spelling fixed (^_^).
	 *
	 * See https://getcomposer.org/installer
	 *
	 * @param string $text Text to display.
	 * @param string|null $colour Colour identifier.  Currently, 'success', 'error' and 'info' are supported.
	 * @param boolean $newline Whether to emit a newline at the end of the output (true by default).
	 *
	 * @internal param $string |null $colour
	 */
	function output($text, $colour=null, $newline=true) {
		// Detect support for coloured text.
		if (DIRECTORY_SEPARATOR == '\\') {
			$hasColourSupport = false !== getenv('ANSICON');
		} else {
			$hasColourSupport = true;
		}
		// Style declarations.
		$styles = array(
			'success' => "\033[0;32m%s\033[0m",
			'error' => "\033[31;31m%s\033[0m",
			'info' => "\033[33;33m%s\033[0m"
		);
		// Determine the sprintf() format mask to use.
		$format = '%s';
		if (isset($styles[$colour]) && $hasColourSupport) {
			$format = $styles[$colour];
		}
		if ($newline) {
			$format .= PHP_EOL;
		}
		// Produce output.
		printf($format, $text);
	}

	/**
	 * Create the basic file system requirements for the ignition script.  That is:
	 *
	 *  1. Create the local cache directory if it does not already exist.  If it already exists, but is invalid, then
	 *     it will need to be manually removed before re-running the script.
	 *  2. Create a fresh local staging directory.  That is, any existing local staging directory is completely removed
	 *     and a new, empty directory is created.
	 *
	 * @param string $localCacheDir Path to the cache directory.
	 * @param string $localStagingDir Path to the staging directory.
	 *
	 * @throws RuntimeException
	 */
	function setupFileSystem($localCacheDir, $localStagingDir) {
		output('Setting up file system... ', 'info', false);
		// Create the cache directory if it does not already exist.
		if (!is_dir($localCacheDir) && !mkdir($localCacheDir)) {
			throw new RuntimeException(sprintf('Cannot create local cache directory "%s" for ignition requirements.', $localCacheDir));
		}
		// Remove the local staging directory if it exists.
		if (is_dir($localStagingDir) && !recursiveRemoveDirectory($localStagingDir)) {
			throw new RuntimeException(sprintf('Cannot remove previous local staging directory "%s", already exists but cannot be deleted.', $localStagingDir));
		}
		// Create a new local staging directory.
		if (!mkdir($localStagingDir)) {
			throw new RuntimeException(sprintf('Cannot create local staging directory "%s".', $localStagingDir));
		}
		output('Done', 'success');
	}

	/**
	 * Download the resources specified by the given "requirements".  Each requirement is a PHP script that is
	 * downloaded from the Sitegear server and stored in the local cache directory.  These requirement scripts provide
	 * the data and functionality required for the actual functionality of Sitegear Ignition.
	 *
	 * @param string[] $requirements List of requirement script names (simple name only, without any extension).
	 * @param string $resourcesRootUrl URL containing the remote resources.
	 * @param string $localCacheDir Local cache directory where the resources are copied to.
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

	/**
	 * Download the specified requirement script into the local cache directory, and return the result of `require`-ing
	 * the downloaded script.
	 *
	 * @param string $requirement Requirement to process.
	 * @param string $localCacheDir Path to the local cache directory.
	 *
	 * @return mixed
	 */
	function processRequirement($requirement, $localCacheDir) {
		$script = sprintf('%s/%s.php', $localCacheDir, $requirement);
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
				// Create a variable in this function's scope which is assigned the value returned by downloading and
				// `require`ing the named requirement script.
				$$requirement = processRequirement($requirement, $localCacheDir);
			}
			if (!(isset($questions) && isset($data) && isset($structure))) {
				// Something is really wrong, one or more of the requirements was downloaded but didn't fulfil its interface.
				throw new RuntimeException('Requirements unfulfilled.');
			}
			output('Requirements fulfilled, ready to proceed with ignition', 'success');
			// Main processing sequence.
			// 1. Ask questions.
			askQuestions($questions, $structure, $data);
			output('All responses accepted', 'success');
			// 2. Build the file system structure in the staging area.
			buildStructure($structure, $data, $localStagingDir, $targetResourcesRootUrl);
			output('Website structure built in staging area', 'success');
			// 3. Move the file system contents into the target directory.
			deploy($localStagingDir, __DIR__);
			output('Website structure deployed to target directory', 'success');
			// 4. Process dependencies.
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

	// Call the main method.
	install();

});
