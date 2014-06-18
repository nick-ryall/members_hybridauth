<?php

  require_once(TOOLKIT . '/class.event.php');

  Class eventmembers_hybridauth_create_user extends Event{

    const ROOTELEMENT = 'members-hybridauth-create-user';

    public static $Members;

    public $eParamFILTERS = array(
      'xss-fail'
    );

    public static function about(){
      return array(
        'name' => 'Members HybridAuth: Create User',
        'author' => array(
          'name' => 'Nick Ryall',
          'website' => 'http://nickryall.com.au',
          'email' => 'me@nickryall.com.au'),
        'version' => '0.1',
        'release-date' => '2014-06-11',
        'trigger-condition' => 'action[members-hybridauth-create-user]'
      );
    }

    public static function getSource(){
      return '11';
    }

    public static function documentation(){
      return '';
    }

    public function load(){
      if(isset($_GET['action'][self::ROOTELEMENT]) && isset($_GET['provider'])) return $this->__trigger();
    }

    protected function __trigger(){
      // Hybrid Auth
      require_once( './hybridauth/Hybrid/Auth.php' );

      // Create the redirect URL based off the current path.
      $page = Frontend::instance() -> Page();
      $current_path = $page->_param['current-path'];

      if( $current_path == '/signin' || $current_path == '/signup' ) {
        $redirect_url = URL;
      } else {
        $redirect_url = URL . $current_path;
      }

      // Members Driver
      self::$Members = Symphony::ExtensionManager()->create('members');
      // Multiple Members Sections. Use the 'Users' section.
      self::$Members->setMembersSection(11);

      $errors = array();

      try {
        // Provider name.
        $provider_name = $_GET['provider'];

        // Hybrid auth config path
        $config = './hybridauth/config.php';

        // Initialize Hybrid_Auth with a given file
        $hybridauth = new Hybrid_Auth( $config );

        // Try to authenticate with the selected provider
        $adapter = $hybridauth->authenticate( $provider_name );
   
        // then grab the user profile 
        $user_profile = $adapter->getUserProfile();

        if(!$user_profile) return;

        // If a Member is already logged in and another Login attempt is requested
        // log the Member out first before trying to login with new details.
        // This will prevent when the session has ended off site, but the
        // Symphony session still lives on.
        // This is also double protection as there is logic done with JS to prevent
        // this as well.
        self::$Members->getMemberDriver()->logout();

        try {

          $member = $this->findMemberByEmail($user_profile->email);

          // Found an existing member. Attempt to login.
          if ($member) {

            $creds = $this->getMemberCredentials($member);

            // Member login
            self::$Members->getMemberDriver()->login($creds, true);

            // Redirect to current path or home screen.
            redirect($redirect_url);

          } else {

            // Generate a random password.
            $new_password = $this->generatePassword();
            
            // Fake the $_POST
            $_POST = array(
              'fields' => array(
                'provider' => $adapter->id,
                'identifier' => $user_profile->identifier,
                'name' => $user_profile->displayName,
                'email' => $user_profile->email,
                'password' => array(
                  'password' => $new_password,
                  'confirm' => $new_password
                ),
                'role' => 3
              ),
              'action' => array(
                self::ROOTELEMENT => true
              )
            );

            include(TOOLKIT . '/events/event.section.php');

            $member = $this->findMemberByEmail($user_profile->email);

            // Found an existing member. Attempt to login.
            if ($member) {
               $creds = $this->getMemberCredentials($member);
            }

            if($result->getAttribute('result') == 'success' && self::$Members->getMemberDriver()->login($creds, true)) {
              redirect($redirect_url);
            } else {
              var_dump($result); exit;
              Symphony::$Log->pushToLog('HybridAuthApiException: With ' . $result, E_ERROR, true);
            }
          }

        } 
        catch( Exception $ex ){
          var_dump($ex); exit;
          Symphony::$Log->pushToLog('HybridAuthApiException: With ' . $e->getMessage(), E_ERROR, true);
        }
      }
      catch( Exception $ex ){
        var_dump($ex); exit;
        Symphony::$Log->pushToLog('HybridAuthApiException: With ' . $e->getMessage(), E_ERROR, true);
      }
    }

    public function findMemberByEmail($email) {
      // Fetch the member ID from the HybridAuth email address
      $id = self::$Members->getField('email')->fetchMemberIDBy($email);

      // Fetch the member entry data
      $member = self::$Members->getMemberDriver()->fetchMemberFromID($id);

      if (is_array($id))
        $id = current($id);

      // Fetch the member entry data
      return self::$Members->getMemberDriver()->fetchMemberFromID($id);
    }

    public function getMemberCredentials($member) {
      $credentials = $member->getData();

      $email_field = self::$Members->getField('email')->get('id');
      $authentication_field = self::$Members->getField('authentication')->get('id');

      $email = $credentials[$email_field]['value'];
      $password = $credentials[$authentication_field]['password'];

      // Populate an array with data to use for logging in the member
      $creds = array();
      $creds['email'] = $email;
      $creds['password'] = $password;

      return $creds;
    }

    public function generatePassword($length = 8) {
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $count = mb_strlen($chars);

      for ($i = 0, $result = ''; $i < $length; $i++) {
          $index = rand(0, $count - 1);
          $result .= mb_substr($chars, $index, 1);
      }

      return $result;
    }

  }
