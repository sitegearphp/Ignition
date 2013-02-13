<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Ask the questions provided, and update the `$structure` and `$data` arrays according to the answers and the question
 * metadata.
 *
 * @param array $questions
 * @param array $structure
 * @param array $data
 *
 * @throws RuntimeException
 */
function askQuestions(array $questions, array &$structure, array &$data) {
	// Initialise.
	output('Please answer the following questions to customise your Sitegear website.  You can accept the defaults for many of the questions if you wish.', 'info');
	$booleanPositive = array( 'yes', 'y', '1', 'true',  't' );
	$booleanNegative = array( 'no',  'n', '0', 'false', 'f' );

	// Ask each question in turn, recurse for dependents.
	foreach ($questions as $question) {
		// Display the question and notes.
		output(PHP_EOL . $question['question'], 'success');
		if (isset($question['notes'])) {
			output(implode(PHP_EOL, $question['notes']), 'info');
		}

		// Prompt for an answer until a valid one is given.
		$answer = null;
		$required = isset($question['required']) && $question['required'];
		$hint = $required ?
				'required' :
				sprintf('default = %s', isset($question['default']) ? sprintf('"%s"', $question['default']) : '[empty]');
		while (is_null($answer)) {
			$response = readline(sprintf('Please give your answer (%s): ', $hint));
			if (strlen($response) === 0 && isset($question['default'])) {
				$response = $question['default'];
			}
			$type = $question['type'];
			if ($type === 'boolean' || $type === 'loop') {
				if (in_array($response, $booleanPositive)) {
					$answer = true;
				} elseif (in_array($response, $booleanNegative)) {
					$answer = false;
				} else {
					output(sprintf('You must answer either positively (%s) or negatively (%s)', implode(',', $booleanPositive), implode(',', $booleanNegative)), 'error');
				}
			} elseif ($type === 'string') {
				if (!$required || strlen($response) > 0) {
					$answer = $response;
				}
			} else {
				throw new RuntimeException(sprintf('Invalid question type "%s" specified.', $type));
			}
		}

		// Handle the answer given.
		output(sprintf('Answered %s', is_string($answer) ? $answer : ($answer ? 'true' : 'false')));
		// TODO

		// Process dependent questions.  Answer must be either true (boolean/loop types) or non-empty (string types)
		// for this to go ahead, otherwise it is skipped.
		if ($answer && isset($question['dependents']) && is_array($question['dependents'])) {
			askQuestions($question['dependents'], $structure, $data);
		}
	}
	output('All questions answered', 'success');
}

/**
 * Create the file system structure corresponding to the given nested array structure.  Each entry is a key-value
 * array, with the keys `name` (the file or directory name), `type` (see below), and in the case of `directory`
 * entries, a `contents` key which maps to an array which repeats the structure (and causes a recursive call).
 *
 * The `type` value may be one of the following types:
 *
 *  * `directory` which causes a recursive call.
 *  * `download` which grabs a resource from a URL relative to the given `$downloadRootUrl`.
 *  * `json` which uses generated data arrays from the `$data` argument to create JSON encoded text files.
 *  * `bootstrap` which generates the bootstrap script based on user selections.
 *
 * @param array $structure Nested array structure to build the file system structure from.
 * @param array $data Nested array structure containing data for JSON files, indexed at the top level by filename.
 * @param string $root The path to create the structure at; must already exist and have the relevant permissions set.
 * @param string $downloadRootUrl The root URL, ending with a slash `/`, under which the required resources can be
 *   accessed.
 *
 * @throws RuntimeException
 */
function buildStructure(array $structure, array $data, $root, $downloadRootUrl) {
	output(sprintf('Building the file system structure in the local staging area at "%s"... ', $root), 'info');
	foreach ($structure as $entry) {
		$type = $entry['type'];
		$name = $entry['name'];
		$path = sprintf('%s/%s', $root, $name);
		output(sprintf('Processing entry of type "%s" with name "%s" and path "%s"', $type, $name, $path), 'info');
		switch ($type) {
			case 'directory':
				if (!mkdir($path)) {
					throw new RuntimeException(sprintf('Could not create directory "%s"', $path));
				}
				if (isset($entry['contents'])) {
					buildStructure($entry['contents'], $data, $path, $downloadRootUrl);
				}
				break;
			case 'download':
				$source = sprintf('%s/%s', $downloadRootUrl, $name);
				if (!copy($source, $path)) {
					throw new RuntimeException(sprintf('Could not download required resource from "%s" to "%s"', $source, $path));
				}
				break;
			case 'json':
				if (file_put_contents($path, json_encode($data[$name])) === false) {
					throw new RuntimeException(sprintf('Could not create JSON file "%s" from data at key "%s"', $path, $name));
				}
				break;
			case 'bootstrap':
				// TODO buildBootstrap()
				break;
		}
	}
	output(sprintf('File system structure created in local staging area at "%s"', $root), 'success');
}

/**
 * Move all files inside the source directory to the target directory, and remove the source directory (can be disabled
 * by passing false as the third argument).
 *
 * @param string $source
 * @param string $target
 * @param boolean $removeSource
 *
 * @throws RuntimeException
 */
function deploy($source, $target, $removeSource=true) {
	output(sprintf('Deploying file system structure from staging area "%s" to target "%s"... ', $source, $target), 'info');
	foreach (scandir($source) as $file) {
		if (!in_array($file, array( '.', '..' ))) {
			$sourceFile = sprintf('%s/%s', $source, $file);
			$targetFile = sprintf('%s/%s', $target, $file);
			if (!rename($sourceFile, $targetFile)) {
				throw new RuntimeException(sprintf('Could not move file "%s" to "%s"', $sourceFile, $targetFile));
			}
		}
	}
	if ($removeSource) {
		if (!rmdir($source)) {
			throw new RuntimeException(sprintf('Could not remove source directory "%s"', $source));
		}
	}
	output(sprintf('File system structure deployed to "%s"', $target), 'success');
}

/**
 * Download composer.phar and use it to install dependencies.
 */
function composerInstall($target, $localResourcesCache, array $additionalArguments=null) {
	output('Processing dependencies using Composer... ', 'info');
	$composerInstaller = $localResourcesCache . '/install-composer.php';
	copy('https://getcomposer.org/installer', $composerInstaller);
	passthru(sprintf('php %s', $composerInstaller));
	passthru(sprintf('php %s/composer.phar install %s', $target, implode(' ', $additionalArguments ?: array())));
	output('Dependencies deployed successfully by Composer', 'success');
}

/**
 * Recursive directory delete.
 *
 * @param $dir
 *
 * @return bool
 */
function recursiveRemoveDirectory($dir) {
	$success = true;
	foreach (scandir($dir) as $file) {
		if (!in_array($file, array( '.', '..' ))) {
			$path = sprintf('%s/%s', $dir, $file);
			if (is_dir($path)) {
				$success = recursiveRemoveDirectory($path) && $success;
			} else {
				$success = unlink($path) && $success;
			}
		}
	}
	return rmdir($dir) && $success;
}

return true;
