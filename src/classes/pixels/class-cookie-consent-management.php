<?php

/**
 * Name: Cookie Consent Management
 * Version:  1.0
 */

// TODO implement CCM https://wordpress.org/plugins/uk-cookie-consent/ (200k) -> doesn't allow cookies to be disabled
// TODO implement CCM https://wordpress.org/plugins/auto-terms-of-service-and-privacy-policy/ (100k)
// TODO implement CCM https://wordpress.org/plugins/complianz-gdpr/ (100k)
// TODO implement CCM https://wordpress.org/plugins/eu-cookie-law/ (100k) -> doesn't set a non tracking cookie. bad programming overall
// TODO https://wordpress.org/plugins/gdpr-cookie-compliance/ (100k)
// TODO https://wordpress.org/plugins/cookiebot/ (60k) -> no cookie or filter based third party tracking opt out
// TODO https://wordpress.org/plugins/gdpr/ (30k) -> not possible to implement since users can choose their own cookie names
// TODO https://wordpress.org/plugins/gdpr-framework/ (30k)
// TODO https://wordpress.org/plugins/wf-cookie-consent/ (20k)
// TODO https://wordpress.org/plugins/responsive-cookie-consent/ (3k)
// TODO https://wordpress.org/plugins/surbma-gdpr-proof-google-analytics/ (1k)

namespace WGACT\Classes\Pixels;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Cookie_Consent_Management {

	public $pluginPrefix;

	// check if third party cookie prevention is active
	public function is_cookie_prevention_active(): bool
    {
		$cookie_prevention = false;

		// use filter to set default to activate prevention
		// add_filter( 'wgact_cookie_prevention', '__return_true' );
		// later, turn it off in order to allow cookies in case they have been actively approved
        $cookie_prevention = apply_filters_deprecated( 'wgact_cookie_prevention', [$cookie_prevention], '1.10.4', 'wooptpm_cookie_prevention' );
        $cookie_prevention = apply_filters( 'wooptpm_cookie_prevention', $cookie_prevention );

		// check if the Moove third party cookie prevention is on
		if ( $this->is_moove_cookie_prevention_active() ) {
			$cookie_prevention = true;
		}

		// check if the Cookie Notice Plugin third party cookie prevention is on
		if ( $this->is_cookie_notice_plugin_cookie_prevention_active() ) {
			$cookie_prevention = true;
		}

		// check if the Cookie Law Info third party cookie prevention is on
		if ( $this->is_cookie_law_info_cookie_prevention_active() ) {
			$cookie_prevention = true;
		}

		// check if marketing cookies have been approved by Borlabs
		if ( $this->checkBorlabsGaveMarketingConsent() ){
			$cookie_prevention = false;
		}

		return $cookie_prevention;
	}

	public function checkBorlabsGaveMarketingConsent(){

		// check if Borlabs is running
		if (function_exists('BorlabsCookieHelper')){

			// check if Borlabs minimum version is installed
			$borlabs_info = get_file_data( ABSPATH . 'wp-content/plugins/' . 'borlabs-cookie/borlabs-cookie.php', [
				'Version' => 'Version'
			] );

			// the minimum version I know of that supports gaveConsent('marketing') is 2.2.4
			if(version_compare('2.1.0', $borlabs_info['Version'], '<=')){

				if (BorlabsCookieHelper()->gaveConsent('google-ads') || BorlabsCookieHelper()->gaveConsent('woopt-pixel-manager')){
					return true;
				}
			}
		}

		return false;
	}

	public function setPluginPrefix( $name ) {
		$this->pluginPrefix = $name;
	}

	// return the cookie contents, if the cookie is set
	public function getCookie( $cookie_name ) {

		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return $_COOKIE[ $cookie_name ];
		} else {
			return null;
		}
	}

	// check if the Cookie Law Info plugin prevents third party cookies
	// https://wordpress.org/plugins/cookie-law-info/
	public function is_cookie_law_info_cookie_prevention_active() {

		$cookie_consent_management_cookie = $this->getCookie( 'cookielawinfo-checkbox-non-necessary' );

		if ( $cookie_consent_management_cookie == 'no' ) {
			return true;
		} else {
			return false;
		}
	}

	// check if the Cookie Notice Plugin prevents third party cookies
	// https://wordpress.org/plugins/cookie-notice/
	public function is_cookie_notice_plugin_cookie_prevention_active() {

		$cookie_consent_management_cookie = $this->getCookie( 'cookie_notice_accepted' );

		if ( $cookie_consent_management_cookie == 'false' ) {
			return true;
		} else {
			return false;
		}
	}

	// check if the Moove GDPR Cookie Compliance prevents third party cookies
	// https://wordpress.org/plugins/gdpr-cookie-compliance/
	public function is_moove_cookie_prevention_active() {
		if ( isset( $_COOKIE['moove_gdpr_popup'] ) ) {

			$cookie_consent_management_cookie = $_COOKIE['moove_gdpr_popup'];
			$cookie_consent_management_cookie = json_decode( stripslashes( $cookie_consent_management_cookie ), true );

			if ( $cookie_consent_management_cookie['thirdparty'] == 0 ) {
				// print_r( $cookie_consent_management_cookie );
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
}