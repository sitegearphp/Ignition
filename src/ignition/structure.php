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
				'type' => 'json'
			),
			array(
				'name' => 'configuration.development.json',
				'type' => 'json'
			),
			array(
				'name' => 'users.json',
				'type' => 'json'
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
				'type' => 'bootstrap'
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
						'name' => 'sections',
						'type' => 'directory',
						'contents' => array(
							array(
								'name' => 'main',
								'type' => 'directory',
								'contents' => array(
									array(
										'name' => 'index.phtml',
										'type' => 'download'
									)
								)
							)
						)
					),
					array(
						'name' => 'templates',
						'type' => 'directory',
						'contents' => array(
							array(
								'name' => 'default.phtml',
								'type' => 'template'
							)
						)
					),
					array(
						'name' => 'navigation.json',
						'type' => 'json'
					)
				)
			)
		)
	),
	array(
		'name' => 'composer.json',
		'type' => 'json'
	),
	array(
		'name' => 'app.php',
		'type' => 'download'
	),
	array(
		'name' => 'engine.php',
		'type' => 'download'
	),
	array(
		'name' => 'cli-config.php',
		'type' => 'download'
	)
);
