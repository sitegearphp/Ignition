<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Ask the questions provided, and update the `$structure` and `$data` arrays according to the answers and the question
 * metadata.  Recurse into `dependent` questions, only when the answer is either true (boolean/loop types) or non-empty
 * (string types).
 *
 * @param array $questions
 * @param array $structure
 * @param array $data
 * @param array $values
 *
 * @throws RuntimeException
 */
function askQuestions(array $questions, array &$structure, array &$data, array &$values) {
	foreach ($questions as $question) {
		$askQuestion = true;
		while ($askQuestion) {
			// Q and A, this question and then dependents.
			writeQuestionText($question);
			$answer = askUntilValidAnswer($question);
			if ($answer && isset($question['dependents']) && is_array($question['dependents'])) {
				askQuestions($question['dependents'], $structure, $data, $values);
			}

			// Handle the original answer.  This is done after dependents are processed so that answers from dependents
			// can be used in the replacements performed here.
			handleAnswer($question, $answer, $structure, $data, $values);

			// Only go again if this was a 'loop' type question and the answer was positive.
			$askQuestion = ($question['type'] === 'loop') && $answer;
		}
	}
}

/**
 * Output the 'text' of the given question, that is, the question itself and any additional notes.
 *
 * @param array $question
 */
function writeQuestionText(array $question) {
	output(PHP_EOL . $question['question'], 'success');
	if (isset($question['notes'])) {
		output('Notes:', 'info');
		output(' * ' . implode(PHP_EOL . ' * ', $question['notes']), 'info');
	}
	if (isset($question['options'])) {
		output('Options:', 'info');
		foreach ($question['options'] as $option) {
			output(sprintf(' * %s (%s)', $option['value'], $option['label']), 'info');
		}
	}
}

/**
 * Write the prompt and receive input from the user for the given question, until a valid response is given.  Convert
 * that response into the relevant type and return it.
 *
 * @param array $question Definition of question to ask.
 *
 * @return string|boolean Given answer.
 *
 * @throws RuntimeException
 */
function askUntilValidAnswer(array $question) {
	// Initialise
	$booleanPositive = array( 'yes', 'y', '1', 'true',  't' );
	$booleanNegative = array( 'no',  'n', '0', 'false', 'f' );
	$type = $question['type'];
	$required = isset($question['required']) && $question['required'];
	// Prompt for an answer until a valid one is given.
	$answer = null;
	while (is_null($answer)) {
		$response = readline(sprintf('Please give your answer (%s): ', getHint($question)));
		if (strlen($response) === 0 && isset($question['default'])) {
			$response = $question['default'];
		}
		if ($type === 'boolean' || $type === 'loop') {
			// Convert string value to boolean (or loop) value.  A valid true or false value causes the loop to exit.
			if (in_array($response, $booleanPositive)) {
				$answer = true;
			} elseif (in_array($response, $booleanNegative)) {
				$answer = false;
			} else {
				output(sprintf('You must answer either positively (%s) or negatively (%s)', implode(',', $booleanPositive), implode(',', $booleanNegative)), 'error');
			}
		} elseif ($type === 'string') {
			$valid = true;
			if (isset($question['options'])) {
				// Check the given answer is an available option.
				$valid = false;
				foreach ($question['options'] as $option) {
					$valid = $valid || ($option['value'] === $response);
				}
				// Special case, allow both default value and override with 'none' (since '' naturally means 'accept
				// default' so it cannot be used as its own value).
				if ($response === 'none') {
					$response = '';
				}
			}
			// Confirm that the answer was given or not required, as well as being a valid option.  This causes the
			// loop to exit.
			if ($valid && (!$required || strlen($response) > 0)) {
				$answer = $response;
			}
		} else {
			throw new RuntimeException(sprintf('Invalid question type "%s" specified.', $type));
		}
	}
	return $answer;
}

/**
 * Get the hint text for the given question.  That is, a representation of whether the question requires an answer or
 * not and what the default value is if no input is given (i.e. user just presses return).
 *
 * @param array $question Question definition array.
 *
 * @return string Text to display.
 */
function getHint(array $question) {
	$required = isset($question['required']) && $question['required'];
	$default = sprintf('default = %s', isset($question['default']) ? sprintf('"%s"', $question['default']) : '[empty]');
	return $required ? (!isset($question['default']) ? 'required' : sprintf('required; %s', $default)) : $default;
}

/**
 * Perform token replacements on the given text value, using the `$values` map for tokens.  Any token in the text like
 * `%token%` where "token" is any key in the `$values` array will be replaced by the corresponding value from the
 * `$values` array.  Tokens with no matching key in `$values` will be replaced by an empty string.
 *
 * @param mixed $value
 * @param array $values
 *
 * @return mixed
 */
function performTokenReplacements($value, array $values) {
	if (is_array($value)) {
		foreach ($value as $k => $v) {
			$value[$k] = performTokenReplacements($v, $values);
		}
	} elseif (is_string($value)) {
		foreach ($values as $k => $v) {
			$value = preg_replace(sprintf('/%%%s%%/', $k), $v, $value);
		}
		$value = preg_replace('/%.+?%/', '', $value);
	} // else return unmodified; forward compatibility
	return $value;
}

/**
 * Handle the given answer to the given question, potentially by updating the given data arrays.
 *
 * @param array $question
 * @param string|boolean $answer
 * @param array $structure
 * @param array $data
 * @param array $values
 *
 * @throws RuntimeException
 */
function handleAnswer(array $question, $answer, array &$structure, array &$data, array &$values) {
	// Handle the answer given.
	$positive = is_string($answer) ? (strlen($answer) > 0) : $answer === true;
	if ($positive && isset($question['actions']) && is_array($question['actions'])) {
		foreach ($question['actions'] as $action) {
			$actionAnswer = performTokenReplacements((isset($action['value']) ? $action['value'] : $answer), $values);
			switch ($action['type']) {
				case 'data':
					$name = $action['name'];
					if (isset($action['key'])) {
						$key = performTokenReplacements($action['key'], $values);
						$dataForKey = buildDataForKey($key, $actionAnswer);
					} else {
						$dataForKey = array( $actionAnswer );
					}
					$data[$name] = array_merge_recursive($data[$name], $dataForKey);
					break;
				case 'structure':
					mergeIntoStructure($structure, $action['path'], $actionAnswer);
					break;
				case 'store':
					$values[$action['name']] = $actionAnswer;
					break;
				default:
					throw new RuntimeException(sprintf('Unknown action type "%s" found for question "%s"', $action['type'], $question['question']));
			}
		}
	}
}

/**
 * Create a nested data array representing only the given value at the given key.  That is, if `$key` is `'a.b.c.d'`
 * and `$value` is `'foo'`, the result will be: `array( 'a' => array( 'b' => array( 'c' => array( 'd' => 'foo' ))))`
 *
 * @param array|string $key
 * @param string $value
 *
 * @return array
 */
function buildDataForKey($key, $value) {
	if (is_string($key)) {
		$key = explode('.', $key);
	}
	if (empty($key)) {
		return $value;
	} else {
		$k = array_shift($key);
		return array( $k => buildDataForKey($key, $value) );
	}
}

/**
 * Merge the given value into the `$structure` array hierarchy, using the given path.
 *
 * @param array $structure
 * @param string $path
 * @param mixed $value
 */
function mergeIntoStructure(array &$structure, $path, $value) {
	$path = explode('/', $path);
	$name = array_shift($path);
	foreach ($structure as $index => $entry) {
		if ($entry['name'] === $name) {
			if (sizeof($path) === 0) {
				$structure[$index]['contents'][] = $value;
			} else {
				$contents = $entry['contents'];
				mergeIntoStructure($contents, $path, $value);
				$structure[$index]['contents'] = $contents;
			}
		}
	}
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
 * @param string $rootDir The path to create the structure at; must already exist and have the relevant permissions set.
 * @param string $downloadRootUrl The root URL, ending with a slash `/`, under which the required resources can be
 *   accessed.
 *
 * @throws RuntimeException
 */
function buildStructure(array $structure, array $data, $rootDir, $downloadRootUrl) {
	output(sprintf('Building the file system structure in the local staging area at "%s"... ', $rootDir), 'info');
	foreach ($structure as $entry) {
		$type = $entry['type'];
		$name = $entry['name'];
		$path = sprintf('%s/%s', $rootDir, $name);
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
				$source = sprintf('%s/%s', $downloadRootUrl, isset($entry['src']) ? $entry['src'] : $name);
				if (!copy($source, $path)) {
					throw new RuntimeException(sprintf('Could not download required resource from "%s" to "%s"', $source, $path));
				}
				break;
			case 'json':
				$jsonOptions = defined('JSON_PRETTY_PRINT') && defined('JSON_UNESCAPED_SLASHES') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
				$jsonData = json_encode(normaliseData($data[$name]), $jsonOptions) . PHP_EOL;
				if (file_put_contents($path, $jsonData) === false) {
					throw new RuntimeException(sprintf('Could not create JSON file "%s" from data at key "%s"', $path, $name));
				}
				break;
		}
	}
}

/**
 * Remove empty values from the given data array (recursively).  This includes empty strings and arrays that are empty
 * or consist only of other empty values.
 *
 * Also trims all excessive whitespace from all values.
 *
 * To specify a value that is actually empty, use a string consisting of only whitespace, e.g. `' '`.
 *
 * @param array $data Data to filter.
 *
 * @return array Filtered data.
 */
function normaliseData(array $data) {
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = $value = normaliseData($value);
		}
		if (empty($value)) {
			unset($data[$key]);
		}
		if (is_string($value)) {
			$data[$key] = trim($value);
		}
	}
	return $data;
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
function composerInstall($target, $localCacheDir, array $additionalArguments=null) {
	$composerInstaller = $localCacheDir . '/install-composer.php';
	copy('https://getcomposer.org/installer', $composerInstaller);
	passthru(sprintf('php %s', $composerInstaller));
	passthru(sprintf('php %s/composer.phar install %s', $target, implode(' ', $additionalArguments ?: array())));
}

return true;
