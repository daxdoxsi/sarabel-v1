<?php

# Loading paths
require '../app/config/files/paths.conf.php';

# Loading config files
require PATH_CONFIG.'../config_loader.conf.php';

# Custom Error Handler
set_error_handler("myErrorHandler");

# Route conversion
$route_params = [];
$request_uri = request_uri(false);

# Loading route
if ( isset( $routes[$request_uri] ) ) {

    # Loading initializers
    if ( is_array($routes[$request_uri]['initializers']) && count($routes[$request_uri]['initializers']) > 0 ) {
        foreach($routes[$request_uri]['initializers'] as $initializer) {
            if ($initializer != '') {
                require PATH_INITIALIZERS.$initializer.'.inc.php';
            }
        }
    }

    # Loading controller
    controller($routes[ $request_uri ]);
}
else {

    # Page not found
    controller($routes['page-not-found']);

}