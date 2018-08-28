<?php
/**
 * Plugin Name: WooCommerce Customers to Robly
 * Version: 1.3.0
 * Description: Adds WooCommerce customers to Robly using their API
 * Author: AndrewRMinion Design
 * Author URI: https://andrewrminion.com
 * Plugin URI: http://code.andrewrminion.com/woocommerce-customers-to-robly/
 * Text Domain: woocommerce-customers-robly
 * Domain Path: /languages
 * License: GPL2
 * GitHub Plugin URI: https://github.com/macbookandrew/woocommerce-customers-robly
 *
 * @package Woocommerce Customers to Robly
 */

/* prevent this file from being accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all sublists from Robly API.
 *
 * @return array All sublists.
 */
function wcc_get_robly_sublists() {
	$options       = get_option( 'wcc_robly_settings' );
	$robly_api_id  = $options['wcc_robly_api_id'];
	$robly_api_key = $options['wcc_robly_api_key'];

	// Get all sublists from Robly API.
	$sublists = wp_remote_get( 'https://api.robly.com/api/v1/sub_lists/show?api_id=' . $robly_api_id . '&api_key=' . $robly_api_key . '&include_all=true' );

	return json_decode( wp_remote_retrieve_body( $sublists ) );
}

/**
 * Add options page.
 */
add_action( 'admin_menu', 'wcc_robly_add_admin_menu' );
add_action( 'admin_init', 'wcc_robly_settings_init' );
add_action( 'admin_enqueue_scripts', 'wcc_admin_assets' );

/**
 * Enqueue select2 and run on all WCC Robly.
 *
 * @return void
 */
function wcc_admin_assets() {
	wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', array(), null, true );
	wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', array(), null, false );

	wp_add_inline_script( 'select2', 'jQuery(document).ready(function(){jQuery("select[name*=wcc_robly]").select2();});', 'after' );
}

/**
 * Add options page to menu.
 *
 * @return void
 */
function wcc_robly_add_admin_menu() {
	add_options_page( 'WooCommerce Customers to Robly', 'WooCommerce to Robly', 'manage_options', 'woocommerce-customers-robly', 'wcc_robly_options_page' );
}

/**
 * Add settings section and fields.
 *
 * @return void
 */
function wcc_robly_settings_init() {
	register_setting( 'wcc_robly_options', 'wcc_robly_settings' );

	// API settings.
	add_settings_section(
		'wcc_robly_options_keys_section',
		__( 'Add your API Keys', 'wcc_robly' ),
		'wcc_robly_api_settings_section_callback',
		'wcc_robly_options'
	);

	add_settings_field(
		'wcc_robly_api_id',
		__( 'API ID', 'wcc_robly' ),
		'wcc_robly_api_id_render',
		'wcc_robly_options',
		'wcc_robly_options_keys_section'
	);

	add_settings_field(
		'wcc_robly_api_key',
		__( 'API Key', 'wcc_robly' ),
		'wcc_robly_api_key_render',
		'wcc_robly_options',
		'wcc_robly_options_keys_section'
	);

	// Alternate email settings.
	add_settings_section(
		'wcc_robly_options_alternate_email_section',
		__( 'Alternate Email', 'wcc_robly' ),
		'wcc_robly_alternate_email_settings_section_callback',
		'wcc_robly_options'
	);

	add_settings_field(
		'wcc_robly_alternate_email',
		__( 'Alternate Email Address', 'wcc_robly' ),
		'wcc_robly_alternate_email_render',
		'wcc_robly_options',
		'wcc_robly_options_alternate_email_section'
	);

	// Email lists settings.
	add_settings_section(
		'wcc_robly_options_sublists_section',
		__( 'Email Lists', 'wcc_robly' ),
		'wcc_robly_global_sublists_section_callback',
		'wcc_robly_options'
	);

	add_settings_field(
		'wcc_robly_global_sublists',
		__( 'Email Lists', 'wcc_robly' ),
		'wcc_robly_global_sublists_render',
		'wcc_robly_options',
		'wcc_robly_options_sublists_section'
	);
}

/**
 * Print API ID field.
 *
 * @return void
 */
function wcc_robly_api_id_render() {
	$options = get_option( 'wcc_robly_settings' ); ?>
	<input type="text" name="wcc_robly_settings[wcc_robly_api_id]" placeholder="8c5cc6b52e139888c3a3eb2cc7dacd9b" size="40" value="<?php echo esc_attr( $options['wcc_robly_api_id'] ); ?>">
	<?php
}

/**
 * Print API key field.
 *
 * @return void
 */
function wcc_robly_api_key_render() {
	$options = get_option( 'wcc_robly_settings' );
	?>
	<input type="text" name="wcc_robly_settings[wcc_robly_api_key]" placeholder="f1a80ae1cb0c73d4f4d341" size="40" value="<?php echo esc_attr( $options['wcc_robly_api_key'] ); ?>">
	<?php
}

/**
 * Print alternate email field.
 *
 * @return void
 */
function wcc_robly_alternate_email_render() {
	$options = get_option( 'wcc_robly_settings' );
	?>
	<input type="email" name="wcc_robly_settings[wcc_robly_alternate_email]" placeholder="john.doe@example.com" value="<?php echo esc_attr( $options['wcc_robly_alternate_email'] ); ?>">
	<?php
}

/**
 * Print sublists field.
 *
 * @return void
 */
function wcc_robly_global_sublists_render() {
	$options = get_option( 'wcc_robly_settings' );

	if ( $options['wcc_robly_api_id'] && $options['wcc_robly_api_key'] ) {
		if ( isset( $options['wcc_robly_global_sublists'] ) ) {
			$selected_lists = $options['wcc_robly_global_sublists'];
		} else {
			$selected_lists = array();
		}

		$all_sublists = wcc_get_robly_sublists();

		// Output form if there are valid lists.
		if ( $all_sublists ) {
			echo '<select multiple name="wcc_robly_settings[wcc_robly_global_sublists][]" size="' . count( $all_sublists ) . '">';
			// Loop through all results.
			foreach ( $all_sublists as $list ) {
				echo '<option value="' . esc_attr( $list->sub_list->id ) . '"';

				// Mark as selected if chosen.
				if ( $selected_lists && in_array( $list->sub_list->id, $selected_lists, true ) ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( $list->sub_list->name ) . '</option>';
			}
			echo '</select>';
		}
	} else {
		echo '<p>Please enter your Robly API ID and key above and save changes.</p>';
	}
}

/**
 * Print API settings description.
 *
 * @return void
 */
function wcc_robly_api_settings_section_callback() {
	echo esc_html( 'Enter your API Keys below. Don’t have any? <a href="mailto:support@robly.com?subject=API access">Request them here</a>.', 'wcc_robly' );
}

/**
 * Print alternate email settings description.
 *
 * @return void
 */
function wcc_robly_alternate_email_settings_section_callback() {
	esc_html_e( 'By default, failed API results will be emailed to the site administrator. To send to a different email address, enter it below; separate multiple addresses with commas.', 'wcc_robly' );
}

/**
 * Print sublists section.
 *
 * @return void
 */
function wcc_robly_global_sublists_section_callback() {
	esc_html_e( 'Choose the list(s) for all customers to be added to.', 'wcc_robly' );
}

/**
 * Print form.
 *
 * @return void
 */
function wcc_robly_options_page() {
	?>
	<div class="wrap">
		<h2>WooCommerce Customers to Robly</h2>
		<form action="options.php" method="post">

			<?php
			settings_fields( 'wcc_robly_options' );
			do_settings_sections( 'wcc_robly_options' );
			submit_button();
			?>

		</form>
	</div>
	<?php
}

/**
 * Add tab to Woo products
 *
 * @param array $product_data_tabs Product data tabs.
 *
 * @return array Product data tabs.
 */
function wcc_robly_product_tab( $product_data_tabs ) {
	// Adds the new tab.
	$product_data_tabs['wcc_robly_tab'] = array(
		'label'  => __( 'Robly', 'wcc_robly' ),
		'target' => 'wcc_robly_product_tab_content',
	);
	return $product_data_tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'wcc_robly_product_tab' );

/**
 * Add product data fields.
 *
 * @return void
 */
function wcc_robly_add_product_data_fields() {
	global $woocommerce, $post;
	?>
	<div id="wcc_robly_product_tab_content" class="panel woocommerce_options_panel">
	<?php
	$options = get_option( 'wcc_robly_settings' );
	if ( $options['wcc_robly_api_id'] && $options['wcc_robly_api_key'] ) {

		// Get saved data.
		$current_sublist_selections = maybe_unserialize( get_post_meta( $post->ID, '_wcc_robly_sublists', true ) );

		$all_sublists = wcc_get_robly_sublists();
		if ( ! empty( $all_sublists ) ) {
			// Output select filed.
			?>
			<p class="form-field">
				<label for="wcc_robly_sublists[]">Choose the list(s) to add this customer to:</label>
				<select multiple name="wcc_robly_sublists[]" size="<?php count( $all_sublists ); ?>">
				<?php
				foreach ( $all_sublists as $list ) {
					echo '<option value="' . esc_attr( $list->sub_list->id ) . '"';
					if ( $current_sublist_selections && in_array( $list->sub_list->id, $current_sublist_selections, true ) ) {
						echo ' selected="selected"';
					}
					echo '>' . esc_html( $list->sub_list->name ) . '</option>';
				}
				?>
				</select>
			</p>
			<?php
		}
	} else {
		echo '<p>Please check your <a href="' . esc_url( get_site_url() ) . '/wp-admin/options-general.php?page=woocommerce-customers-robly">Robly API ID and key</a>.</p>';
	}
	?>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'wcc_robly_add_product_data_fields' );

/**
 * Save product data fields.
 *
 * @param int $post_id Post ID.
 *
 * @return void
 */
function wcc_robly_add_product_data_fields_save( $post_id ) {
	$wcc_robly_lists = $_POST['wcc_robly_sublists'];
	if ( ! empty( $wcc_robly_lists ) && ! is_serialized( $wcc_robly_lists ) ) {
		update_post_meta( $post_id, '_wcc_robly_sublists', maybe_serialize( $wcc_robly_lists ) );
	}
}
add_action( 'woocommerce_process_product_meta', 'wcc_robly_add_product_data_fields_save' );

/**
 * Add customer data to Robly.
 *
 * @param int $order_id WooCommerce order ID.
 *
 * @return void
 */
function submit_woo_customers_to_robly( $order_id ) {
	global $wpdb;
	$error_message = null;

	// Get API keys and URL.
	$options         = get_option( 'wcc_robly_settings' );
	$robly_api_id    = $options['wcc_robly_api_id'];
	$robly_api_key   = $options['wcc_robly_api_key'];
	$api_base        = 'https://api.robly.com/api/v1/';
	$api_credentials = '?api_id=' . $robly_api_id . '&api_key=' . $robly_api_key;

	// Set notification email address.
	if ( isset( $options['alternate_email'] ) ) {
		$notification_email = $options['alternate_email'];
	} else {
		$notification_email = get_option( 'admin_email' );
	}

	// Get global sublists.
	$robly_sublists = $options['wcc_robly_global_sublists'];

	// Get order info.
	$order = new WC_Order( $order_id );

	// Loop through order items to get Robly sublist IDs.
	foreach ( $order->get_items() as $item ) {
		$item_robly_lists = get_post_meta( $item['product_id'], '_wcc_robly_sublists', true );
		if ( $item_robly_lists ) {
			foreach ( maybe_unserialize( $item_robly_lists ) as $this_sublist ) {
				$robly_sublists[] = $this_sublist;
			}
		}
	}

	// Get customer info.
	$customer_info    = $order->get_address();
	$email            = str_replace( '+', '%2B', $customer_info['email'] );
	$first_name       = $customer_info['first_name'];
	$last_name        = $customer_info['last_name'];
	$street_address_1 = $customer_info['address_1'];
	$city             = $customer_info['city'];
	$state            = $customer_info['state'];
	$zip              = $customer_info['postcode'];
	$phone            = $customer_info['phone'];

	// Search Robly for customer by email.
	$search          = wp_remote_get( $api_base . 'contacts/search' . $api_credentials . '&email=' . $email );
	$search_response = json_decode( wp_remote_retrieve_body( $search ) );

	// Set API method for subsequent call.
	if ( isset( $search_response->member ) ) {
		// Handle deleted/unsubscribed members.
		if ( false === $search_response->member->is_subscribed || true === $search_response->member->is_deleted ) {
			// Run the request and check to see if manual email is needed.
			$resubscribe          = wp_remote_post( $api_base . 'contacts/resubscribe' . $api_credentials . '&email=' . $email );
			$resubscribe_response = wp_remote_retrieve_body( $resubscribe );
			$json_response        = json_decode( $resubscribe_response );
			if ( true !== $json_response->successful ) {
				$send_email     = true;
				$error_message .= 'Resubscribe: ' . json_decode( $resubscribe_response )->message;
			} else {
				$send_email = false;
			}
		}
		// Continue with updating contact info.
		$api_method = 'contacts/update_full_contact';
		// Handle new members.
	} else {
		$api_method = 'sign_up/generate';
	}

	// Set up user data for the request.
	$post_url = $api_method . $api_credentials;
	// TODO: remove hardcoded fields.
	$user_parameters = array(
		'email'                 => $email,
		'fname'                 => $first_name,
		'lname'                 => $last_name,
		'data8'                 => $street_address_1,
		'data9'                 => $city,
		'data10'                => $state,
		'data22'                => $state,
		'data11'                => $zip,
		'data5'                 => $phone,
		'include_autoresponder' => 'true',
	);
	$user_parameters = str_replace( '%40', '@', http_build_query( $user_parameters ) );

	// Add sublist IDs.
	$post_data = null;
	if ( $robly_sublists ) {
		foreach ( $robly_sublists as $this_list ) {
			$post_data .= 'sub_lists[]=' . $this_list . '&';
		}
	}
	$post_data = rtrim( $post_data, '&' );

	// Run the request and check to see if manual email is needed.
	$user          = wp_remote_post( $api_base . $api_method . $api_credentials . '&' . $user_parameters, array( 'body' => $post_data ) );
	$user_response = wp_remote_retrieve_body( $user );
	$json_response = json_decode( $user_response );

	if ( true !== $json_response->successful || ( true === $json_response->successful && false !== strpos( $json_response->message, 'already exists' ) ) ) {
		$send_email     = true;
		$error_message .= 'Update Contact: ' . json_decode( $$user_response )->message;
	} else {
		$send_email = false;
	}

	// Send notification email if necessary.
	if ( $send_email ) {
		$email_sent = mail( $notification_email, 'Contact to manually add to Robly', "API failure\n\nAPI call:\n" . $api_base . $api_method . '?api_id=XXX&api_key=XXX&' . $user_parameters . "\nLists: " . $post_data . "\n\nDetails:\n" . $error_message . "\n\nSent by the WooCommerce Customers to Robly plugin on " . home_url() );
	}
}
add_action( 'woocommerce_payment_complete', 'submit_woo_customers_to_robly', 10, 1 );
