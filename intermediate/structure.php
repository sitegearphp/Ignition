<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Nested array structure defining the directories and files to create in the target directory.
 *
 * This structure is augmented programmatically depending on user selection and input.
 */
return array(
	array(
		'name' => 'config',
		'type' => 'directory',
		'contents' => array(
			array(
				'name' => 'configuration.json',
				'type' => 'generated'
			),
			array(
				'name' => 'configuration.development.json',
				'type' => 'generated'
			),
			array(
				'name' => 'users.json',
				'type' => 'generated'
			)
		)
	),
	array(
		'name' => 'public',
		'type' => 'directory',
		'contents' => array(
			array(
				'name' => 'css',
				'type' => 'directory'
			),
			array(
				'name' => 'images',
				'type' => 'directory'
			),
			array(
				'name' => 'js',
				'type' => 'directory'
			),
			array(
				'name' => 'index.php',
				'type' => 'generated-bootstrap'
			)
		)
	),
	array(
		'name' => 'site',
		'type' => 'directory',
		'contents' => array(
			array(
				'name' => 'content',
				'type' => 'directory',
				'contents' => array(
					array(
						'name' => 'components',
						'type' => 'directory'
					),
					array(
						'name' => 'components',
						'type' => 'directory'
					),
					array(
						'name' => 'components',
						'type' => 'directory'
					),
					array(
						'name' => 'navigation.json',
						'type' => 'generated'
					)
				)
			)
		)
	),
	array(
		'name' => 'composer.json',
		'type' => 'generated'
	),
	array(
		'name' => 'app.php',
		'type' => 'download',
		'url' => 'http://sitegear.org/ignition/resources/target/app.php'
	),
	array(
		'name' => 'engine.php',
		'type' => 'download',
		'url' => 'http://sitegear.org/ignition/resources/target/engine.php'
	),
	array(
		'name' => 'cli-config.php',
		'type' => 'download',
		'url' => 'http://sitegear.org/ignition/resources/target/cli-config.php'
	)
);
