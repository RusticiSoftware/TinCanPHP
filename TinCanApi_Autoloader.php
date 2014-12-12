<?php

/*  Not in original API. Replaces the original PSR-4 autoloader for PHP 5.2 compatibility. */

spl_autoload_register( 'tincanapi_class_loader' );

function tincanapi_class_loader( $class ) {

	$basedir    = dirname( __FILE__ );

	if( preg_match( '/^TinCanAPI_/', $class ) ) {
		
		// Note: To make up for lack of Namespaces, all classes have been prefixed with TinCanAPI_.
		
		$filename = $basedir . str_replace( 'TinCanAPI_', '', '/src/' . $class . '.php' );

		if ( is_readable( $filename ) ) {
			require $filename;

			return true;
		}
	}

	return false;
}