<?php

if( !class_exists('BaseAdminModel') ) {

  class BaseAdminModel {

    public function update_redirects( $redirects ) {
      update_option('isobar_redirects', $redirects);
    }

    public function update_option( $option ) {
      update_option( $option, true );
    }

    public function delete_option( $option ) {
      delete_option( $option );
    }

    public function get_option( $option ) {
      return get_option( $option );
    }

    public function get_redirects() {
      return $redirects = get_option('isobar_redirects');
    }

  }

}