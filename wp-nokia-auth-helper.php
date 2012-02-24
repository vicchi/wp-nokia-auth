<?php

class WPNokiaAuthHelper {
	static	$instance;
	
	const OPTIONS = 'wp_nokia_auth_settings';
	const ID_KEY = 'app_id';
	const TOKEN_KEY = 'app_token';
	const SECRET_KEY = 'app_secret';
	
	const CONTEXT_STUB = 'nokia.maps.util.ApplicationContext.set (
	{
		"appId": "%s",
		"authenticationToken": "%s"
	}
	);';
	
	function __construct () {
		self::$instance = $this;
	}
	
	public function get_id () {
		return $this->get_option (self::ID_KEY);
	}
	
	public function get_token () {
		return $this->get_option (self::TOKEN_KEY);
	}
	
	public function get_secret () {
		return $this->get_option (self::SECRET_KEY);
	}
	
	public function get_maps_context ($add_script=false) {
		$settings = get_option (self::OPTIONS);
		$context = sprintf (self::CONTEXT_STUB, $settings[self::ID_KEY], $settings[self::TOKEN_KEY]);
		
		if ($add_script) {
			return '<script type="text/javascript">' . "\n"
				. $context . "\n"
				. '</script>' . "\n";
		}

		return $context;
	}
	
	private function get_option ($key='') {
		$settings = get_option (self::OPTIONS);
		if (isset ($settings[$key])) {
			return $settings[$key];
		}
		return $settings;
	}
	
}	// end-class WPNokiaAuthHelper
?>