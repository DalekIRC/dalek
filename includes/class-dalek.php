<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://github.com/ValwareIRC
 * @since      1.0.0
 *
 * @package    Dalek
 * @subpackage Dalek/includes
 */

use WPMailSMTP\Vendor\phpseclib3\Common\Functions\Strings;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Dalek
 * @subpackage Dalek/includes
 * @author     Valware <v.a.pond@outlook.com>
 */
class Dalek {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Dalek_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DALEK_VERSION' ) ) {
			$this->version = DALEK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'dalek';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Dalek_Loader. Orchestrates the hooks of the plugin.
	 * - Dalek_i18n. Defines internationalization functionality.
	 * - Dalek_Admin. Defines all hooks for the admin area.
	 * - Dalek_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dalek-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dalek-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-dalek-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-dalek-public.php';

		$this->loader = new Dalek_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Dalek_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Dalek_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Dalek_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Dalek_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_public, 'add_admin_pages');
		$this->loader->add_action( 'widgets_init', $plugin_public, 'add_widget');

		$this->loader->add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), $plugin_public, 'settings_link');

		$this->loader->add_action( 'edit_user_profile', $plugin_public, 'add_suspension_field');
	}

	
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Dalek_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Send a rehash signal to services with a server name =]
	 * JSON-RPC
	 */
	public static function do_rehash($name)
	{
		$name = base64_decode($name);
		$data = '{
			"id": '.rpc_id().',
			"jsonrpc": "2.0",
			"method": "rehash",
			"params": {"server": "'.$name.'"}
		}';
		$resp = json_decode(self::rpc_query($data), true);
		if (isset($resp['result']) && $resp['result'] == "Success")
			dalek_print_notification("Successfully rehashed: $name");
	}
	/**
	 * Query the Dalek RPC webserver 
	 */
	public static function rpc_query($data = NULL)
	{
		if (!$data)
			return;
		$url = "http://localhost:1024/api";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		return $resp;
	}

	/**
	 * Send a request to remove a TKL
	 * JSON-RPC
	 */
	public static function tkl_remove($mask, $type)
	{
		$data = '{
			"id": '.rpc_id().',
			"jsonrpc": "2.0",
			"method": "tkl.del",
			"params": {"mask": "'.$mask.'", "type": "'.$type.'"}
		}';
		$resp = json_decode(self::rpc_query($data), true);
		if (isset($resp['result']) && $resp['result'] == "Success")
			dalek_print_notification("Successfully removed ban against $mask of type $type");
		else
			dalek_print_notification("An error occurred: ".$resp['error']['message']." (Code: ".$resp['error']['code'].")");
	}

	/**
	 * Send oper to a nick
	 */
	public static function send_oper($nick)
	{
		$data = '{
			"id": '.rpc_id().',
			"jsonrpc": "2.0",
			"method": "svso.add",
			"params": {"user": "'.$nick.'"}
		}';
		$resp = json_decode(self::rpc_query($data), true);
		if (isset($resp['result']) && $resp['result'] == "Success")
			dalek_print_notification("Successfully set oper on $nick");
		else
			dalek_print_notification("An error occurred: ".$resp['error']['message']." (Code: ".$resp['error']['code'].")");
	}
	/**
	 * Remove oper from a nick
	 */
	public static function del_oper($nick)
	{
		$data = '{
			"id": '.rpc_id().',
			"jsonrpc": "2.0",
			"method": "svso.del",
			"params": {"user": "'.$nick.'"}
		}';
		$resp = json_decode(self::rpc_query($data), true);
		if (isset($resp['result']) && $resp['result'] == "Success")
			dalek_print_notification("Successfully removed $nick's oper");
		else
			dalek_print_notification("An error occurred: ".$resp['error']['message']." (Code: ".$resp['error']['code'].")");
	}

	public static function convert_mode_to_word(String $modes) : String
	{
		$return = "";

		if (!strlen($modes))
			return $return;

		for ($i = 0; isset($modes[$i]) && ($r = $modes[$i]); ++$i)
		{
			if ($r == "Y")
				$return .= "OperJoin, ";

			elseif ($r == "q")
				$return .= "Owner, ";

			elseif ($r == "a")
				$return .= "Admin, ";

			elseif ($r == "o")
				$return .= "Operator, ";

			elseif ($r == "h")
				$return .= "Halfop, ";

			elseif ($r == "v")
				$return .= "Voice";
			
			else continue;
		}

		$return = rtrim($return,", ");
		return $return;
	}
}



/**
 * Generate a random string for RPC IDs
 */
function rpc_id()
{
	$mt = microtime(false);
	$mt = explode(" ",$mt);
	$mt = $mt[1];
	return (int)random_int(1111,399933) * $mt / 4;
}

