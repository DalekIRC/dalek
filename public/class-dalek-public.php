<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/ValwareIRC
 * @since      1.0.0
 *
 * @package    Dalek
 * @subpackage Dalek/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Dalek
 * @subpackage Dalek/public
 * @author     Valware <v.a.pond@outlook.com>
 */
class Dalek_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dalek_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dalek_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dalek-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'public/templates/styles.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dalek_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dalek_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dalek-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'public/templates/admin-menu-script.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * The settings button in the plugins page
	 */
	public function settings_link($links)
	{
		$link = '<a href="admin.php?page=dalek">Main</a>';
		array_push($links, $link);
		return $links;
	}
	public function add_admin_pages()
	{
		add_menu_page('DalekIRC', 'IRC Overview', 'manage_options', 'dalek_plugin', [$this, 'irc_overview'], 'dashicons-networking', 10);
		add_submenu_page('dalek_plugin', 'Users Manager', 'Users', 'manage_options', 'dalek_users', [$this, 'information_view']);
		add_submenu_page('dalek_plugin', 'Channels Manager', 'Channels', 'manage_options', 'dalek_channels', [$this, 'test']);
		add_submenu_page('dalek_plugin', 'UnrealIRCd Manager', 'UnrealIRCd', 'manage_options', 'dalek_unreal', [$this, 'test']);
		
	}
	public function add_widget()
	{
		register_widget( 'Dalek_Widget' );
	}
	public function irc_overview()
	{
		require_once plugin_dir_path(__FILE__).'templates/overview.php';
	}
	public function information_view()
	{
		require_once plugin_dir_path(__FILE__).'templates/information.php';
	}

	public static function test()
	{
		echo "You what lad";
	}
	public function add_suspension_field($user)
	{
		// Only show this option to users who can delete other users
		if (!current_user_can(is_multisite() ? 'manage_network_users' : 'edit_users'))
			return;
		?>
		<table class="form-table">
			<tbody><h2>Account Suspension</h2>
				<tr>
					<th>
						<label for="dalek_suspend">Suspended</label>
					</th>
					<td>
						<input type="checkbox" name="dalek_suspend" id="dalek_suspend" value="1" <?php checked(1, get_the_author_meta('dalek_suspended', $user->ID)); ?> />
                        <label for="dalek_suspend"><span class="description">Check this to suspend the user.</span></label>
					</td>
				</tr>
				<tr>
					<th>
						<label for="dalek_suspend_reason">Reason</label>
					</th>
					<td>
						<input type="input" name="dalek_suspend_reason" id="dalek_suspend_reason" value="No reason" <?php checked(1, get_the_author_meta('dalek_suspended_reason', $user->ID)); ?> />
					</td>
				</tr>
			<tbody>
		</table>
		<?php
	}
}
