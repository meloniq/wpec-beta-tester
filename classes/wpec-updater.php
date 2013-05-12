<?php
class WPEC_GitHub_Updater extends WP_GitHub_Updater {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
		add_action( 'init', array( &$this, 'load_textdomain' ) );

		$this->config = $this->create_config_data();
		$this->config['version'] = get_option( 'wpec_beta_tester_sha', false );
		$this->config['new_version'] = $this->get_latest_commit_sha();
		
		parent::__construct( $this->config );

	}


	/**
	 * Creates an array with configuration data 
	 *
	 * @since 1.0
	 * @return array
	 */
	function create_config_data() {
		global $wp_version;

		$source_data = array(
			'user' => 'wp-e-commerce',
			'repo' => 'WP-e-Commerce',
			'branch' => 'master',
		);

		$source = get_option( 'wpec_beta_tester_source' );
		$pull_id = get_option( 'wpec_beta_tester_pull' );
		if ( $pulls = $this->get_pulls_source_data() )
			$source_data = $pulls;

		extract( $source_data );

		$config = array(
			'slug' => trailingslashit( 'wp-e-commerce' ) . 'wp-shopping-cart.php',
			'proper_folder_name' => 'wp-e-commerce',
			'api_url' => 'https://api.github.com/repos/' . $user . '/' . $repo,
			'raw_url' => 'https://raw.github.com/' . $user . '/' . $repo . '/' . $branch,
			'github_url' => 'https://github.com/' . $user . '/' . $repo,
			'zip_url' => 'https://github.com/' . $user . '/' . $repo . '/zipball/' . $branch,
			'sslverify' => false,
			'requires' => $wp_version,
			'tested' => $wp_version,
			'readme' => 'readme.txt',
			'access_token' => '',
		);

		return $config;
	}


	/**
	 * Returns data of repository assigned to pull request
	 *
	 * @since 1.0
	 * @return array|bool
	 */
	function get_pulls_source_data() {
		$source = get_option( 'wpec_beta_tester_source' );
		$pull_id = get_option( 'wpec_beta_tester_pull' );
		
		if ( $source != 'pulls' )
			return false;

		if ( ! is_numeric( $pull_id ) || $pull_id < 1 )
			return false;

		$pull_url = 'https://api.github.com/repos/wp-e-commerce/WP-e-Commerce/pulls/' . $pull_id;
		$response = wp_remote_get( $pull_url, array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) )
			return false;

		$pull_data = json_decode( $response['body'] );

		if ( $pull_data->state != 'open' )
			return false;

		return array(
			'user' => $pull_data->head->repo->owner->login,
			'repo' => $pull_data->head->repo->name,
			'branch' => $pull_data->head->ref,
		);		
	}


	/**
	 * Misc actions that run on 'admin_init'
	 *
	 * @since 1.0
	 * @return void
	 */
	function admin_init() {
		register_setting( 'wpec_beta_tester_options', 'wpec_beta_tester_source', array( &$this, 'validate_setting' ) );
		register_setting( 'wpec_beta_tester_options', 'wpec_beta_tester_pull', 'intval' );
	}


	/**
	 * Adds plugin admin menu
	 *
	 * @since 1.0
	 * @return void
	 */
	function add_admin_menu() {
		add_management_page( __( 'Beta Testing WPEC', 'wpec-beta-tester' ), __( 'WPEC Beta Testing', 'wpec-beta-tester' ), 'update_plugins', 'wpec_beta_tester', array( &$this, 'display_page' ) );
	}


	/**
	 * Loads plugin localization
	 *
	 * @since 1.0
	 * @return void
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'wpec-beta-tester', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Validates source type setting
	 *
	 * @since 1.0
	 * @return string
	 */
	function validate_setting( $setting ) {
		if ( ! in_array( $setting, array( 'master', 'pulls' ) ) )	{
			$setting = 'master';
		}
		// clear last commit transient
		delete_site_transient( 'wpec_beta_tester_last_commit' );

		return $setting;
	}


	/**
	 * Generates settings page for plugin
	 *
	 * @since 1.0
	 * @return void
	 */
	function display_page() {
		if ( ! current_user_can( 'update_plugins' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpec-beta-tester' ) );

		?>
		<div class="wrap"><?php screen_icon(); ?>
			<h2><?php _e( 'Beta Testing WP e-Commerce', 'wpec-beta-tester' ); ?></h2>
			<div class="updated fade">
				<p><?php _e( '<strong>Please note:</strong> Once you have switched your WP e-Commerce to one of these beta versions of software it will not always be possible to downgrade as the database structure maybe updated during the development of a major release.', 'wpec-beta-tester' ); ?></p>	
			</div>
			<div>
				<p>
					<?php printf(
						__( 'By their nature these releases are unstable and should not be used anyplace where your data is important. So please <a href="%1$s">backup your database</a> before upgrading to a test release.', 'wpec-beta-tester' ),
						_x( 'http://codex.wordpress.org/Backing_Up_Your_Database', 'Url to database backup instructions', 'wpec-beta-tester' )
					); ?>
				</p>
				<p>
					<?php printf(
						__( 'Thank you for helping in testing WP e-Commerce please <a href="%s">report any bugs you find</a>.', 'wpec-beta-tester' ),
						_x( 'https://github.com/wp-e-commerce/WP-e-Commerce/issues/new', 'Url to raise a new ticket', 'wpec-beta-tester' )
					); ?>
				</p>
				<p><?php _e( 'By default your WP e-Commerce install uses the stable update stream, to return to this please deactivate this plugin', 'wpec-beta-tester' ); ?></p>

				<form method="post" action="options.php"><?php settings_fields( 'wpec_beta_tester_options' ); ?>
				<fieldset><legend><?php _e( 'Please select the update source you would like this blog to use:', 'wpec-beta-tester' ); ?></legend>
				<table class="form-table">
					<?php $source = get_option( 'wpec_beta_tester_source', 'master' ); ?>
					<tr>
						<th><label><input name="wpec_beta_tester_source"
							id="update-source-master" type="radio" value="master"
							class="tog" <?php checked( 'master', $source ); ?> /><?php _e( 'Test master branch', 'wpec-beta-tester' ); ?></label></th>
						<td><?php _e( 'This contains the work that is occuring on a master branch. This should also be fairly stable.', 'wpec-beta-tester' ); ?></td>
					</tr>
					<tr>
						<th><label><input name="wpec_beta_tester_source"
							id="update-source-pulls" type="radio" value="pulls"
							class="tog" <?php checked( 'pulls', $source ); ?> /><?php _e( 'Test pull request', 'wpec-beta-tester' ); ?></label></th>
						<td><?php _e( 'This contains the work that is occuring on a branch assigned to submitted pull request. This may be unstable at times. <em>Only use this if you really know what you are doing</em>.', 'wpec-beta-tester' ); ?></td>
					</tr>
					<?php $pull_id = get_option( 'wpec_beta_tester_pull' ); ?>
					<tr>
						<th><label><input name="wpec_beta_tester_pull"
							id="update-source-pull-id" type="text" value="<?php echo $pull_id; ?>"
							class="tog" /><?php _e( 'ID', 'wpec-beta-tester' ); ?></label></th>
						<td><?php _e( 'If you have choosen pull requests as source, enter here ID of pull request to test.', 'wpec-beta-tester' ); ?></td>
					</tr>
				</table>
				</fieldset>
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wpec-beta-tester' ); ?>" /></p>
				</form>
				<p><?php printf( __( 'Why don\'t you <a href="%s">head on over and upgrade now</a>.', 'wpec-beta-tester' ), 'update-core.php' ); ?></p>
			</div>
		</div>
<?php
	}


	/**
	 * Obtains and returns SHA of latest commit
	 *
	 * @since 1.0
	 * @return string|bool
	 */
	public function get_latest_commit_sha() {
		$last_commit = get_site_transient( 'wpec_beta_tester_last_commit' );

		if ( ! isset( $last_commit ) || ! $last_commit || '' == $last_commit ) {
			$commits = wp_remote_get(
				path_join( $this->config['api_url'], 'commits' ),
				array(
					'sslverify' => $this->config['sslverify'],
				)
			);
			
			if ( 200 !== wp_remote_retrieve_response_code( $commits ) )
				return false;

			if ( is_wp_error( $commits ) )
				return false;

			$commits     = json_decode( $commits['body'] );
			$last_commit = ( is_array( $commits ) && isset( $commits[0]->sha ) ) ? substr( $commits[0]->sha, 0, 7 ) : '';

			// refresh every hour
			set_site_transient( 'wpec_beta_tester_last_commit', $last_commit, DAY_IN_SECONDS );
		}

		return $last_commit;
	}


	/**
	 * Hook into the plugin update check and connect to github
	 *
	 * @since 1.0
	 * @param object  $transient the plugin data transient
	 * @return object $transient updated plugin data transient
	 */
	public function api_check( $transient ) {

		// Check if the transient contains the 'checked' information
		// If not, just return its value without hacking it
		if ( empty( $transient->checked ) )
			return $transient;

		// if sha's match, let's move on
		if ( $this->config['new_version'] == $this->config['version'] )
			return $transient;

		$new_version = ( $this->get_remote_version() ) ? $this->get_remote_version() . '-' . $this->config['new_version'] : $this->config['new_version'];

		$response = new stdClass;
		$response->new_version = $new_version;
		$response->slug = $this->config['proper_folder_name'];
		$response->url = $this->config['github_url'];
		$response->package = $this->config['zip_url'];

		$transient->response[ $this->config['slug'] ] = $response;

		return $transient;
	}


	/**
	 * Obtains and returns version number from plugin file stored in repository
	 *
	 * @since 1.0
	 * @return string|bool
	 */
	public function get_remote_version() {
		$version = get_site_transient( 'wpec_beta_tester_remote_version' );

		if ( ! isset( $version ) || ! $version || '' == $version ) {

			$query = trailingslashit( $this->config['raw_url'] ) . basename( $this->config['slug'] );
			$query = add_query_arg( array( 'access_token' => $this->config['access_token'] ), $query );

			$raw_response = wp_remote_get( $query, array( 'sslverify' => $this->config['sslverify'] ) );

			if ( is_wp_error( $raw_response ) )
				return false;

			if ( preg_match( '/\* Version: ([0-9\.]*)(.*)/', $raw_response['body'], $matches ) ) {
				$version = $matches[1];
				set_site_transient( 'wpec_beta_tester_remote_version', $version, 60*60*1 );
			}
		}

		return $version;
	}


	/**
	 * Upgrader/Updater
	 * Move & activate the plugin, echo the update message
	 *
	 * @since 1.0
	 * @param boolean $true       always true
	 * @param mixed   $hook_extra not used
	 * @param array   $result     the result of the move
	 * @return array $result the result of the move
	 */
	public function upgrader_post_install( $true, $hook_extra, $result ) {
		global $wp_filesystem;

		// Move
		$proper_destination = WP_PLUGIN_DIR . '/' . $this->config['proper_folder_name'];
		$wp_filesystem->move( $result['destination'], $proper_destination );
		$result['destination'] = $proper_destination;

		// Add SHA to version number
		$plugin_file = WP_PLUGIN_DIR . '/' . $this->config['slug'];
		$file_contents = file_get_contents( $plugin_file );
		$fp = fopen( $plugin_file, 'w' );
		$file_contents = preg_replace( '/\* Version: ([0-9\.]*)(.*)/', "* Version: $1" . '-' . $this->config['new_version'], $file_contents );
		fwrite( $fp, $file_contents );
		fclose( $fp );

		// Activate
		$activate = activate_plugin( WP_PLUGIN_DIR . '/' . $this->config['slug'] );

		// Output the update message
		if ( is_wp_error( $activate ) )
			_e( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'wpec-beta-tester' );
		else
			_e( 'Plugin reactivated successfully.', 'wpec-beta-tester' );

		if ( ! is_wp_error( $activate ) ) 
			update_option( 'wpec_beta_tester_sha', $this->config['new_version'] );

		return $result;
	}

}
