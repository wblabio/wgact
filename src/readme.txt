=== Plugin Name ===
Contributors: alekv, wolfbaer, woopt
Tags: woocommerce, google ads, conversion tracking, dynamic retargeting, remarketing , adwords
Requires at least: 3.7
Tested up to: 5.6
Requires PHP: 7.2
Stable tag: 1.8.16
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track the order value and create dynamic remarketing lists in Google Ads from WooCommerce

== Description ==

This plugin <strong>tracks the value of WooCommerce orders and collects data for dynamic remarketing lists in Google Ads</strong>.

With this data you can optimize all your Google Ads campaigns to achieve maximum efficiency.

<strong>Highlights</strong>

* Precise measurement by preventing duplicate reporting effectively, excluding admins and shop managers from tracking, and not counting failed payments.
* Collects dynamic remarketing audiences for dynamic retargeting: [Google Ads Dynamic Remarketing](https://support.google.com/google-ads/answer/3124536)
* Implements the new Google Add Cart Data functionality. More info about the new feature: [add cart data to the conversion](https://support.google.com/google-ads/answer/9028254)
* Support for various cookie consent management systems

<strong>Documentation</strong>

Link to the full documentation of the plugin: [Open the documentation](https://docs.woopt.com/wgact/?utm_source=wp-org&utm_medium=documentation-link&utm_campaign=wp-org-documentation-link#/)

<strong>Cookie Consent Management</strong>

The plugin uses data from several Cookie Consent Management plugins to manage approvals and disapprovals for injection of marketing pixels.

It works with the following Cookie Consent Management plugins out of the box:

* [Cookie Notice](https://wordpress.org/plugins/cookie-notice/)
* [Cookie Law Info](https://wordpress.org/plugins/cookie-law-info/)
* [GDPR Cookie Compliance](https://wordpress.org/plugins/gdpr-cookie-compliance/)
* [Borlabs Cookie](https://borlabs.io/borlabs-cookie/) (from version 2.1.0)
  [Borlabs Cookie Setup](https://docs.woopt.com/wgact/?utm_source=wp-org&utm_medium=documentation-link&utm_campaign=wp-org-documentation-link&utm_content=borlabs-cookie#/consent-mgmt/borlabs-cookie)

<strong>Requirements</strong>

[List of requirements](https://docs.woopt.com/wgact/?utm_source=wp-org&utm_medium=documentation-link&utm_campaign=wp-org-documentation-link&utm_content=requirements#/requirements)

== Installation ==

1. Upload the plugin directory into your plugins directory `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Get the Google Ads conversion ID and the conversion label. You will find both values in the Google Ads conversion tracking code. [Get the conversion ID and the conversion label](https://www.youtube.com/watch?v=p9gY3JSrNHU)
4. In the WordPress admin panel go to WooCommerce and then into the 'Google Ads Conversion Tracking' menu. Please enter the conversion ID and the conversion label into their respective fields.

== Frequently Asked Questions ==

= Is there detailed documentation for the plugin? =

Yes. Head over to this link: [Documentation](https://docs.woopt.com/wgact/?utm_source=wp-org&utm_medium=documentation-link&utm_campaign=wp-org-documentation-link#/)

= How do I check if the plugin is working properly? =

1. Turn off any kind of caching and / or minification plugins.
2. Log out of the shop.
3. Turn off any kind of ad or script blocker in your browser.
4. Search for one of your keywords and click on one of your ads.
5. Purchase an item from your shop.
6. Wait up to 48 hours until the conversion shows up in Google Ads. (usually takes only a few hours)

With the Google Tag Assistant you will also be able to see the tag fired on the thankyou page.

= I get a fatal error and I am running old versions of WordPress and/or WooCommerce. What can I do? =

As this is a free plugin we don't support old versions of WordPress and WooCommerce. You will have to update your installation.

= I am using an offsite payment gateway and the conversions are not being tracked. What can I do? =

We don't support if an offsite payment gateway is in use. The reason is that those cases can be complex and time consuming to solve. We don't want to cover this for a free plugin. We do not recommend offsite payment gateways anyway. A visitor can stop the redirection manually which prevents at least some conversions to be tracked. Also offsite payment gateways are generally bad for the conversion rate.

= I've done everything right in the test, but it still doesn't work. What can I do? =

Here is a non-exhaustive list of causes that might interfere with the plugin code.

* Minification plugins try to minify the JavaScript code of the plugin. Not all minification plugins do this good enough and can cause problems. Turn off the JavaScript minification and try again.
* Caching could also cause problems if caching is set too aggressively. Generally don't ever enable HTML caching on a WooCommerce shop, as it can cause troubles with plugins that generate dynamic output.

= I see issues in the backend of my shop. Admin pages get rendered weird, and popups don't go away when I click to close them. How can I fix this? =

You probably have some script or ad blocker activated. Deactivate it and the issues should go away. Usually you can disable the blocker for just that particular site (your WooCommerce back end).

Our plugin injects tracking pixels on the front end of WooCommerce shops. As a consequence scripts of our plugin have been added to some privacy filter lists. The idea is to prevent the scripts running if a shop visitor has some ad blocker enabled and wants to visit the front end of the shop. This is totally ok for visitors of the front end of the shop. But, it becomes an issue for admins of the shop who have a blocker activated in their browser and visit the backend of the shop.

Unfortunately there is no way for us to generally approve our scripts in all blockers for the WooCommerce back end.

Therefore we recommend admins of the shop to exclude their own shop from the blocker in their browser.

= Where can I report a bug or suggest improvements? =

Please post your problem in the WGACT Support forum: http://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag
You can send the link to the front page of your shop too if you think it would be of help.

== Screenshots ==

1. Settings page

== Changelog ==

= 1.8.16 =

[//]: # fs_premium_only_begin
* New: Google Analytics enhanced e-commerce
[//]: # fs_premium_only_end
New: Output the variation ID for dynamic remarketing
[//]: # fs_premium_only_begin
* Tweak: Strip HTML tags from description in microdata output for FB
[//]: # fs_premium_only_end
Tweak: Made Google Analytics always receive the post ID as product ID because this is more robust
Fix: Script blocker documentation link

= 1.8.15 =

[//]: # fs_premium_only_begin
* New: Added Facebook microdata output
[//]: # fs_premium_only_end
* Fix: Determine correctly new customer for shopping cart data on new customers who paid immediately
* Tweak: Created new trait to calculate brand for product
* Tweak: Added a new array with additional product attributes (like brand which is calculated)
* Tweak: added ability to load traits in autoload.php
* Tweak: Bumped up WC version
* Tweak: Added an additional is_array() check in order to suppress a PHP 7.4 notice when checking the environment

= 1.8.14 =

* Tweak: Make the <noptimize> tag only appear if Autoptimize is active
* Tweak: Removed a duplicate filter
* Fix: Moved get_cart() query into is_cart() condition

= 1.8.13 =

* New: Filter to prevent conversion pixels to fire on purchase confirmation page
* Tweak: Replaced _e() with echo where necessary
* Tweak: Syntax cleanup

= 1.8.12 =

* Tweak: Calculate filtered order total for all pixels

= 1.8.11 =

[//]: # fs_premium_only_begin
* Tweak: Added product IDs to Microsoft Advertising purchase event
[//]: # fs_premium_only_end
* Fix: Removed a function call where the function was missing

= 1.8.10 =

[//]: # fs_premium_only_begin
* New: Google consent mode
* New: Setting for cookiebot
* New: Added advanced order deduper
* Tweak: Removed Pinterest noscript tags
* Fix: Added product IDs to Microsoft Advertising purchase event
[//]: # fs_premium_only_end
* New: Added basic order deduper
* New: Google Shopping new_customer parameter
* New: Added switch to enable transaction deduping (default enabled)
* Tweak: Product identifier output now for all the same
* Tweak: Adjusted the HTML comment output
* Tweak: Added new cookie for Borlabs Cookie
* Tweak: Made some input elements clickable
* Tweak: Moved check for failed payments and admin and shop manager up to the pixel manager

= 1.8.9 =

* Tweak: readme.txt links
* Tweak: fallback to post ID in case the SKU is not set
* Tweak: Adjusted the HTML comments
* Tweak: Don't inject cart scripts on cart page if cart is empty

= 1.8.8 =

[//]: # fs_premium_only_begin
* Tweak: Adjusted directory name
[//]: # fs_premium_only_end
* Tweak: Bumped up version
* Tweak: Changed regex for GMC IDs to allow 7 digit IDs
* Tweak: Improved speed to hide script blocker warning
* Tweak: Adjusted documentation links

= 1.8.7 =

* Fix: Added new classes to SVN

= 1.8.6 =

* Tweak: Code cleanup
* Tweak: Adjusted doc links

= 1.8.5 =

[//]: # fs_premium_only_begin
* Tweak: Bing dynamic remarketing events
[//]: # fs_premium_only_end
* New: Hotjar pixel

= 1.8.4 =

[//]: # fs_premium_only_begin
* New: Microsoft Ads pixel
* New: Twitter pixel
* New: Pinterest pixel
* New: Hotjar pixel
[//]: # fs_premium_only_end
* Tweak: Renamed subsection 'Order Logic' to 'Shop'
* Tweak: Refactored debug info
* Tweak: Added WP Rocket JavaScript concatenation to debug info

= 1.8.3 =

[//]: # fs_premium_only_begin
* New: Google business vertical setting
[//]: # fs_premium_only_end

= 1.8.2 =

* New: Check for WP Rocket JavaScript concatenation
* New: Added filter which helps adding multiple additional conversion IDs and labels

= 1.8.1 =

* Fix: Version number
* Fix: FB default pixel id

= 1.8.0 =

* New: Google Analytics UA standard beta
* New: Google Analytics 4 beta
* New: Google Optimize beta
* New: Activation indicators
* Tweak: Put admin scripts into header for faster rendering
* Fix: Detect proper admin path in tabs.js

= 1.7.13 =

* New: Facebook pixel
* Tweak: Adjust db and bump up to version 3
* Tweak: Introduced Pixel_Manager and restructured Google Ads class

= 1.7.12 =

* Fix: Removed namespace for main class because it was conflicting with freemius in some cases

= 1.7.11 =

* Fix: Directory name fix
* New: Warning message if an ad- or script-blocker is active
* Tweak: Improved one of the db saving functions
* Tweak: Start using namespaces


= 1.7.10 =

* Fix: child theme detection

= 1.7.9 =

* Fix: Roll back to 1.7.7 since namespace don't work everywhere
* Fix: child theme detection

= 1.7.8 =

* New: Warning message if an ad- or script-blocker is active
* Tweak: Improved one of the db saving functions
* Tweak: Start using namespaces

= 1.7.7 =

* Fix: Don't show the rating popup if an admin uses a script blocker

= 1.7.6 =

* Fix: Improved check if dynamic remarketing settings already has been set before checking for it.
* Fix: Saving to the database threw sometimes warnings that have been fixed.
* Tweak: Styling changes

= 1.7.5 =

* New: Added checks for freemius servers
* New: Dynamic remarketing pixels
* New: Deactivation trigger for the WGDR plugin if dynamic remarketing is enabled
* Fix: Adjusted the cookie name for Cookie Law Info
* Fix: Improved detection if WooCommerce is active on multisite
* Fix: Fixed default setting for conversion_id
* Tweak: Added back rating testing code
* Tweak: Adjusted some links
* Tweak: Code style cleanups

= 1.7.4 =

* Fix: Fixed the ask for rating constant

= 1.7.3 =

* Fix: Don't open the rating page if user clicks on already done
* Tweak: Backward compatibility to PHP 7.0

= 1.7.2 =

* Fix: Fixed a printf syntax error that caused issues on some installations

= 1.7.1 =

* Tweak: Removed deletion of settings on uninstall in order to preserve the settings

= 1.7.0 =

* New: Added German translations
* Fix: Reversed some code in freemius to make it compatible with older versions of PHP (< PHP 7.2)
* Fix: Fixed the uninstall hook for it to work with freemius
* Tweak: Added some comments for translators
* Tweak: Removed old language packs
* Tweak: Add gtag config if gtag insertion is disabled
* Tweak: Rating request improved
* Tweak: Removed plugin ads
* Tweak: Added documentation
* Tweak: Updated db scheme
* Tweak: Merge new default options recursively
* Tweak: On save merge new and old options recursively, set missing checkbox options to zero, omit db_version

= 1.6.17 =

* Tweak: Reactivate freemius

= 1.6.16 =

* Fix: Deactivate freemius

= 1.6.15 =

* Tweak: Adjusted freemius main slug for plugin

= 1.6.14 =

* New: Implemented Freemius telemetry
* Tweak: Adjustments to the descriptions and links to new documentation
* Tweak: Only run if WooCommerce is active

= 1.6.13 =

* New: Implemented framework for sections and subsections
* Tweak: Some code cleanup
* Tweak: Made strings more translation friendly
* Tweak: Properly escaped all translatable strings
* Fix: Textdomain

= 1.6.12 =

* New: Plugin version output into debug info
* Fix: Conversion id validation
* Tweak: Moved JavaScript to proper enqueued scripts
* Tweak: Bumped up WC and WP versions

= 1.6.11 =

* New: Tabbed settings
* New: Debug information
* Tweak: Code style adjustments

= 1.6.10 =

* Fix: Disabled some error_log invocation since it can cause issues in some rare server configurations

= 1.6.9 =

* Fix: Re-enabled settings link on plugins page

= 1.6.8 =

* Fix: Changed how Borlabs Cookie activation works

= 1.6.7 =

* Fix: Implemented check for Borlabs minimum version

= 1.6.6 =

* New: Added option to disable the pixel with a filter add_filter( 'wgact_cookie_prevention', '__return_true' )
* New: Added Borlabs cookie management approval for marketing
* Tweak: Refactored the code into classes

= 1.6.5 =

* Tweak: Removed duplicate noptimize tag
* Tweak: Removed CDATA fix since it is not necessary anymore with the new conversion tag

= 1.6.4 =

* Fix: Fixed the calculation for the non-default order total value (which includes tax and shipping)

= 1.6.3 =

* Info: Tested up to WP 5.4

= 1.6.2 =

* Tweak: More reliable method to detect the visitor country added

= 1.6.1 =

* New: Add Cart Data feature
* New: Added a switch to disable the insertion of the gtag
* Tweak: Added more descriptions on the settings page
* Tweak: Code optimisations

= 1.5.5 =

* Tweak: Made the conversion ID and label validation code more robust

= 1.5.4 =

* Tweak: Updated function that inserts the settings link on the plugins overview page

= 1.5.3 =

* Info: Tested up to WP 5.2

= 1.5.2 =

* Fix: Correctly calculate the value when no filter is active

= 1.5.1 =

* Tweak: Re-enabled order value filter

= 1.4.17 =

* Info: Tested up to WP 5.1

= 1.4.16 =

* Info: Updated a few text strings

= 1.4.15 =

* Info: Changing name from AdWords to Google Ads

= 1.4.14 =

* Info: Tested up to WC 3.5.3

= 1.4.13 =

* Info: Tested up to WC 3.5.2

= 1.4.12 =

* Tweak: bumping up the WC version

= 1.4.11 =

* Tweak: remove some debug code
* fix: properly save the order_total_logic option

= 1.4.10 =

* Tweak: switched sanitization function to wp_strip_all_tags

= 1.4.9 =

* Tweak: Added input validation and sanitization
* Tweak: Added output escaping

= 1.4.8 =

* Tweak: Added discounts into order value calculation

= 1.4.7 =

* New: Switched over to the newest version of the AdWords conversion tracking pixel

= 1.4.6 =

* Tweak: Disabled minification through Autoptimize

= 1.4.5 =

* Tweak: Order ID back in apostrophes

= 1.4.4 =

* Tweak: Switched on JavaScript tracking with a fix for the CDATA bug http://core.trac.wordpress.org/ticket/3670
* Tweak: The correct function is being used to get the currency depending on the WooCommerce version
* Fix: Added missing </noscript> tag

= 1.4.3 =

* Tweak: Remove campaign URL parameter

= 1.4.2 =

* Fix: Backward compatibility for $order->get_currency()

= 1.4.1 =

* Tweak: Making the plugin PHP 5.4 backwards compatible
* Fix: Fixing double counting check logic

= 1.4 =

* New: Ask kindly for a rating of the plugin
* New: Add a radio button to use different styles of order total
* Tweak: Consolidate options into one array
* Tweak: Code cleanup

= 1.3.6 =

* New: WordPress 4.8 compatibility update
* Tweak: Minor text tweak.

= 1.3.5 =

* Fix: Fixed a syntax error that caused issues on some installations.

= 1.3.4 =

* Tweak: Added some text output to make debugging for users easier.

= 1.3.3 =

* Tweak: Refurbishment of the settings page

= 1.3.2 =

* New: Uninstall routine

= 1.3.1 =

* New: Keep old deduplication logic in the code as per recommendation by AdWords

= 1.3.0 =

* New: AdWords native order ID deduplication variable

= 1.2.2 =

* New: Filter for the conversion value

= 1.2.1 =

* Fix: wrong conversion value fix

= 1.2 =

* New: Filter for the conversion value

= 1.1 =

* Tweak: Code cleanup
* Tweak: To avoid over reporting only insert the retargeting code for visitors, not shop managers and admins

= 1.0.6 =

* Tweak: Switching single pixel function from transient to post meta

= 1.0.5 =

* Fix: Adding session handling to avoid duplications

= 1.0.4 =

* Fix: Skipping a tag version

= 1.0.3 =

* Fix: Implement different logic to exclude failed orders as the old one is too restrictive

= 1.0.2 =

* Fix: Exclude orders where the payment has failed

= 1.0.1 =

* New: Banner and icon
* Update: Name change

= 1.0 =

* Update: Release of version 1.0!

= 0.2.4 =

* Update: Minor update to the internationalization

= 0.2.3 =

* Update: Minor update to the internationalization

= 0.2.2 =

* New: The plugin is now translation ready

= 0.2.1 =

* Update: Improving plugin security
* Update: Moved the settings to the submenu of WooCommerce

= 0.2.0 =

* Update: Further improving cross browser compatibility

= 0.1.9 =

* Update: Implemented a much better workaround tor the CDATA issue
* Update: Implemented the new currency field
* Fix: Corrected the missing slash dot after the order value

= 0.1.8 =

* Fix: Corrected the plugin source to prevent an error during activation

= 0.1.7 =

* Significantly improved the database access to evaluate the order value.

= 0.1.6 =

* Added some PHP code to the tracking tag as recommended by Google.

= 0.1.5 =

* Added settings field to the plugin page.
* Visual improvements to the options page.

= 0.1.4 =

* Changed the woo_foot hook to wp_footer to avoid problems with some themes. This should be more compatible with most themes as long as they use the wp_footer hook.

= 0.1.3 =

* Changed conversion language to 'en'.

= 0.1.2 =

* Disabled the check if WooCommerce is running. The check doesn't work properly with multisite WP installations, though the plugin does work with the multisite feature turned on.
* Added more description in the code to explain why I've build a workaround to not place the tracking code into the thankyou template of WC.

= 0.1.1 =

* Some minor changes to the code

= 0.1 =

* This is the initial release of the plugin. There are no known bugs so far.
