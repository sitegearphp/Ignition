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
		'required' => true,
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.id'
			)
		)
	),
	array(
		'question' => 'Please enter a site display name',
		'notes' => array(
			'This is used in content management tools and may be used within the site as a configuration item.'
		),
		'type' => 'string',
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.display-name'
			)
		)
	),
	array(
		'question' => 'Please enter the URL of the site logo',
		'notes' => array(
			'This is used in content management tools and may be used within the site as a configuration item.',
			'This may be an absolute URL or a relative URL within the site itself.'
		),
		'type' => 'string',
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.logo-url'
			)
		)
	),
	array(
		'question' => 'Please enter the site administrator\'s name',
		'notes' => array(
			'This is displayed on error pages.',
			'If you do not wish to use an individual\'s name, use something like "the site administrator".'
		),
		'type' => 'string',
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.administrator.name'
			)
		)
	),
	array(
		'question' => 'Please enter the site administrator\'s email address',
		'notes' => array(
			'This is displayed on error pages.',
			'If you do not wish to use an individual\'s email address, use an alias or leave this blank.'
		),
		'type' => 'string',
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.administrator.email'
			)
		)
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
				'required' => true,
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'emailKey'
					)
				)
			),
			array(
				'question' => 'Please enter the email address',
				'type' => 'string',
				'required' => true,
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'emailValue'
					)
				)
			),
			array(
				'question' => 'Please enter the override email address for development environments',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'emailDevOverride'
					)
				)
			)
		),
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'configuration.json',
				'key' => 'site.email.%emailKey%',
				'value' => '%emailValue%'
			),
			array(
				'type' => 'data',
				'name' => 'configuration.development.json',
				'key' => 'site.email.%emailKey%',
				'value' => '%emailDevOverride%'
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
		'default' => 'no',
		'actions' => array(
			array(
				'type' => 'store',
				'name' => 'generateHtaccess'
			)
		)
	),
	array(
		'question' => 'Do you want to include and activate X-Sendfile?',
		'notes' => array(
			'X-Sendfile is any web server module or function which allows direct serving of static files after processing by dynamic scripts like PHP.',
			'Examples exist for Apache and for other web servers, but not for the PHP built-in web server.  The header name is configurable for different implementations.',
			'It is perfectly safe to enable this even if X-Sendfile is not available, it will only be used if it is both configured and detected.'
		),
		'type' => 'boolean',
		'default' => 'yes',
		'dependents' => array(
			array(
				'question' => 'Do you want to override the name of the X-Sendfile header?',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'system.file-response.header'
					)
				)
			)
		),
		'actions' => array(
			array(
				'type' => 'store',
				'name' => 'activateXSendfile'
			)
		)
	),
	array(
		'question' => 'Which default page template do you want to include?',
		'notes' => array(
			'This forms the basis of all pages within the site by default.',
			'Any number of templates can be added later, meaning different URLs (or URL pattern matches) can be presented using different page layouts or styles.'
		),
		'type' => 'string',
		'default' => 'recommended',
		'options' => array(
			array(
				'value' => null,
				'label' => 'None - requires further configuration, the site will not work without a default template'
			),
			array(
				'value' => 'minimal',
				'label' => 'Minimal HTML5 template'
			),
			array(
				'value' => 'minimal-xml',
				'label' => 'Minimal HTML5 template with XML style'
			),
			array(
				'value' => 'recommended',
				'label' => 'Recommended HTML5 starting point'
			),
			array(
				'value' => 'recommended-xml',
				'label' => 'Recommended HTML5 starting point with XML style'
			)
		),
		'actions' => array(
			array(
				'type' => 'store',
				'name' => 'templateFileName'
			)
		)
	),
	// TODO Actually build the database instance (?)
	array(
		'question' => 'Do you need a database?',
		'notes' => array(
			'Any database type supported by Doctrine is supported by Sitegear',
			'This option must be selected if you are using any modules that require Doctrine module, otherwise additional configuration is required',
			'This does not actually build the database, this should be done through tools such as phpMyAdmin'
		),
		'type' => 'boolean',
		'default' => 'no',
		'dependents' => array(
			array(
				'question' => 'Please enter the database driver type',
				'type' => 'string',
				'default' => 'pdo_mysql',
				'required' => true,
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'modules.doctrine.connection.driver'
					)
				)
			),
			array(
				'question' => 'Please enter the database name',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'modules.doctrine.connection.dbname'
					)
				)
			),
			array(
				'question' => 'Please enter the username',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'modules.doctrine.connection.user'
					)
				)
			),
			array(
				'question' => 'Please enter the password',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'modules.doctrine.connection.password'
					)
				)
			)
		)
	),
/** TODO Support this
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
				'default' => 'sitegear.log',
				'actions' => array(
					array(
						'type' => 'data',
						'name' => 'configuration.json',
						'key' => 'modules.doctrine.connection.dbname'
					)
				)
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
**/
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
				'question' => 'Please enter the user\'s email address (username)',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'userEmail'
					)
				)
			),
			array(
				'question' => 'Please enter the user\'s password',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'userPassword'
					)
				)
			),
			array(
				'question' => 'Please enter the user\'s real (display) name',
				'type' => 'string',
				'actions' => array(
					array(
						'type' => 'store',
						'name' => 'userName'
					)
				)
			)
		),
		'actions' => array(
			array(
				'type' => 'data',
				'name' => 'users.json',
				'value' => array(
					'active' => true,
					'data' => array(
						'email' => '%userEmail%',
						'password' => '%userPassword%',
						'name' => '%userName%'
					)
				)
			)
		)
	)
);
