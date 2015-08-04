<?php
// If uninstall not called from WordPress exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

$url = admin_url( 'plugins.php' );
wp_die( 'Oops! You can\'t uninstall main plugin. <a href="'.$url.'" title="Return to plugins">Return to plugins.</a>' );
