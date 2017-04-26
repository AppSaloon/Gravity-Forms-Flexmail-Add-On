<?php
/**
Plugin Name: Gravity Forms Flexmail Add-On
Plugin URI: http://www.gravityforms.com
Description: Integrates Gravity Forms with Flexmail, allowing form submissions to be automatically sent to your Flexmail account
Version: 1.0
Author: AppSaloon
Author URI: https://www.appsaloon.be
Text Domain: gravityformsflexmail
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2017 AppSaloon

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

define( 'GF_FLEXMAIL_VERSION', '1.0' );

// If Gravity Forms is loaded, bootstrap the Mailchimp Add-On.
add_action( 'gform_loaded', array( 'GF_Flexmail_Bootstrap', 'load' ), 5 );

/**
 * Class GF_MailChimp_Bootstrap
 *
 * Handles the loading of the Mailchimp Add-On and registers with the Add-On Framework.
 */
class GF_Flexmail_Bootstrap {

    /**
     * If the Feed Add-On Framework exists, Mailchimp Add-On is loaded.
     *
     * @access public
     * @static
     */
    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gf-flexmail.php' );

        GFAddOn::register( 'GFFlexmail' );
    }
}

/**
 * Returns an instance of the GFFlexmail class
 *
 * @see    GFMailChimp::get_instance()
 *
 * @return object GFFlexmail
 */
function gf_flexmail() {
    return GFFlexmail::get_instance();
}