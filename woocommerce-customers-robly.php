<?php
/*
 * Plugin Name: WooCommerce Customers to Robly
 * Version: 1.0
 * Description: Adds WooCommerce customers to Robly using their API
 * Author: AndrewRMinion Design
 * Author URI: https://andrewrminion.com
 * Plugin URI: PLUGIN SITE HERE
 * Text Domain: woocommerce-customers-robly
 * Domain Path: /languages
 * License: GPL2
 * GitHub Plugin URI: https://github.com/macbookandrew/woocommerce-customers-robly
 */

/* prevent this file from being accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add WP settings
 */

/* add settings page */
add_action( 'admin_menu', 'wcc_robly_add_admin_menu' );
add_action( 'admin_init', 'wcc_robly_settings_init' );

// add to menu
function wcc_robly_add_admin_menu() {
    add_options_page( 'Woocommerce Customers to Robly', 'Woocommerce to Robly', 'manage_options', 'woocommerce-customers-robly', 'wcc_robly_options_page' );
}

// add settings section and fields
function wcc_robly_settings_init() {
    register_setting( 'wcc_robly_options', 'wcc_robly_settings' );

    // API settings
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

    // alternate email settings
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


    // email lists settings
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

// print API ID field
function wcc_robly_api_id_render() {
    $options = get_option( 'wcc_robly_settings' ); ?>
    <input type="text" name="wcc_robly_settings[wcc_robly_api_id]" placeholder="8c5cc6b52e139888c3a3eb2cc7dacd9b" size="40" value="<?php echo $options['wcc_robly_api_id']; ?>">
    <?php
}

// print API Key field
function wcc_robly_api_key_render() {
    $options = get_option( 'wcc_robly_settings' ); ?>
    <input type="text" name="wcc_robly_settings[wcc_robly_api_key]" placeholder="f1a80ae1cb0c73d4f4d341" size="40" value="<?php echo $options['wcc_robly_api_key']; ?>">
    <?php
}

// print alternate email field
function wcc_robly_alternate_email_render() {
    $options = get_option( 'wcc_robly_settings' ); ?>
    <input type="email" name="wcc_robly_settings[wcc_robly_alternate_email]" placeholder="john.doe@example.com" value="<?php echo $options['wcc_robly_alternate_email']; ?>">
    <?php
}

// print sublists field
function wcc_robly_global_sublists_render() {
    $options = get_option( 'wcc_robly_settings' );

    if ( $options['wcc_robly_api_id'] && $options['wcc_robly_api_key'] ) {
        $robly_API_id = $options['wcc_robly_api_id'];
        $robly_API_key = $options['wcc_robly_api_key'];
        $selected_lists = $options['wcc_robly_global_sublists'];

        // get all sublists from Robly API
        $sublists_ch = curl_init();
        curl_setopt( $sublists_ch, CURLOPT_URL, 'https://api.robly.com/api/v1/sub_lists/show?api_id=' . $robly_API_id . '&api_key=' . $robly_API_key . '&include_all=true' );
        curl_setopt( $sublists_ch, CURLOPT_RETURNTRANSFER, true );
        $sublists_ch_response = curl_exec( $sublists_ch );
        curl_close( $sublists_ch );

        // decode JSON return
        $all_sublists = json_decode( $sublists_ch_response );

        // output form if there are valid lists
        if ( $all_sublists ) {
            echo '<select multiple name="wcc_robly_settings[wcc_robly_global_sublists][]" size="' . count( $all_sublists ) . '">';
            // loop through all results
            foreach ( $all_sublists as $list ) {
                echo '<option value="' . $list->sub_list->id . '"';

                // mark as selected if chosen
                if ( $selected_lists && in_array( $list->sub_list->id, $selected_lists ) ) {
                    echo ' selected="selected"';
                }
                echo '>' . $list->sub_list->name . '</option>';
            }
            echo '</select>';
        }
    } else {
        echo '<p>Please enter your Robly API ID and key above and save changes.</p>';
    }
}

// print API settings description
function wcc_robly_api_settings_section_callback() {
    echo __( 'Enter your API Keys below. Don’t have any? <a href="mailto:support@robly.com?subject=API access">Request them here</a>.', 'wcc_robly' );
}

// print alternate email settings description
function wcc_robly_alternate_email_settings_section_callback() {
    echo __( 'By default, failed API results will be emailed to the site administrator. To send to a different email address, enter it below; separate multiple addresses with commas.', 'wcc_robly' );
}

// print sublists section
function wcc_robly_global_sublists_section_callback() {
    echo __( 'Choose the list(s) for all customers to be added to.', 'wcc_robly' );
}

// print form
function wcc_robly_options_page() { ?>
    <div class="wrap">
       <h2>Woocommerce Customers to Robly</h2>
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
 */
add_filter( 'woocommerce_product_data_tabs', 'wcc_robly_product_tab' );
function wcc_robly_product_tab( $product_data_tabs ) {
    // Adds the new tab
    $product_data_tabs['wcc_robly_tab'] = array(
        'label' 	=> __( 'Robly', 'wcc_robly' ),
        'target' 	=> 'wcc_robly_product_tab_content'
    );
    return $product_data_tabs;
}
add_action( 'woocommerce_product_data_panels', 'wcc_robly_add_product_data_fields' );
function wcc_robly_add_product_data_fields() {
    global $woocommerce, $post;
    ?>
    <div id="wcc_robly_product_tab_content" class="panel woocommerce_options_panel">
    <?php
    $options = get_option( 'wcc_robly_settings' );
    if ( $options['wcc_robly_api_id'] && $options['wcc_robly_api_key'] ) {
        $robly_API_id = $options['wcc_robly_api_id'];
        $robly_API_key = $options['wcc_robly_api_key'];

        // get all sublists from Robly API
        $sublists_ch = curl_init();
        curl_setopt( $sublists_ch, CURLOPT_URL, 'https://api.robly.com/api/v1/sub_lists/show?api_id=' . $robly_API_id . '&api_key=' . $robly_API_key . '&include_all=true' );
        curl_setopt( $sublists_ch, CURLOPT_RETURNTRANSFER, true );
        $sublists_ch_response = curl_exec( $sublists_ch );
        curl_close( $sublists_ch );

        // get saved data
        $current_sublist_selections = maybe_unserialize( get_post_meta( $post->ID, '_wcc_robly_sublists', true ) );

        // decode JSON return into array of choices
        $all_sublists = json_decode( $sublists_ch_response );
        if ( $all_sublists ) {
            // output select ?>
            <p class="form-field">
                <label for="wcc_robly_sublists[]">Choose the list(s) to add this customer to:</label>
                <select multiple name="wcc_robly_sublists[]" size="<?php count( $all_sublists ); ?>">
                <?php foreach ( $all_sublists as $list ) {
                    echo '<option value="' . $list->sub_list->id . '"';
                    if ( in_array( $list->sub_list->id, $current_sublist_selections ) ) {
                        echo ' selected="selected"';
                    }
                    echo '>' . $list->sub_list->name . '</option>';
                } ?>
                </select>
            </p>
        <?php
        }
    } else {
        echo '<p>Please check your <a href="' . get_site_url() .'/wp-admin/options-general.php?page=woocommerce-customers-robly">Robly API ID and key</a>.</p>';
    }
    ?>
    </div>
    <?php
}

// save data
add_action( 'woocommerce_process_product_meta', 'wcc_robly_add_product_data_fields_save' );
function wcc_robly_add_product_data_fields_save( $post_id ) {
    $wcc_robly_lists = $_POST['wcc_robly_sublists'];
    if ( ! empty( $wcc_robly_lists ) && ! is_serialized( $wcc_robly_lists ) ) {
        update_post_meta( $post_id, '_wcc_robly_sublists', maybe_serialize( $wcc_robly_lists ) );
    }
}


/**
 * Add customer emails to Robly
 */

/* hook into Woocommerce payment complete */
add_action( 'woocommerce_payment_complete', 'submit_woo_customers_to_robly', 10, 1 );
function submit_woo_customers_to_robly( $order_id ) {
    global $wpdb;

    // get API keys
    $options = get_option( 'wcc_robly_settings' );
    $robly_API_id = $options['wcc_robly_api_id'];
    $robly_API_key = $options['wcc_robly_api_key'];

    // set notification email address
    if ( $options['alternate_email'] ) {
        $notification_email = $options['alternate_email'];
    } else {
        $notification_email = get_option( 'admin_email' );
    }

    // get global sublists
    $robly_sublists = $options['wcc_robly_global_sublists'];

    // get order info
    $order = new WC_Order( $order_id );

    // loop through order items to get Robly sublist IDs
    foreach ( $order->get_items() as $item ) {
        $item_robly_lists = get_post_meta( $item['product_id'], '_wcc_robly_sublists', true );
        foreach ( maybe_unserialize( $item_robly_lists ) as $this_sublist ) {
            $robly_sublists[] = $this_sublist;
        }
    }

    // get customer info
    $user_meta = get_user_meta( $order->customer_user );
    $first_name = $user_meta['billing_first_name'][0];
    $last_name = $user_meta['billing_last_name'][0];
    $street_address_1 = $user_meta['billing_address_1'][0];
    $city = $user_meta['billing_city'][0];
    $state = $user_meta['billing_state'][0];
    $zip = $user_meta['billing_postcode'][0];
    $phone = $user_meta['billing_phone'][0];

    // set up data for the request
    $post_url_first_run = 'https://api.robly.com/api/v1/sign_up/generate?api_id=' . $robly_API_id . '&api_key=' . $robly_API_key;
    $post_url_subsequent_runs = 'https://api.robly.com/api/v1/contacts/update_full_contact?api_id=' . $robly_API_id . '&api_key=' . $robly_API_key;
    $post_request_data = array(
        'email'         => $email,
        'fname'         => $first_name,
        'lname'         => $last_name,
        'data8'         => $street_address_1,
        'data9'         => $city,
        'data10'        => $state,
        'data22'        => $state,
        'data11'        => $zip,
        'data5'         => $phone
    );

    // send request via cUrl
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_URL, $post_url_first_run );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_request_data );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    $first_curl_response = curl_exec( $ch );

    // get sublist(s) and run cUrl for each since PHP won’t allow duplicate array keys and Robly requires sub_lists[] => each list ID
    foreach ( $robly_sublists as $this_sublist ) {

        // add this sublist to the request
        $post_request_data['sub_lists'] = $this_sublist;

        curl_setopt( $ch, CURLOPT_URL, $post_url_subsequent_runs );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_request_data );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $post_result = curl_exec( $ch );

        // check for cUrl errors and send email if needed
        $post_result_array = json_decode( $post_result );
        if ( $post_result_array->successful == 'false' ) {
            $send_email = 'true';
            $notification_content .= $post_result;
        }
    } // end sublist loop

    // close cUrl connection
    curl_close( $ch );

    // send notification email if necessary
    mail( $notification_email, 'Contact to manually add to Robly', $notification_content );
}
