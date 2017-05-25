=== WooCommerce Customers to Robly ===
Contributors: macbookandrew
Donate link: https://cash.me/$AndrewRMinionDesign
Tags: woocommerce, robly, email, automation, customer
Requires at least: 3.0.1
Tested up to: 4.4
Stable tag: 1.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds WooCommerce customers to one or more Robly lists, optionally based on the product(s) they purchased.

== Description ==

[Robly](https://www.robly.com/) is a paid email marketing service provider that allows customers to be in multiple lists for targeted marketing purposes. Using Robly’s API, this plugin adds WooCommerce customers to one or more Robly lists, optionally based on the product(s) they purchased.

This plugin requires an active Robly account as well as WooCommerce. You’ll also need to <a href="mailto:support@robly.com?subject=API access">contact Robly support</a> to get an API ID and key for your account.

== Installation ==

1. Upload this folder to the `/wp-content/plugins/` directory or install from the Plugins menu in WordPress
1. Activate the plugin through the Plugins menu in WordPress
1. Go to Settings > WooCommerce to Robly in WordPress, enter your Robly API ID and key, and choose the list(s) for all customers to be added to upon purchase of any product.
1. Optionally, go to the Robly tab on specific WooCommerce products and choose the list(s) to add customers to upon purchase of that individual product.

== Frequently asked questions ==

= What is Robly? =

[Robly](https://www.robly.com/) is a paid email marketing service provider that helps you send emails to large numbers of people at once.

= What do I need to use this plugin? =

This plugin requires an active Robly account as well as WooCommerce. You’ll also need to <a href="mailto:support@robly.com?subject=API access">contact Robly support</a> to get an API ID and key for your account.

= API-what? =

API stands for “Application Programming Interface,” which basically means computer code that is able to talk to other computer systems and get or send information. Most API providers require an API key of some sort (similar to a username and password) to ensure that only authorized people are able to use their services.

= What info is sent or received? =

1. When you install the plugin and enter your API ID and key, your WordPress site will contact the Robly API, asking for all the lists you have set up in your account. You are then able to choose certain lists to which all customers will be added, or choose certain lists to which purchasers of individual products are added, and those choices are saved in your WordPress options.
1. When a customer makes a purchase, WordPress will contact the Robly API and search for that customer in your Robly account by their email address. If found, it will update their information; otherwise, it will create a new contact with the customer’s name, email address, and billing address, as well as the list(s) you selected.

== Screenshots ==

1. Settings screen
2. Per-product settings

== Changelog ==

= 1.2.4 =
* Fix some email encoding issues

= 1.2.3 =
* Fix issues caused by 1.2.2

= 1.2.2 =
* Prevent double URL-encoding

= 1.2.1 =
* URL-encode customer data before sending to API

= 1.2 =
* Resubscribe deleted/unsubscribed users
* Correctly get customer info if “guest checkout” is enabled
* Minor bugfixes

= 1.1.4 =
* Fix server error when contact already exists
* Improve error notice

= 1.1.3 =
* Fix emails being sent on successful API call
* Add some more debugging info to failed API calls

= 1.1.2 =
* Update documentation and add banner for WP plugin directory

= 1.1.1 =
* Send sub_lists as POST data rather than URL

= 1.1 =
* Use only two API calls instead of successive calls for each sublist

= 1.0 =
* Initial plugin
