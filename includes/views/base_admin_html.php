<?php

if( !class_exists('BaseAdminHtml') ) {

  class BaseAdminHtml {

    public $page;
    public $redirects;
    public $wildcard;

    public function __construct( $data ) {
      $this->page = $data['page'];
      $this->redirects = $data['redirects'];
      $this->wildcard = $data['wildcard'];
    }

    public function render() {
      $this->render_options_page();
    }

    private function render_current_redirects() {
      $content = '';
      $count = 1;

      if( $this->redirects ) {
        foreach( $this->redirects as $request => $destination ) {
          $content .=
            '<tr class="js-redirect">
              <td style="width: 2%;">'.$count.'.</td>
              <td style="width: 35%;"><input type="text" name="isobar_redirects[request][]" value="'.$request.'" style="width:99%" /></td>
              <td style="width: 2%;">&raquo;</td>
              <td style="width: 58%;"><input type="text" name="isobar_redirects[destination][]" value="'.$destination.'" style="width:99%;" /></td>
              <td style="width: 2%;"><span class="delete-redirect js-delete-redirect">Delete</span></td>
            </tr>';
          $count++;
        }
      }

      return $content;
    }

    private function render_options_page() {
    ?>
      <div class="wrap">
        <div class="logo-container">
          <img src="<?php echo plugins_url('../assets/img/isobar-logo.png', dirname(__FILE__)); ?>">
        </div>
        <h1 class="isobar-redirect-title">Isobar Redirects</h1>

          <div class="current-total-redirects">
            <span>Current Total Redirects: </span><strong><?php echo count($this->redirects); ?></strong>
          </div>

          <div class="isobar-redirect-uploads">
            <div class="redirect-block">
              <div class="js-isobar-upload-redirects" data-toggle="modal" data-target="#squarespaceModal">Upload Redirects File</div>
            </div>
            <div class="redirect-block">
              <div class="js-isobar-export-redirects">Export Exisiting Redirects</div>
            </div>
            <div class="redirect-block">
              <div class="js-delete-redirects redirect-warning">Delete All Redirects</div>
            </div>
            <div class="redirect-block -options">
              <div class="js-jump-bottom">Jump to Bottom</div>
            </div>
          </div>

          <form method="post" class="js-isobar-redirect-form" action="options-general.php?page=<?php echo $this->page; ?>&savedata=true">

          <?php wp_nonce_field( 'save_redirects', '_s301r_nonce' ); ?>

          <table class="widefat">
            <thead>
              <tr>
                <th colspan="2">Request</th>
                <th colspan="2">Destination</th>
              </tr>
            </thead>
            <tbody class="js-redirect-body">
              <tr>
                <td colspan="2"><small>example: /home.html</small></td>
                <td colspan="2"><small>example: <?php echo get_option('home'); ?>/home/</small></td>
              </tr>

              <?php echo $this->render_current_redirects(); ?>

              <tr class="js-redirect">
                <td style="width: 2%;"></td>
                <td style="width: 35%;"><input type="text" name="isobar_redirects[request][]" value="" style="width:99%;" /></td>
                <td style="width: 2%;">&raquo;</td>
                <td style="width: 58%;"><input type="text" name="isobar_redirects[destination][]" value="" style="width:99%;" /></td>
                <td><span class="delete-redirect js-delete-redirect">Delete</span></td>
              </tr>
            </tbody>
          </table>

          <div class="add-redirect">
            <div class="js-add-redirect button-primary">Add Redirect</div>
            <?php $wildcard_checked = ($this->wildcard) ? ' checked="checked"' : ''; ?>
            <p><input type="checkbox" name="isobar_redirects[wildcard]" <?php echo $wildcard_checked; ?> /><label for="wps301-wildcard"> Use Wildcards?</label></p>

            <p class="submit"><input type="submit" name="submit_301" class="button-primary js-submit-changes" value="<?php _e('Save Changes') ?>" /></p>
          </div>

          </form>
        </div>
      </div>
    <?php
      $this->render_upload_modal();
      $this->render_export_content();
      $this->render_assets();
    }

    private function render_upload_modal() {
    ?>
      <div class="modal js-redirect-upload-modal">
        <div class="modal-content">
          <span class="close">&times;</span>
          <form role="form" action="options-general.php?page=<?php echo $this->page; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label>Select File: </label>
              <input type="file" name="isobar_redirects_file">
            </div>
            <div class="form-group">
              <input type="submit" name="redirects_upload_submit" class="button-primary">
            </div>
          </form>
        </div>
      </div>
    <?php
    }

    private function render_export_content() {
    ?>
      <div class="isobar-redirects-export -hidden">
        <form role="form" method="post" class="js-submit-export" action="options-general.php?page=<?php echo $this->page; ?>">
          <input type="hidden" name="redirects_export_submit" value="">
        </form>
      </div>
    <?php
    }

    private function render_assets() {
    ?>
      <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('../dist/styles/main.css', dirname(__FILE__)); ?>">
      <script type="text/javascript" src="<?php echo plugins_url('../dist/scripts/main.js', dirname(__FILE__)); ?>"></script>
      <style type="text/css">
        #wpfooter {
          display: none;
        }
      </style>
    <?php
    }

  }

}