<?php
/*
Plugin Name: Voice
Plugin URI: wordpress.org/extend/plugins/voice/
Description: Give and gather opinions.
Version: 1.0.1
Author: Poutsch Corp.
Author URI: https://voicepolls.com
License: GPL2
*/


// v1.0 : First Release

// Create shortcode handler for Voice
// e.g [voicepoll id="xxx"]
// error_reporting(-1);
// ini_set('display_errors', 'On');

require( dirname( __FILE__ ) .'/wp-voice-plugin/settings.php');

function addvoice($atts) {

  extract(shortcode_atts(array(
    'id' => ''
    ), $atts));

  $author = get_option( 'voice_polls' )['id'];

  return "<p class='poutsch_question' style='margin-bottom:15px'><a href='https://voicepolls.com/question/".$id."' target='_blank' class='poutsch_publisher_".$author."'>https://voicepolls.com/question/".$id."</a></p><script type='text/javascript'>if(window.voiceLoad){window.voiceLoad();}</script>";
}

add_shortcode('voicepoll', 'addvoice');



// Add Voice media button
// function media_buttons() {
//   echo " <a href='http://voice.ee?page=voice&iframe&TB_iframe=false' onclick='return false;' id='add_poll' class='button thickbox' title='Add poll'><img src='img/polldaddy@2x.png' width='15' height='15' alt='Add poll' style='margin: -2px 0 0 -1px; padding: 0 2px 0 0; vertical-align: middle;' /> Add Voice </a>";
// }

// Add Voice button to MCE
function add_voice_button() {
  if( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
    return;

  if(get_user_option('rich_editing') == 'true') {
    add_filter('mce_external_plugins', 'add_voice_tinymce_plugin');
    add_filter('mce_buttons', 'register_voice_button');
  }
}

function register_voice_button($buttons) {
  array_push($buttons, "|", "voiceEmbed");
  return $buttons;
}

function add_voice_tinymce_plugin($plugin_array) {
  if(get_option( 'voice_polls' )){
    if(isset(get_option( 'voice_polls' )['token'])){
      ?>
      <script type="text/javascript">
        var voice_token = '<?php echo get_option( 'voice_polls' )['token']; ?>';
        var voice_theme = '<?php echo get_option( 'voice_polls' )['theme']; ?>';
      </script>
      <?php
    }
  }
  $plugin_array['voiceEmbed'] = plugins_url( 'wp-voice-plugin/editor_plugin.js', __FILE__);
  return $plugin_array;
}
add_action('init', 'add_voice_button');

// Incorrect setup Warning
function voice_login_warning() {
  global $cache_enabled;
  $page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';
  if(function_exists( "admin_url" )){
    if (false == get_option( 'voice_polls' ) )
      echo '<div class="updated"><p><strong>' . sprintf( __( 'Warning! The Voice Polls plugin is not setup. Visit the <a href="%s">plugin settings page</a> to complete setup.', 'voice' ), admin_url( 'options-general.php?page=voice' ) ) . '</strong></p></div>';
    if(get_option( 'voice_polls' ) && isset(get_option( 'voice_polls' )['invalid'])){
      echo '<div class="updated"><p><strong>' . sprintf( __( 'Warning! The Voice Polls plugin is not properly setup. Visit the <a href="%s">plugin settings page</a> to fix the issue.', 'voice' ), admin_url( 'options-general.php?page=voice' ) ) . '</strong></p></div>';
    }
  }
}

// Add settings menu to Wordpress
  if ( is_admin() ){ // admin actions
    add_action( 'admin_notices', 'voice_login_warning' ); //TODO: Add a manager tab
    $my_settings_page = new MySettingsPage();
  } else {
    // non-admin enqueues, actions, and filters

  }

   $voice_active = plugins_url('/wp-voice-plugin/wp-voice-include.js', __FILE__); //this add the voice javascript loader to every page on the blog
   wp_enqueue_script('voice-include', $voice_active,  false, false);
