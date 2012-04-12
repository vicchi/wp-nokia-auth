<?php
/*
Plugin Name: WP Nokia Auth
Plugin URI: http://www.vicchi.org/codeage/wp-nokia-auth
Description: A WordPress plugin to manage your Nokia Location API credentials across all themes and plugins on a site.
Version: 1.0
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-nokia-auth
*/

define ('WPNOKIAAUTH_PATH', plugin_dir_path (__FILE__));
define ('WPNOKIAAUTH_URL', plugin_dir_url (__FILE__));

require_once (WPNOKIAAUTH_PATH . '/wp-plugin-base/wp-plugin-base.php');
require_once (WPNOKIAAUTH_PATH . '/wp-nokia-auth-helper.php');

class WPNokiaAuth extends WP_PluginBase {
	static $instance;
	
	const VERSION = 10;
	const OPTIONS = 'wp_nokia_auth_settings';
	const ID = 'wp-nokia-auth';
	
	/**
	 * Class constructor
	 */

	function __construct () {
		self::$instance = $this;
		
		$this->hook ('plugins_loaded');
	}

	/**
	 * "plugins_loaded" action hook; called after all active plugins and pluggable functions
	 * are loaded.
	 *
	 * Adds admin actions.
	 */
	
	function plugins_loaded () {
		register_activation_hook (__FILE__, array ($this, 'add_settings'));

		$this->hook ('init');
		
		if (is_admin ()) {
			$this->hook ('admin_init');
			$this->hook ('admin_menu');
			$this->hook ('admin_print_scripts');
			$this->hook ('admin_print_styles');
			
			add_filter ('plugin_action_links_' . plugin_basename (__FILE__),
				array ($this, 'admin_settings_links'));
		}
	}

	/**
	 * Queries the back-end database for WP Nokia Auth settings and options.
	 *
	 * @param string $key Optional settings/options key name; if specified only the value
	 * for the key will be returned, if the key exists, if omitted all settings/options
	 * will be returned.
	 * @return mixed If $key is specified, a string containing the key's settings/option 
	 * value is returned, if the key exists, else an empty string is returned. If $key is
	 * omitted, an array containing all settings/options will be returned.
	 */
	
	function get_option () {
		$num_args = func_num_args ();
		$options = get_option (self::OPTIONS);

		if ($num_args > 0) {
			$args = func_get_args ();
			$key = $args[0];
			$value = "";
			if (isset ($options[$key])) {
				$value = $options[$key];
			}
			return $value;
		}
		
		else {
			return $options;
		}
	}

	/**
	 * Adds/updates a settings/option key and value in the back-end database.
	 *
	 * @param string key Settings/option key to be created/updated.
	 * @param string value Value to be associated with the specified settings/option key
	 */
	
	function set_option ($key , $value) {
		$options = get_option (self::OPTIONS);
		$options[$key] = $value;
		update_option (self::OPTIONS , $options);
	}

	/**
	 * "init" action hook; called to initialise the plugin
	 */
	
	function init () {
		$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
		load_plugin_textdomain ('wp-nokia-auth', false, $lang_dir);
	}
	
	/**
	 * plugin activation / "activate_pluginname" action hook; called when the plugin is
	 * first activated.
	 *
	 * Defines and sets up the default settings and options for the plugin. The default set
	 * of options are configurable, at activation time, via the
	 * 'wp_nokia_auth_default_settings' filter hook.
	 */

	function add_settings () {
		$settings = $this->get_option ();
		if (!is_array ($settings)) {
			$settings = apply_filters ('wp_nokia_auth_default_settings',
				array (
					'installed' => 'on',
					'version' => VERSION,
					'app_id' => '',
					'app_token' => '',
					'app_secret' => ''
					)
				);
			update_option (self::OPTIONS, $settings);
		}
	}
	
	/**
	 * "admin_init" action hook; called after the admin panel is initialised.
	 */

	function admin_init () {
		$this->admin_upgrade ();
		
		$app_id = $this->get_option ('app_id');
		if (empty ($app_id)) {
			$this->hook ('admin_notices');
		}
	}
	
	/**
	 * "plugin_action_links_'plugin-name'" action hook; called to add a link to the plugin's
	 * settings/options panel.
	 */

	function admin_settings_links ($links) {
		$settings_link = '<a href="options-general.php?page=wp-nokia-auth/wp-nokia-auth.php">'
			. __('Settings')
			. '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

	/**
	 * Called in response to the "admin_init" action hook; checks the current set of
	 * settings/options and upgrades them according to the new version of the plugin.
	 */

	function admin_upgrade () {
		$settings = null;
		$upgrade_settings = false;
		$current_plugin_version = null;
		
		$settings = $this->get_option ();
		if (is_array ($settings) &&
				!empty ($settings['version']) &&
				$settings['version'] == VERSION) {
			return;
		}
		
		if (!is_array ($settings)) {
			$this->add_settings ();
		}
		
		else {
			if (!empty ($settings['version'])) {
				$current_plugin_version = $settings['version'];
			}
			
			else {
				$current_plugin_version = '00';
			}
			
			switch ($current_plugin_version) {
				case '00':
					$settings['version'] = VERSION;
					$upgrade_settings = true;
					
				default:
					break;
			}	// end-switch (...)
			
			if ($upgrade_settings) {
				update_option (self::OPTIONS, $settings);
			}
		}
	}
	
	/**
	 * Called from the "admin_init" action hook; displays a notice for as long as the plugin
	 * remains unconfigured.
	 */
	
	function admin_notices () {
		if (current_user_can ('manage_options')) {
			$content = sprintf (
				__('You need to add your Nokia Location API authentication details to WP Nokia  Auth; you can go to the <a href="%s">WP Nokia Auth Settings And Options page</a> to do this now'),
				admin_url ('options-general.php?page=wp-nokia-auth/wp-nokia-auth.php'));
			echo '<div class="wp-nokia-auth-error">' . $content . '</div>';
		}
	}
	
	/**
	 * "admin_menu" action hook; called after the basic admin panel menu structure is in
	 * place.
	 */

	function admin_menu () {
		if (function_exists ('add_options_page')) {
			$page_title = $menu_title = __('WP Nokia Auth');
			add_options_page ($page_title,
					$menu_title,
					'manage_options',
					__FILE__,
					array ($this, 'admin_display_settings'));
		}
	}

	/**
	 * "admin_print_scripts" action hook; called to enqueue admin specific scripts.
	 */

	function admin_print_scripts () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'], 'wp-nokia-auth')) {
			wp_enqueue_script ('postbox');
			wp_enqueue_script ('dashboard');
		}
	}
	
	/**
	 * "admin_print_styles" action hook; called to enqueue admin specific CSS.
	 */

	function admin_print_styles () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'], 'wp-nokia-auth')) {
			wp_enqueue_style ('dashboard');
			wp_enqueue_style ('global');
			wp_enqueue_style ('wp-admin');
			//wp_enqueue_style ('farbtastic');
			wp_enqueue_style ('wp-nokia-auth-admin',
				WPNOKIAAUTH_URL . 'css/wp-nokia-auth-admin.css');
		}
	}
	
	/**
	 * add_options_page() callback function; called to emit the plugin's settings/options
	 * page.
	 */

	function admin_display_settings () {
		$settings = $this->admin_save_settings ();
		
		$wrapped_content = '';
		$auth_content = '';
		$maps_content = '';
		$places_content = '';
	
		$auth_content .= '<p><strong>' . __('Overview') . '</strong><br />'
			. sprintf (__('You can obtain Nokia Location API credentials from the <a href="%s">Nokia API Registration</a> site.'), 'http://api.developer.nokia.com/')
			. '</p>';
			
		$auth_content .= '<p><strong>' . __('Application ID') . '</strong><br />
			<input type="text" name="wp_nokia_app_id" id="wp_nokia_app_id" value="' . $settings['app_id'] . '" size="35" /><br />
			<small>' . __('Enter your registered Nokia Location API App ID') . '</small></p>';
			
		$auth_content .= '<p><strong>' . __('Application Token') . '</strong><br />
			<input type="text" name="wp_nokia_app_token" id="wp_nokia_app_token" value="' . $settings['app_token'] . '" size="35" /><br />
			<small>' . __('Enter your registered Nokia Location API App Token') . '</small></p>';
			
		$auth_content .= '<p><strong>' . __('Application Secret') . '</strong><br />
			<input type="text" name="wp_nokia_app_secret" id="wp_nokia_app_secret" value="' . $settings['app_secret'] . '" size="35" /><br />
			<small>' . __('Enter your registered Nokia Location API App Secret') . '</small></p>';

		if (!empty ($settings['app_id']) &&
				!empty ($settings['app_token']) &&
				!empty ($settings['app_secret'])) {
			$helper = new WPNokiaAuthHelper;
			$context = $helper->get_maps_context (true);
			
			$maps_content .= '<p>'
				. __('To use your Nokia Location API authentication tokens simply copy and paste the code below into your WordPress theme or plugin. Alternatively you can use the <code>WPNokiaAuthHelper</code> PHP class that ships with this plugin; see <code>wp-nokia-auth-helper.php</code> for more information.')
				. '</p>';
				
			$maps_content .= '<textarea class="wp-nokia-auth-code" cols="70" rows="8">' . htmlspecialchars ($context) . '</textarea>';
			
			$places_content .= '<p>'
				. __('The Nokia Places Javascript API does not currently require authentication.')
				. '</p>';
		}

		if (function_exists ('wp_nonce_field')) {
			$wrapped_content .= wp_nonce_field (
				'wp-nokia-auth-update-options',
				'_wpnonce',
				true,
				false);
		}
		
		$wrapped_content .= $this->admin_postbox ('wp-nokia-auth-settings',
			__('Nokia Location APIs Registration Information'),
			$auth_content);
			
		if (!empty ($maps_content)) {
			$wrapped_content .= $this->admin_postbox ('wp-nokia-auth-maps-context',
				__('Nokia Maps API Authentication Context'),
				$maps_content);
			
			$wrapped_content .= $this->admin_postbox ('wp-nokia-auth-places-context',
				__('Nokia Places API Authentication Context'),
				$places_content);
		}
			
		$this->admin_wrap (__('WP Nokia Auth Settings And Options'), $wrapped_content);
	}
	
	/**
	 * Extracts a specific settings/option field from the $_POST array.
	 *
	 * @param string field Field name.
	 * @return string Contents of the field parameter if present, else an empty string.
	 */

	function admin_option ($field) {
		return (isset ($_POST[$field]) ? $_POST[$field] : '');
	}
	
	/**
	 * Verifies and saves the plugin's settings/options to the back-end database.
	 */

	function admin_save_settings () {
		$settings = $this->get_option ();
		
		if (!empty ($_POST['wp_nokia_auth_option_submitted'])) {
			if (strstr ($_GET['page'], 'wp-nokia-auth') &&
					check_admin_referer ('wp-nokia-auth-update-options')) {
						
				$settings['app_id'] = html_entity_decode ($this->admin_option ('wp_nokia_app_id'));
				$settings['app_token'] = html_entity_decode ($this->admin_option ('wp_nokia_app_token'));
				$settings['app_secret'] = html_entity_decode ($this->admin_option ('wp_nokia_app_secret'));
				
				echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
				_e('WP Nokia Auth Settings And Options Updated.');
				echo "</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
				
				update_option (self::OPTIONS, $settings);
			}
		}
		
		$settings = $this->get_option ();
		return $settings;
	}
	
	/**
	 * Creates a postbox entry for the plugin's admin settings/options page.
	 *
	 * @param string id CSS id for this postbox
	 * @param string title Title string for this postbox
	 * @param string content HTML content for this postbox
	 * @return string Wrapped postbox content.
	 */

	function admin_postbox ($id, $title, $content) {
		$handle_title = __('Click to toggle');

		$postbox_wrap = '<div id="' . $id . '" class="postbox">';
		$postbox_wrap .= '<div class="handlediv" title="'
			. $handle_title
			. '"><br /></div>';
		$postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
		$postbox_wrap .= '<div class="inside">' . $content . '</div>';
		$postbox_wrap .= '</div>';

		return $postbox_wrap;
	}

	/**
	 * Wrap up all the constituent components of the plugin's admin settings/options page.
	 *
	 * @param string tab Settings/options tab context name
	 * @param string title Title for the plugin's admin settings/options page.
	 * @param string content HTML content for the plugin's admin settings/options page.
	 * @return string Wrapped HTML content
	 */

	function admin_wrap ($title, $content) {
	?>
	    <div class="wrap">
	        <h2><?php echo $title; ?></h2>
	        <form method="post" action="">
	            <div class="postbox-container wp-nokia-auth-postbox-settings">
	                <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
	                        echo $content;
	                    ?>
	                    <p class="submit"> 
	                        <input type="submit" name="wp_nokia_auth_option_submitted" class="button-primary" value="<?php _e('Save Changes')?>" /> 
	                    </p> 
	                    <br /><br />
	                    </div>
	                  </div>
	                </div>
	                <div class="postbox-container wp-nokia-auth-postbox-sidebar">
	                  <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
							echo $this->admin_help_and_support ();
							echo $this->admin_show_colophon ();
	                    ?>
	                    </div>
	                </div>
	            </div>
	        </form>
	    </div>
	<?php	
	}

	/**
	 * Emits the plugin's help/support side-box for the plugin's admin settings/options page.
	 */

	function admin_help_and_support () {
		$email_address = antispambot ("gary@vicchi.org");

		$content = '<p>'
			. __('For help and support with WP Nokia Auth, here\'s what you can do:')
			. '<ul>'
			. '<li>'
			. sprintf (__('Firstly ... take a look at <a href="%s">this</a> before firing off a question.'), 'http://www.vicchi.org/2012/03/31/asking-for-wordpress-plugin-help-and-support-without-tears/')
			. '</li>'
			. '<li>'
			. __('Then ... ask a question on the <a href="http://wordpress.org/tags/wp-nokia-auth?forum_id=10">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.')
			. '</li>'
			. '<li>'
			. __('Or ... ask me a question on Twitter; I\'m <a href="http://twitter.com/vicchi">@vicchi</a>.')
			. '</li>'
			. '<li>'
			. sprintf (__('Or ... drop me an <a href="mailto:%s">email </a>instead.'), $email_address)
			. '</li>'
			. '</ul>'
			. '</p>'
			. '<p>'
			. __('But ... help and support is a two way street; here\'s what you might want to do:')
			. '<ul>'
			. '<li>'
			. sprintf (__('If you like this plugin and use it on your WordPress site, or if you write about it online, <a href="http://www.vicchi.org/codeage/wp-nokia-auth/">link to the plugin</a> and drop me an <a href="mailto:%s">email</a> telling me about this.'), $email_address)
			. '</li>'
			. '<li>'
			. __('Rate the plugin on the <a href="http://wordpress.org/extend/plugins/wp-nokia-auth/">WordPress plugin repository</a>.')
			. '</li>'
			. '<li>'
			. __('WP Nokia Auth is both free as in speech and free as in beer. No donations are required; <a href="http://www.vicchi.org/codeage/donate/">here\'s why</a>.')
			. '</li>'
			. '</ul>'
			. '</p>';

		return $this->admin_postbox ('wp-nokia-auth-support', __('Help &amp; Support'), $content);
	}

	/**
	 * Emits the plugin's colophon side-box for the plugin's admin settings/options page.
	 */

	function admin_show_colophon () {
		$content = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p>';

		$content .= '<p>'
			. __('For the inner nerd in you, the latest version of WP Nokia Auth was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.2 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.')
			. '<p>';

		$content .= '<p>'
			. __('The official home for WP Nokia Auth is on <a href="http://www.vicchi.org/codeage/wp-nokia-auth/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-nokia-auth/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-nokia-auth">GitHub</a> to download, fork and otherwise hack around.')
			. '<p>';

		return $this->admin_postbox ('wp-nokia-auth-colophon', __('Colophon'), $content);
	}
}	// end-class WPNokiaAuth

$_wp_nokia_auth_instance = new WPNokiaAuth;

?>