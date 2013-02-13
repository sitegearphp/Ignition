<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Nested array structure defining the contents of JSON data files added to the generated site structure.  Each
 * top-level key is a filename (unqualified, just the "leaf" name), and the value is the array representation of the
 * data to add to that file.
 *
 * Each top-level entry here corresponds to an entry in the `structure.php` data structure, which has a `type` value
 * of "generated".
 *
 * This structure is augmented programmatically depending on user selection and input.
 */
return array(
	/**
	 * Composer configuration.  This is what brings in all the dependencies including Sitegear itself.
	 */
	'composer.json' => array(
		'require' => array(
			'sitegear/sitegear' => '*'
		),
		// TODO This is only temporary, it should be added in response to the relevant question
		'minimum-stability' => 'dev'
	),
	/**
	 * Main configuration file.
	 */
	'configuration.json' => array(),
	/**
	 * Development configuration overrides file.
	 */
	'configuration.development.json' => array(),
	/**
	 * User data file (authentication and access control).
	 */
	'users.json' => array(),
	/**
	 * Navigation data file (for generation of site navigation, breadcrumbs, sitemaps, etc).
	 */
	'navigation.json' => array(
		array(
			'url' => '',
			'label' => 'Home',
			'tooltip' => 'Return to the home page'
		)
	)
);
