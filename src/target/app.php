<?php
/**
 * Sitegear application object definition bootstrap.
 */
return call_user_func(function() {

	/**
	 * Create the engine object using lower level bootstrap.
	 * @var \Sitegear\Engine\SitegearEngine $engine
	 */
	$engine = require 'engine.php';

	/**
	 * Create Symfony event dispatcher and bridge with Sitegear.
	 */
	$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
	$dispatcher->addSubscriber(new \Sitegear\Base\Engine\KernelEvent\EngineBootstrapListener($engine));
	$dispatcher->addSubscriber(new \Sitegear\Base\Engine\KernelEvent\EngineRouterListener($engine));
	$dispatcher->addSubscriber(new \Sitegear\Base\Engine\KernelEvent\EngineRendererListener($engine));
	$dispatcher->addSubscriber(new \Sitegear\Base\Engine\KernelEvent\EngineExceptionListener($engine));

	/**
	 * Create Sitegear controller resolver.
	 */
	$resolver = new \Sitegear\Engine\Controller\SitegearControllerResolver($engine);

	/**
	 * Create and return the application object, which is simply a Symfony HttpKernel instance.
	 */
	return new \Symfony\Component\HttpKernel\HttpKernel($dispatcher, $resolver);

});
