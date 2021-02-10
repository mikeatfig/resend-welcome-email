<?php
/*
Plugin Name: Resend Welcome Email
Plugin URI:  http://www.twitter.com/atwellpub
Description: Quickly send a new welcome email and password reset link for a user through the user's profile edit area.
Version:     1.2.0
Author:      Hudson Atwell
Author URI:  https://codeable.io/developers/hudson-atwell/?ref=99TG1
Text Domain: resend-welcome-email
Domain Path: /languages
*/


/**
 * Security check.
 * Prevent direct access to the file.
 *
 * @since 1.0.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resend_Welcome_Email
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Resend_Welcome_Email' ) ) {

	/**
	 * Class Resend_Welcome_Email
	 */
	class Resend_Welcome_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {

			/* Check user permission */
			if ( ! current_user_can( 'edit_users' ) ) {
				return;
			}

			/* Define constants */
			self::define_constants();

			add_filter( 'user_row_actions',  array( __CLASS__, 'filter_user_row_actions' ), 10, 2 );
			add_filter( 'personal_options', array( __CLASS__, 'personal_options' ), 10, 2 );
			add_filter( 'bulk_actions-users', array( __CLASS__, 'register_bulk_action' ) );
			add_filter( 'handle_bulk_actions-users', array( __CLASS__, 'handle_bulk_action' ), 10, 3 );

			add_action( 'admin_notices', array( __CLASS__, 'bulk_action_admin_notice' ) );


			/* Adds admin listeners for processing actions */
			self::add_admin_listeners();
		}

		/**
		 *  Defines constants.
		 */
		public static function define_constants() {
			define( 'RESEND_WELCOME_EMAIL_CURRENT_VERSION', '1.2.0' );
			define( 'RESEND_WELCOME_EMAIL_FILE', __FILE__ );
			define( 'RESEND_WELCOME_EMAIL_URLPATH', plugins_url( ' ', __FILE__ ) );
			define( 'RESEND_WELCOME_EMAIL_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
		}

		/**
		 * Discovers which tests to run and runs them.
		 *
		 * @param array    $actions
		 * @param \WP_User $user
		 *
		 * @return array
		 */
		public static function filter_user_row_actions( array $actions, WP_User $user ) {
			if ( ! ( $link = self::send_welcome_email_url( $user ) ) ) {
				return $actions;
			}

			$actions['send_welcome_email'] = '<a href="' . $link . '">' . esc_html__( 'Resend Welcome Email', 'resend-welcome-email' ) . '</a>';

			return $actions;
		}

		/**
		 * Register bulk action
		 *
		 * @param array $bulk_actions
		 *
		 * @return array
		 */
		public static function register_bulk_action( array $bulk_actions ) {
			$bulk_actions['bulk_send_welcome_email'] = esc_html__( 'Resend Welcome Email', 'resend-welcome-email' );

			return $bulk_actions;
		}

		/**
		 * Handle bulk actions
		 *
		 * @param string $sendback
		 * @param string $doaction
		 * @param array  $items
		 *
		 * @return string
		 */
		public static function handle_bulk_action( string $sendback, string $doaction, array $items ) {
			if ( $doaction !== 'bulk_send_welcome_email' ) {
				return $sendback;
			}
			foreach ( $items as $item ) {
				self::resend_welcome_email( $item );
			}
			$sendback = add_query_arg( 'bulk_sent_welcome_emails', count( $items ), $sendback );

			return $sendback;
		}

		/**
		 * Bulk actions notifications
		 */
		public static function bulk_action_admin_notice() {
			if ( ! empty( $_REQUEST['bulk_sent_welcome_emails'] ) ) {
				$emailed_count = intval( $_REQUEST['bulk_sent_welcome_emails'] );
				printf( '<div class="updated fade">' .
					_n( 'Resent %s welcome email.',
						'Resent %s welcome emails.',
						$emailed_count,
						'resend-welcome-email'
					) . '</div>', $emailed_count );
			}
		}

		/**
		 * Add link in user profile.
		 *
		 * @param \WP_User $user
		 */
		public static function personal_options( WP_User $user ) {
			if ( ! ( $link = self::send_welcome_email_url( $user ) ) ) {
				return;
			}

			?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Welcome Email', 'resend-welcome-email' ); ?></th>
				<td>
					<a href="<?php echo $link; ?>"><?php esc_html_e( 'Send New', 'resend-welcome-email' ); ?></a>
				</td>
			</tr>
			<?php
		}

		/**
		 * Listens for email send commands and fires them.
		 */
		public static function add_admin_listeners() {
			if ( ! isset( $_GET['action'] ) ||
			     ( 'resend_welcome_email' !== $_GET['action'] )
			) {
				return;
			}

			/* Resend welcome email */
			self::resend_welcome_email();

			/* Register success notice */
			add_action( 'admin_notices', array( __CLASS__, 'define_notice' ) );
			add_action( 'network_admin_notices', array( __CLASS__, 'define_notice' ) );
		}

		/**
		 * Register admin notice that email has been sent.
		 */
		public static function define_notice() {
			?>
			<div class="updated">
				<p><?php esc_html_e( 'Welcome email sent!', 'resend-welcome-email' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Helper function. Returns the switch to or switch back URL for a given user.
		 *
		 * @param  WP_User $user The user to be switched to.
		 *
		 * @return string|bool The required URL, or false if there's no old user or the user doesn't have the required capability.
		 */
		public static function send_welcome_email_url( WP_User $user ) {
			return esc_url( wp_nonce_url( add_query_arg( array(
					'action'  => 'resend_welcome_email',
					'user_id' => $user->ID,
				), '' ),
					"send_welcome_email_{$user->ID}" )
			);
		}

		/**
		 * Resends the welcome email.
		 *
		 * @param int $user_id
		 *
		 * @return bool|WP_User WP_User object on success, false on failure.
		 */
		public static function resend_welcome_email( int $user_id = 0 ) {
			if ( ! isset( $_GET['user_id'] ) && $user_id === 0 ) {
				return false;
			}

			if ( $user_id === 0 ) {
				$user_id = $_GET['user_id'];
			}

			if ( ! $user = get_userdata( $user_id ) ) {
				return false;
			}

			wp_new_user_notification( $user_id, null, 'both' );
		}

		/**
		 * Load the text domain for translation.
		 *
		 * since: 1.0.3
		 */


	}

	/**
	 *  Load Resend_Welcome_Email class in init.
	 */
	function Load_Resend_Welcome_Email() {
		new Resend_Welcome_Email();
	}

	add_action( 'admin_init', 'Load_Resend_Welcome_Email', 10 );

	/**
	 * Load text domain
	 */
	 add_action( 'plugins_loaded', 'rwe_load_textdomain' );
	 function rwe_load_textdomain() {
		load_plugin_textdomain( 'resend-welcome-email' , FALSE, basename( dirname( __FILE__ ) ) . '/languages/');
	 }

}
