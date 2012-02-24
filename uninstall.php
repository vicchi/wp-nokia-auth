<?php

if (defined ('WP_UNINSTALL_PLUGIN')) {
	delete_option ('wp_nokia_auth_settings');
}

else {
	exit ();
}

?>