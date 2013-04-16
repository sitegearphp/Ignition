<?php
/**
 * Sitegear command line interface configuration script; definition of $helperSet is required, see:
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html
 */
$helperSet = call_user_func(function() {

	/**
	 * Create the engine object using lower level bootstrap.
	 * @var \Sitegear\Engine\SitegearEngine $engine
	 */
	$engine = require 'engine.php';

	/**
	 * Create the helper set.
	 */
	return new \Symfony\Component\Console\Helper\HelperSet(array(
		'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($engine->doctrine()->getEntityManager())
	));

});
