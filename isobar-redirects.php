<?php

/*
  Plugin Name: Isobar 301 Redirects
  Description: Custom 301 redirects plugin provided by Isobar
  Version: 0.1
  Author: Isobar
  Author URI: http://www.isobar.com/ie/en/
*/

/**
* @author Cian Gallagher
*/

// Controller
require('includes/controller/base_controller.php');

// Model
require('includes/model/base_admin_model.php');

// View
require('includes/views/base_admin_html.php');

$controller = new BaseController();

if( $controller ) {
  // Create the menu in the CMS to start
  add_action( 'admin_menu', array($controller, 'init'), 1 );

  // Load redirects
  add_action( 'init', array($controller, 'init_redirects'), 1 );

  if (isset($_POST['submit_301']) || isset($_POST['submit_301_hidden'])) {
    add_action( 'admin_init', array($controller, 'save_redirects') );
  }

  if (isset($_POST['redirects_upload_submit'])) {
    add_action( 'admin_init', array($controller, 'import_csv') );
  }

  if (isset($_POST['redirects_export_submit'])) {
    add_action( 'admin_init', array($controller, 'export_csv') );
  }
}