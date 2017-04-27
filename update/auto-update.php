<?php
namespace gravityformsflexmail\update;

if ( ! defined( 'ARPU_DIR' ) ) {
    define( 'ARPU_DIR', dirname(__FILE__).'/' );
}

use gravityformsflexmail\update\classes\bb\Arpu_Bitbucket_Plugin_Updater;

Class Auto_Update{
    public function __construct()
    {
        add_action( 'admin_init', array($this, 'arpu_bb_handle_updates') );
    }

    public function arpu_bb_handle_updates(){
        $bb_plugin = array(
            'plugin_file' => GF_FLEXMAIL_DIR . 'gravityformsflexmail.php',
            'bb_host' => 'https://api.bitbucket.org',
            'bb_download_host' => 'http://bitbucket.org',
            'bb_owner' => 'appsaloonupdater',
            'bb_password' => 'aLdNmRqZwVvL32',
            'bb_project_name' => 'appsaloon',
            'bb_repo_name' => 'gravityformsflexmail'
        );

        new Arpu_Bitbucket_Plugin_Updater( $bb_plugin );
    }
}