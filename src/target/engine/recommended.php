<?php
/**
 * Sitegear engine object definition bootstrap.
 */
return call_user_func(function() {

	/**
	 * Setup logging (optional).
	 */
	$logger = new \Monolog\Logger('sitegear');
	$streamHandler = new \Monolog\Handler\StreamHandler(__DIR__ . '/sitegear.log', \Monolog\Logger::INFO);
	$streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter("[%datetime%] [%channel%] [%level_name%] %message% %context%\n"));
	$logger->pushHandler($streamHandler);
	$logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
	\Sitegear\Util\LoggerRegistry::getInstance()->register($logger);

	/**
	 * Define the application environment setting and path to the configuration file.
	 */
	$environment = defined('APPLICATION_ENV') ? APPLICATION_ENV : (getenv('APPLICATION_ENV') ?: null);
	$config = __DIR__ . '/config/configuration.json';

	/**
	 * Create and return the engine object.  There are many default implementations being used here that can be
	 * overridden using constructor-based dependency injection.  See the API documentation for details.
	 */
	$engine = new \Sitegear\Engine\SitegearEngine(__DIR__, $environment);
	return $engine->configure($config);

});
