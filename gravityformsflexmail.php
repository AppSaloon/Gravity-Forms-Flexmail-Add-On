<?php
/**
Plugin Name: Gravity Forms Flexmail Add-On
Plugin URI: https://www.appsaloon.be
Description: Integrates Gravity Forms with Flexmail, allowing form submissions to be automatically sent to your Flexmail account
Version: 1.3.0
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

namespace gravityformsflexmail;

define( 'GF_FLEXMAIL_VERSION', '1.3.0' );

if( !defined('GF_FLEXMAIL_DIR')){
    define('GF_FLEXMAIL_DIR', dirname( __FILE__) .'/' );
}

class Gravityformsflexmail
{

    public function __construct()
    {
        // set autoloader
        $this->set_autoloader();

        // load gf settings
        add_action( 'gform_loaded', array( $this, 'load' ), 5 );
    }

    /**
     * If the Feed Add-On Framework exists, Mailchimp Add-On is loaded.
     *
     * @access public
     * @static
     */
    public function load() {

        if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gf-flexmail.php' );

        \GFAddOn::register( 'GFFlexmail' );
    }

    /**
     * Set autoloader
     */
    public function set_autoloader()
    {
        spl_autoload_register( array($this, 'gf_flexmail_autoload') );
    }

    /**
     * Autoloader
     *
     * @param $class
     */
    public function gf_flexmail_autoload( $class ) {
        if ( strpos( $class, 'gravityformsflexmail\\' ) === 0 ) {
            $path = substr( $class, strlen( 'gravityformsflexmail\\' ) );
            $path = strtolower( $path );
            $path = str_replace( '_', '-', $path );
            $path = str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';
            $path = __DIR__ . DIRECTORY_SEPARATOR . $path;

            if ( file_exists( $path ) ) {
                include $path;
            }
        }
    }
}

new Gravityformsflexmail();

/**
 * Returns an instance of the GFFlexmail class
 *
 * @see    GFFlexmail::get_instance()
 *
 * @return object GFFlexmail
 */
function gf_flexmail() {
    return \GFFlexmail::get_instance();
}