<?php

if( !class_exists('BaseController') ) {

  class BaseController {

    public $page = "RedirectOptions";

    public $data = array();

    public $model;

    public $max_filesize = 10000000;

    private $mime_types = array();

    private $allowed_extensions = array();

    private $error_report = array();

    private $upload_report = array();

    public function __construct() {
      $this->model = new BaseAdminModel();

      $this->set_mime_types();
      $this->set_allowed_ext();
    }

    public function init() {
      $this->data['page'] = $this->page;
      $this->data['redirects'] = $this->get_redirects();
      $this->data['wildcard'] = $this->model->get_option('isobar_redirects_wildcard');

      $this->create_menu();
    }

    public function init_redirects() {
      $this->perform_redirect();
    }

    public function _toHtml() {
      $admin_html = new BaseAdminHtml( $this->data );

      return $content = $admin_html->render();
    }

    public function create_menu() {
      add_options_page('Isobar Redirects', 'Isobar Redirects', 'manage_options', 'RedirectOptions', array($this,'_toHtml'));
    }

    public function save_redirects() {
      if( !$this->check_user_permissions() /*|| check_admin_referer('save_redirects', '_s301r_nonce')*/ )
        wp_die( 'You do not have sufficient permissions to access this page, or you have an invalid nonce.' );

      $redirects = array();
      $data = $_POST['isobar_redirects'];

      for($x = 0; $x < count($data['request']); $x++) {
        $request = trim( sanitize_text_field( $data['request'][$x] ) );
        $destination = trim( sanitize_text_field( $data['destination'][$x] ) );

        if(empty($request) || empty($destination))
          continue;

        $redirects[$request] = $destination;
      }

      $this->model->update_redirects( $redirects );
      $this->data['redirects'] = $this->model->get_redirects();

      if( $data['wildcard'] ) {
        $this->model->update_option('isobar_redirects_wildcard');
        $this->data['wildcard'] = $this->model->get_option('isobar_redirects_wildcard');
      } else {
        $this->model->delete_option('isobar_redirects_wildcard');
        $this->data['wildcard'] = $this->model->get_option('isobar_redirects_wildcard');
      }
    }

    public function perform_redirect() {
      $user_request = $this->get_user_request();

      $redirects = $this->model->get_redirects();
      $wildcard = $this->model->get_option('isobar_redirects_wildcard');

      foreach( $redirects as $request => $destination ) {
        $perform_redirect = false;

        // Check if address and protocol has been appended, and remove
        if( strpos($user_request, 'https://') !== false ) {
          $user_request = str_replace(get_site_url(), '', $user_request);
        }

        if( $wildcard && (strpos($request, '*') !== false) ) {
          if( (strpos($user_request, '/wp-login') !== 0) && (strpos($user_request, '/wp-admin') !== 0) ) {

            $request = str_replace('*','(.*)', $request);
            $pattern = '/^' . str_replace( '/', '\/', rtrim( $request, '/' ) ) . '/';
            $destination = str_replace('*','$1', $destination);
            $output = preg_replace($pattern, $destination, $user_request);

            if ($output !== $user_request) $perform_redirect = true; $destination = $output;

          }
        } elseif( urldecode($user_request) == rtrim($request, '/') ) {
          $perform_redirect = true;
        }

        if( $perform_redirect && trim($destination, '/') !== trim($user_request, '/') ) {
          // Check if domain need to be prepended
          if(strpos($destination, '/') === 0) {
            $home_parts = explode('?', home_url());
            $destination = rtrim($home_parts[0], '/').'/'.trim($destination,'/').'/';

            if(isset($home_parts[1])){
              $destination .= '?'.$home_parts[1];
            }
          }

          header('HTTP/1.1 301 Moved Permanently');
          header('Location: ' . $destination);
          exit();
        }
      }
    }

    public function import_csv() {
      $data = $_FILES['isobar_redirects_file'];
      $current_redirects = $this->get_redirects();

      $mime_types = $this->get_mime_types();
      $allowed_extensions = $this->get_allowed_ext();

      $temp = explode( ".", $data["name"] );
      $extension = end( $temp );

      if( empty($data) ) {
        $this->add_error_report( "File is empty." );
        return;
      }

      // O(1) lookup versus O(N)
      if( array_key_exists($data['type'], $mime_types) && ($data['size'] <= $this->max_filesize) && array_key_exists($extension, $allowed_extensions) ) {
        // Check errors
        if( $data['error'] > 0 ) {
          // Handle that..
          $this->add_error_report( "ERROR: Return Code: " . $data['error'] );
        }

        $this->add_upload_report( "Upload: " . $data['name'] );
        $this->add_upload_report( "Size: " . ($data['size'] / 1024) . "kb" );

        if( ($handle = fopen($data['tmp_name'], 'r')) !== false ) {
          while( ($data = fgetcsv($handle, 1000, ",")) !== false ) {
            if( !isset($current_redirects[$data[0]]) && !empty($data[1]) ) {
              $current_redirects[$data[0]] = $data[1];
              $this->add_upload_report( $data[0] . " was added to redirect to " . $data[1] );
            } else {
              // Redirect already exists, handle this
              $this->add_error_report( $data[0] . " already exists as a redirect." );
            }
          }
        }

        fclose( $handle );

        // Update redirects
        $this->model->update_redirects( $current_redirects );
        $this->data['redirects'] = $this->model->get_redirects();

      } else {
        if( !array_key_exists($data['type'], $mime_types) ) {
          // Invalid mime type, handle this
          $this->add_error_report( $data['type'] . " is not a valid file type, please choose a valid file type." );
        }
      }
    }

    public function export_csv() {
      // Export list of exisiting redirects
      $current_redirects = $this->get_redirects();
      $timestamp = date('Y/m/d H:i:s');

      header('Content-Type: application/excel');
      header('Content-Disposition: attachment; filename="isobar_301_redirects_'.$timestamp.'.csv"');

      $data = array();

      if(!empty($current_redirects)) {
        foreach( $current_redirects as $from => $to ) {
          $data[] = array( $from, $to );
        }
      } else {
        $data[] = array('from', 'to');
      }

      ob_end_clean();
      $fp = fopen('php://output', 'w');

      foreach ( $data as $line ) {
        fputcsv($fp, $line);
      }

      fclose($fp);
      exit();
    }

    private function add_upload_report( $report_message ) {
      $this->upload_report[] = $report_message;
    }

    private function get_upload_report() {
      return $this->upload_report;
    }

    private function add_error_report( $error_message ) {
      $this->error_report[] = $error_message;
    }

    private function get_error_report() {
      return $this->error_report;
    }

    private function get_user_request() {
      $user_request = str_ireplace( get_option('home'), '', $this->get_address() );
      return $user_request = rtrim( $user_request, '/' );
    }

    private function get_protocol() {
      return $protocol = ($_SERVER['SERVER_PORT'] == 80 || ($_SERVER['REQUEST_SCHEME'] == 'http')) ? 'http' : 'https';
    }

    private function get_address() {
      return $this->get_protocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function get_redirects() {
      return $this->model->get_redirects();
    }

    private function set_mime_types() {
      $this->mime_types = array(
        'application/csv'             => 'application/csv',
        'application/excel'           => 'application/excel',
        'application/ms-excel'        => 'application/ms-excel',
        'application/x-excel'         => 'application/x-excel',
        'application/vnd.ms-excel'    => 'application/vnd.ms-excel',
        'application/vnd.msexcel'     => 'application/vnd.msexcel',
        'application/octet-stream'    => 'application/octet-stream',
        'application/data'            => 'application/data',
        'application/x-csv'           => 'application/x-csv',
        'application/txt'             => 'application/txt',
        'plain/text'                  => 'plain/text',
        'text/anytext'                => 'text/anytext',
        'text/csv'                    => 'text/csv',
        'text/x-csv'                  => 'text/x-csv',
        'text/plain'                  => 'text/plain',
        'text/comma-separated-values' => 'text/comma-separated-values'
      );
    }

    private function set_allowed_ext() {
      $this->allowed_extensions = array(
        'csv' => 'csv'
      );
    }

    private function get_allowed_ext() {
      return $this->allowed_extensions;
    }

    private function get_mime_types() {
      return $this->mime_types;
    }

    private function check_user_permissions() {
      if( !current_user_can('manage_options') ) {
        return false;
      }

      return true;
    }

  }

}