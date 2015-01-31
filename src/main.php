<?php
/**
 * Main class for processing license activations/deactivations
 *
 * @package calderawp\licensing
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

/**
 * @version 1.0.0
 */

namespace calderawp\licensing;

/**
 * Class main
 *
 * @package calderawp\licensing
 */
class main {

	/**
	 * The params passed to this class
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * The name of the option that stores the license key.
	 *
	 * @var string
	 */
	public $license_key_option_name;

	/**
	 * The name of the option that stores the license status.
	 *
	 * @var string
	 */
	public $license_status_option_name;

	/**
	 * The AJAX action used for processing license codes.
	 *
	 * @var string
	 */
	public $ajax_action;

	/**
	 * The field containing the nonce when processing license codes.
	 *
	 * @var string
	 */
	public $nonce_field;

	/**
	 * The name of the action for the nonce.
	 *
	 * @var string
	 */
	public $nonce_action;

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 *
	 * @param array $params {
	 *
	 *      @type string store_url URL for the store.
	 *      @type string version Plugin version.
	 *      @type string item_name The plugin's name.
	 *      @type string author The plugin's author.
	 *      @type string license_key_option_name The name of the option that stores the license key.
	 *      @type string license_status_option_name The name of the option that stores the license status.
	 *      @type string plugin_root_file The file path for the file containing the plugin header.
	 *      @type string ajax_action The AJAX action used for processing license codes.
	 *      @type string nonce_field The field containing the nonce when processing license codes.
	 *      @type string nonce_action The name of the action for the nonce.
	 *
	 * }
	 */
	function __construct( $params ) {
		$this->params = $params;
		$this->nonce_action = $this->params[ 'nonce_action' ];
		$this->nonce_field = $this->params[ 'nonce_field' ];
		$this->ajax_action = $this->params[ 'ajax_action' ];
		$this->license_key_option_name = $this->params[ 'license_key_option_name' ];
		$this->license_status_option_name = $this->params[ 'license_status_option_name' ];

		$ajax_action = 'wp_ajax_'.$params[ 'ajax_action' ];
		add_action( $ajax_action, array( $this, 'update' ) );
		add_action( 'admin_init', array( $this, 'register_license_key_option' ) );
	}

	/**
	 * Initialize the EDD updater class.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function updater_class(  ) {
		if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {

			include( dirname( __FILE__ ) . '/includes/EDD_SL_Plugin_Updater.php' );
		}

		$license_key = trim( get_option( $this->params[ 'license_key_option_name' ] ) );

		$edd_updater = new EDD_SL_Plugin_Updater( $this->params[ 'store_url' ], $this->params[ 'plugin_root_file' ], array(
				'version' 	=> $this->params[ 'version' ],
				'license' 	=> $license_key,
				'item_name' => $this->params[ 'author' ],
			)

		);

	}

	/**
	 * Process the license activation or deactivation
	 *
	 * @since 1.0.0
	 */
	public function update() {
		$license_data = false;
		$data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING) ;

		$error_response = array(
			'success' => false,
			'message' => __( 'License Could Not Be Processed. Please try again' ),
			'license' => 'false'
		);

		if ( ! is_array( $data ) || empty( $data ) || ! wp_verify_nonce(  $data[ $this->params[ 'nonce_field' ] ], $this->params[ 'nonce_action' ] ) ){
			wp_send_json_error( $error_response );
		}

		if ( isset( $data[ 'code'] ) ) {
			$code = $data[ 'code' ];
			if ( isset( $data[ 'license_action' ] ) ) {
				if ( 'activate' === $data[ 'license_action' ]  ) {
					$license_data = $this->activate_license( $code );
				} else {
					$license_data = $this->deactivate_license( $code );
				}
			}

			nocache_headers();
			if ( is_object( $license_data ) ) {

				if ( isset( $license_data->success ) && $license_data->success ) {
					$license_data = (array) $license_data;
					$license_data[ 'license' ] = 'activated';
					wp_send_json_success( $license_data  );
				}elseif( isset( $license_data->license ) && 'deactivated' == $license_data->license ) {
					$license_data = (array) $license_data;
					$license_data[ 'license' ] = 'deactivated';
					wp_send_json_success( $license_data );
				}
				else{
					wp_send_json_error(  $license_data );
				}

			}

			wp_send_json_error( $error_response );

		}

	}

	/**
	 * Activate License
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param string|bool $license Optional. The license code. If false, will get from DB.
	 *
	 * @return bool|void
	 */
	protected function activate_license( $license = false ) {
		$response = $this->api_request( 'activate_license', $license );

		if ( false == $response || is_wp_error( $response ) ) {
			return false;

		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $license_data->success ) && $license_data->success ) {
			update_option( $this->params['license_key_option_name'], $license );
			update_option( $this->params['license_status_option_name'], true );
		}else{
			return false;

		}

		return $license_data;

	}

	/**
	 * Deactivate license
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param string|bool $license Optional. The license code. If false, will get from DB.
	 *
	 * @return bool|void
	 */
	protected function deactivate_license( $license = false ) {
		$response = $this->api_request( 'deactivate_license', $license );

		if ( false == $response || is_wp_error( $response ) ) {
			return false;

		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );


		if( isset( $license_data->license ) && $license_data->license == 'deactivated' ) {
			delete_option( $this->params[ 'license_key_option_name' ] );
			delete_option( $this->params[ 'license_status_option_name' ] );
		}
		else{
			return false;
		}

		return $license_data;

	}


	/**
	 * Register option for the license key
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @uses 'admin_init' action
	 */
	public function register_license_key_option() {
		register_setting( 'cep_licenses', $this->params[ 'license_key_option_name' ], array( $this, 'sanitize_license' ) );
	}


	/**
	 * Sanitization callback for register_setting() used in $this->register_license_key_option()
	 *
	 * @since 1.0.0
	 *
	 * @param string $new The new license key.
	 *
	 * @return mixed|bool|string
	 */
	public function sanitize_license( $new ) {
		$old = get_option( $this->params[ 'license_key_option_name' ] );
		if( $old && $old != $new ) {
			delete_option( $this->params[ 'license_status_option_name' ] );
		}

		return $new;

	}

	/**
	 * Check a license.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param string|bool $license Optional. The license code. If false, will get from DB.
	 *
	 * @return bool
	 */
	protected function check_license( $license = false ) {

		global $wp_version;

		$response = $this->api_request( 'check_license', $license );

		if ( false == $response || is_wp_error( $response ) ) {
			return false;

		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( isset( $license_data->license ) && $license_data->license == 'valid' ) {
			echo 'valid';
			exit;

		} else {
			echo 'invalid';
			exit;

		}

	}

	/**
	 * Make request to licensing API
	 *
	 * @since 1.0.0
	 *
	 * @param string $edd_action Action to take on remote server activate_license|deactivate_license|check_license
	 * @param string|bool $license Optional. The license code. If false, will get from DB.
	 *
	 * @return array|\WP_Error
	 */
	protected function api_request( $edd_action, $license = false ) {
		if ( ! in_array( $edd_action, array( 'activate_license', 'deactivate_license', 'check_license' ) ) )  {
			return false;
		}

		if ( ! $license ) {
			$license = get_option( $this->params[ 'license_key_option_name' ] );
		}

		$license = trim( $license );


		$api_params = array(
			'edd_action'=> $edd_action,
			'license' 	=> $license,
			'item_name' => urlencode( $this->params[ 'item_name' ] ),
			'url'       => home_url()
		);

		$url      = add_query_arg( $api_params, $this->params[ 'store_url' ] );
		$response = wp_remote_get(
			$url, array(
				'timeout'   => 15,
				'sslverify' => false
			)
		);

		return $response;

	}

}
