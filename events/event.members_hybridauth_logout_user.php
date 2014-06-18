<?php

  require_once(TOOLKIT . '/class.event.php');

  Class eventmembers_hybridauth_logout_user extends Event{

    const ROOTELEMENT = 'members-hybridauth-logout-user';

    public $eParamFILTERS = array(
    );


    public static function about(){
      return array(
        'name' => 'Members HybridAuth: Logout User',
        'author' => array(
          'name' => 'Nick Ryall',
          'website' => 'http://nickryall.com.au',
          'email' => 'me@nickryall.com.au'),
        'version' => '0.1',
        'release-date' => '2014-06-11',
        'trigger-condition' => 'action[members-hybridauth-logout-user]'
      );
    }

    public static function documentation(){
      return '';
    }

    public function load(){
      if(isset($_GET['logout']) && $_GET['logout'] == 'true') {
        return $this->__trigger();
      }
    }

    protected function __trigger(){
      // Hybrid Auth
      require_once( './hybridauth/Hybrid/Auth.php' );

      // Hybrid auth config path
      $config = './hybridauth/config.php';

      // Initialize Hybrid_Auth with a given file
      $hybridauth = new Hybrid_Auth( $config );

      Hybrid_Auth::logoutAllProviders();

      redirect(URL . '?member-action=logout');
    }
  }
