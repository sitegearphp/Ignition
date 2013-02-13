<?php
/*!
 * This file is a part of Sitegear Ignition.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Nested array structure defining the questions presented to the user, the types of answers required, default values,
 * and relationships between the questions.
 */
return array(
	array(
		'question' => 'Please enter a site id',
		'notes' => array(
			'This is a unique identifier, in lower-case-with-hyphens, for the site.',
			'This is recommended but not required in dedicated hosting environments.'
		),
		'type' => 'string',
		'required' => true
	),
	array(
		'question' => 'Please enter a site display name',
		'notes' => array(
			'This is used in content management tools and may be used within the site as a configuration item.'
		),
		'type' => 'string'
	),
	array(
		'question' => 'Please enter the URL of the site logo',
		'notes' => array(
			'This is used in content management tools and may be used within the site as a configuration item.',
			'This may be an absolute URL or a relative URL within the site itself.'
		),
		'type' => 'string'
	),
	array(
		'question' => 'Please enter the site administrator\'s name',
		'notes' => array(
			'This is displayed on error pages.',
			'If you do not wish to use an individual\'s name, use something like "the site administrator".'
		),
		'type' => 'string'
	),
	array(
		'question' => 'Please enter the site administrator\'s email address',
		'notes' => array(
			'This is displayed on error pages.',
			'If you do not wish to use an individual\'s email address, use an alias or leave this blank.'
		),
		'type' => 'string'
	),
	array(
		'question' => 'Do you need to add a site email address?',
		'notes' => array(
			'Email addresses can be added to the configuration file and referred to throughout the site, either for form processing or display purposes.'
		),
		'type' => 'loop',
		'default' => 'no',
		'dependents' => array(
			array(
				'question' => 'Please enter the email address key (e.g. "admin" or "contact")',
				'type' => 'string',
				'required' => true
			),
			array(
				'question' => 'Please enter the email address',
				'type' => 'string',
				'required' => true
			),
			array(
				'question' => 'Please enter the override email address for development environments',
				'type' => 'string'
			)
		)
	),
	array(
		'question' => 'Will you be running on Apache?',
		'notes' => array(
			'This will generate a .htaccess file.',
			'If you are using a different server or select no, additional configuration may be required.'
		),
		'type' => 'boolean',
		'default' => 'no'
	),
	array(
		'question' => 'Do you want to include and activate XSendfile?',
		'notes' => array(
			'XSendfile is any web server module or function which allows direct serving of static files after processing by dynamic scripts like PHP.',
			'Examples exist for Apache and for other web servers, but not for the PHP built-in web server.',
			'It is perfectly safe to enable this even if XSendfile is not available, it will only be used if it is both configured and detected.'
		),
		'type' => 'boolean',
		'default' => 'yes',
		'dependents' => array(
			array(
				'question' => '',
				'type' => 'string'
			)
		)
	),
	array(
		'question' => 'Do you want to use XML style markup?',
		'notes' => array(
			'If you don\'t know what this is, select "no".'
		),
		'type' => 'boolean',
		'default' => 'no'
	),
	array(
		'question' => 'Do you want to include HTML5 boilerplate?',
		'notes' => array(
			'HTML5 boilerplate provides a useful starting point for your HTML5 page templates.'
		),
		'type' => 'boolean',
		'default' => 'yes',
		'dependents' => array(
			array(
				'question' => 'Do you want to include the IE-specific html element classes?',
				'notes' => array(
					'This is an optional feature of HTML5 boilerplate which provides version-specific classes on the root (html) element.',
				),
				'type' => 'boolean',
				'default' => 'no'
			)
		)
	),
	array(
		'question' => 'Do you need a database?',
		'notes' => array(
			'Any database type supported by Doctrine is supported by Sitegear',
			'Currently only MySQL is supported by the Sitegear Ignition script',
			'This option must be selected if you are using any modules that require Doctrine module, otherwise additional configuration is required'
		),
		'type' => 'boolean',
		'default' => 'no',
		'dependents' => array(
			array(
				'question' => 'Please enter the database name',
				'type' => 'string'
			),
			array(
				'question' => 'Please enter the username',
				'type' => 'string'
			),
			array(
				'question' => 'Please enter the password',
				'type' => 'string'
			)
		)
	),
	array(
		'question' => 'Do you want to include and activate Monolog?',
		'notes' => array(
			'Monolog is a logging framework which seamlessly integrates with Sitegear when it is available.',
			'Any PSR-3 compatible logging framework may be used.'
		),
		'type' => 'boolean',
		'default' => 'yes',
		'dependents' => array(
			array(
				'question' => 'What is the filename you wish to record the log messages in?',
				'notes' => array(
					'Relative to the site root.'
				),
				'type' => 'string',
				'default' => 'sitegear.log'
			),
			array(
				'question' => 'What is the minimum logging level you wish to use?',
				'notes' => array(
					'Must be a valid logging level: debug, info, alert, notice, warning, error, fatal.',
					'Specifies the minimum level of log messages that are written to the file.'
				),
				'type' => 'string',
				'default' => 'info'
			)
		)
	),
	array(
		'question' => 'Do you want to add a user?',
		'notes' => array(
			'Users created here will have full administration privileges',
			'At least one user must be created for access to the content management tools without further configuration.'
		),
		'type' => 'loop',
		'default' => 'no',
		'dependents' => array(
			array(
				'question' => 'Please enter the username',
				'type' => 'string'
			),
			array(
				'question' => 'Please enter the password',
				'type' => 'string'
			)
		)
	)
);
