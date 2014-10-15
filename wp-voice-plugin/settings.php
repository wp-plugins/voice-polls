<?php
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
    	add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    	add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
    	add_options_page(
    		'Settings Admin', 
    		'Voice Polls', 
    		'manage_options', 
    		'voice', 
    		array( $this, 'create_admin_page' )
    		);
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
    	$this->options = get_option( 'voice_polls' );
        //$this->options = false;
        //var_dump($this->options);
      ?>
      <div class="wrap">
        <script type="text/javascript">
          var appBanners = document.getElementsByClassName('updated'), i;
          for (var i = 0; i < appBanners.length; i ++) {
            appBanners[i].style.display = 'none';
          }
        </script>
        <?php screen_icon(); ?>
        <h2>Voice: Give and Gather opinions</h2>
        <form method="post" action="options.php">
         <?php
            // This prints out all hidden setting fields
         settings_fields( 'voice_option_group' );
         do_settings_sections( 'voice' );
         submit_button(); 
         ?>
       </form>
     </div>
     <?php
   }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
    	register_setting(
            'voice_option_group', // Option group
            'voice_polls', // Option name
            array( $this, 'sanitize' ) // Sanitize
            );

    	add_settings_section(
            'setting_section_id', // ID
            'Basic Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'voice' // Page
            );

    	add_settings_field(
            'token', // ID
            'Account', // Title 
            array( $this, 'token_callback' ), // Callback
            'voice', // Page
            'setting_section_id' // Section           
            );  

    	add_settings_field(
    		'theme_bg', 
    		'Theme', 
    		array( $this, 'theme_callback' ), 
    		'voice', 
    		'setting_section_id'
    		);      


    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
      //var_dump($input);
      $new_input = array();
      if( isset( $input['token'] ) ){
        $new_input['token'] = sanitize_text_field( $input['token'] );
            //try{
        $data = json_decode(file_get_contents('https://api.voicepolls.com/1/account?xorigin=true&access_token='.$new_input['token']));
        if($data->meta != 200){
          $new_input['invalid'] = true;
        }else{
          unset($new_input['invalid']);
          $new_input['id'] = $data->response->id;
          $new_input['username'] = $data->response->username;
        }
            // }catch(Exception $e){
            //     $new_input['invalid'] = true;
            // }
      }else{
        $new_input['invalid'] = true;
      }

      if( isset( $input['theme_bg'] ) )
        $new_input['theme_bg'] = sanitize_text_field( $input['theme_bg'] );

      if( isset( $input['theme_1'] ) )
        $new_input['theme_1'] = sanitize_text_field( $input['theme_1'] );

        //absint()
      return $new_input;
    }

  /**
     * Print the Section text + validation
     */
  public function print_section_info()
  {

  }

/**
     * Get the settings option array and print one of its values
     */
public function token_callback()
{
  if(isset($this->options['invalid']) || !$this->options['username']){ ?>
  <script>
    window.addEventListener("message", function(ev) {
      if(typeof ev.data === 'object' && ev.data.token){
        jQuery('#voice_token').val(ev.data.token);
      }
    });


    jQuery(function(){
      jQuery('#voice_token').val('');
    })



    var voicepopup = function(){
      jQuery(function(){
            //jQuery('#voice_token').val('etienne');
            //jQuery("#submit").trigger('click');

            var child = window.open('https://voicepolls.com/login?redirect=/popups/login/WPlogin.html','','toolbar=0,status=0,width=550,height=650');
            setTimeout(function(){
              child.focus();
            }, 100);
            var timer = setInterval(checkChild, 20);

            function checkChild() {
              if (child.closed) {
                jQuery("#submit").trigger('click');
                //alert("Child window closed");   
                clearInterval(timer);
              }
            }
          })
    }

  </script>
  <a onclick='voicepopup();' href='#'>Click here to setup</a>.
  <?php
}else{
  print '<a href="https://voicepolls.com/user/'.$this->options['username']. '" target="_blank">@'.$this->options['username']. '</a><br><br>Would you like to change account? <a onclick="window.voice_logout();" href="#">Logout</a>';
  ?>
  <script type="text/javascript">
   jQuery(function(){
    window.voice_logout = function(){
      jQuery.get('https://api.voicepolls.com/1/account/logout?xorigin=true&access_token='+jQuery("#voice_token").val(), function(){
        jQuery("#voice_token").val('');
        jQuery("#submit").trigger('click');
      });
      
    }
  })
</script>
<?php
}
printf(
  '<input type="text" id="voice_token" name="voice_polls[token]" value="%s" style="display: none"/>',
  isset( $this->options['token'] ) ? esc_attr( $this->options['token']) : ''
  );
}

/**
     * Get the settings option array and print one of its values
     */
public function theme_callback()
{
  if(!isset($this->options['invalid']) && $this->options['username']){
    $data = json_decode(file_get_contents('https://api.voicepolls.com/1/account?xorigin=true&access_token='.$this->options['token']));
        //var_dump($data->response);
    $items = $data->response->settings->embeds;
    //var_dump($items->background_color);
        // var_dump($items);
    print '<p style="line-height: 50px">Background color<br><input class="poutsch_theme" type="color" id="poutsch_theme_bg" name="voice_polls[theme_bg]" value="'.$items->background_color.'" style="width: 50px; height: 50px"></p>';
    print '<p style="line-height: 50px">Question/text color<br><input class="poutsch_theme" type="color" id="poutsch_theme_1" name="voice_polls[theme_1]" value="'.$items->primary_color.'" style="width: 50px; height: 50px"></p>';
    ?>
    <script>
      jQuery(function(){
        jQuery('.poutsch_theme').on('change', function(){
          //alert(jQuery("#voice_token").val())
          jQuery.ajax({
            url: 'https://api.voicepolls.com/1/account?xorigin=true',
            method: 'PUT',
            data: {
              access_token: jQuery("#voice_token").val(),
              settings: {
                embeds: {
                  background_color: jQuery('#poutsch_theme_bg').val(),
                  primary_color: jQuery('#poutsch_theme_1').val()
                }
              }
            }
          })
        })
      })
    </script>
    <?php
    print '<br><br>Make sure the colors have a strong contrast (eg. black/white)';

  }else{
    print 'You must be logged in to access this option';
  }
}
}