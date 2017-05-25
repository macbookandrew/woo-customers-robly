<?php
/*
 * Plugin Name: WooCommerce Customers to Robly
 * Version: 1.2.4
 * Description: Adds WooCommerce customers to Robly using their API
 * Author: AndrewRMinion Design
 * Author URI: https://andrewrminion.com
 * Plugin URI: http://code.andrewrminion.com/woocommerce-customers-to-robly/
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
    add_options_page( 'WooCommerce Customers to Robly', 'WooCommerce to Robly', 'manage_options', 'woocommerce-customers-robly', 'wcc_robly_options_page' );
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
        if ( isset( $options['wcc_robly_global_sublists'] ) ) {
            $selected_lists = $options['wcc_robly_global_sublists'];
        } else {
            $selected_lists = array();
        }

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
                    if ( $current_sublist_selections && in_array( $list->sub_list->id, $current_sublist_selections ) ) {
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

/* hook into WooCommerce payment complete */
add_action( 'woocommerce_payment_complete', 'submit_woo_customers_to_robly', 10, 1 );
function submit_woo_customers_to_robly( $order_id ) {
    global $wpdb;
    $error_message = NULL;

    // get API keys and URL
    $options = get_option( 'wcc_robly_settings' );
    $robly_API_id = $options['wcc_robly_api_id'];
    $robly_API_key = $options['wcc_robly_api_key'];
    $API_base = 'https://api.robly.com/api/v1/';
    $API_credentials = '?api_id=' . $robly_API_id . '&api_key=' . $robly_API_key;

    // set notification email address
    if ( isset( $options['alternate_email'] ) ) {
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
        if ( $item_robly_lists ) {
            foreach ( maybe_unserialize( $item_robly_lists ) as $this_sublist ) {
                $robly_sublists[] = $this_sublist;
            }
        }
    }

    // get customer info
    $customer_info = $order->get_address();
    $email = str_replace( '+', '%2B', $customer_info['email'] );
    $first_name = $customer_info['first_name'];
    $last_name = $customer_info['last_name'];
    $street_address_1 = $customer_info['address_1'];
    $city = $customer_info['city'];
    $state = $customer_info['state'];
    $zip = $customer_info['postcode'];
    $phone = $customer_info['phone'];

    // search Robly for customer by email
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $API_base . 'contacts/search' . $API_credentials . '&email=' . $email );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $curl_search = curl_exec( $ch );
    $curl_search_response = json_decode( $curl_search );

    // set API method for subsequent call
    if ( isset( $curl_search_response->member ) ) {
        // handle deleted/unsubscribed members
        if ( $curl_search_response->member->is_subscribed == false || $curl_search_response->member->is_deleted == true ) {
            curl_setopt( $ch, CURLOPT_URL, $API_base . 'contacts/resubscribe' . $API_credentials . '&email=' . $email );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            // run the request and check to see if manual email is needed
            $resubscribe_curl_response = curl_exec( $ch );
            $json_response = json_decode( $resubscribe_curl_response );
            if ( $json_response->successful != true ) {
                $send_email = true;
                $error_message .= 'Resubscribe: ' . json_decode( $resubscribe_curl_response )->message;
            } else {
                $send_email = false;
            }
        }
        // continue with updating contact info
        $API_method = 'contacts/update_full_contact';
    // handle new members
    } else {
        $API_method = 'sign_up/generate';
    }

    // set up user data for the request
    $post_url = $API_method . $API_credentials;
    $user_parameters = array(
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
    $user_parameters = str_replace( '%40', '@', http_build_query( $user_parameters ) );

    // add sublist IDs
    $post_data = NULL;
    if ( $robly_sublists ) {
        foreach ( $robly_sublists as $this_list ) {
            $post_data .= 'sub_lists[]=' . $this_list . '&';
        }
    }
    $post_data = rtrim( $post_data, '&' );

    // set up the rest of the request
    curl_setopt( $ch, CURLOPT_URL, $API_base . $API_method . $API_credentials . '&' . $user_parameters );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // run the request and check to see if manual email is needed
    $user_curl_response = curl_exec( $ch );
    $json_response = json_decode( $user_curl_response );
    if ( $json_response->successful != true || ( $json_response->successful == true && strpos( $json_response->message, 'already exists' ) !== false ) ) {
        $send_email = true;
        $error_message .= 'Update Contact: ' . json_decode( $user_curl_response )->message;
    } else {
        $send_email = false;
    }

    // close cUrl connection
    curl_close( $ch );

    // send notification email if necessary
    if ( $send_email ) {
        $email_sent = mail( $notification_email, 'Contact to manually add to Robly', "API failure\n\nAPI call:\n" . $API_base . $API_method . '?api_id=XXX&api_key=XXX&' . $user_parameters . "\nLists: " . $post_data . "\n\nDetails:\n" . $error_message . "\n\nSent by the WooCommerce Customers to Robly plugin on " . home_url() );
    }
}
