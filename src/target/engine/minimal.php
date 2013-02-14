<?php
/**
 * Sitegear engine object definition bootstrap.
 */
return call_user_func(function() {

	/**
	 * Define the application environment setting and path to the configuration file.
	 */
	$environment = defined('APPLICATION_ENV') ? APPLICATION_ENV : (getenv('APPLICATION_ENV') ?: null);
	$config = __DIR__ . '/config/configuration.json';

	/**
	 * Create and return the engine object.  There are many default implementations being used here that can be
	 * overridden using constructor-based dependency injection.  See the API documentation for details.
	 */
	$engine = new \Sitegear\Core\Engine\Engine(__DIR__, $environment);
	return $engine->configure($config);

});
