<?php
/**
 * Sitegear boilerplate front controller.
 */
call_user_func(function() {

	/**
	 * Setup PSR-0 class autoloading.
	 */
	require_once dirname(__DIR__) . '/vendor/autoload.php';

	/**
	 * Create the request object, which represents the HTTP request including the URL, parameters, headers, etc.
	 */
	$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

	/**
	 * Get the application instance.
	 * Invoke the standard Symfony HttpKernelInterface processing cycle.
	 * @var \Symfony\Component\HttpKernel\HttpKernelInterface $app
	 */
	$app = require_once dirname(__DIR__) . '/app.php';

	/**
	 * Invoke the standard Symfony HttpKernelInterface processing cycle.
	 */
	$app->handle($request)->prepare($request)->send();

});
