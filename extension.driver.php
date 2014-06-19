<?php

  class Extension_Members_HybridAuth extends Extension{

    /**
     * about
     * @access public
     * @return void
     */
    public function about(){
      return array(
        'name' => 'Members with Hybrid Auth',
        'type' => 'Event',
        'version' => '0.1',
        'release-date' => '2014-06-11',
        'author' => array(
          'name' => 'Nick Ryall',
          'website' => 'nickryall.com.au',
          'email' => 'me@nickryall.com.au'),
        'description' => 'Integrate Members and Hybrid Auth.'
      );
    }

    /**
     * getSubscribedDelegates
     *
     * @see Toolkit\Extension::getSubscribedDelegates()
     * @access public
     * @return void
     */
    public function getSubscribedDelegates()
    {
      return array(
          array(
              'page' => '/system/preferences/',
              'delegate' => 'AddCustomPreferenceFieldsets',
              'callback' => 'appendPreferences'
          ),
          array(
              'page' => '/system/preferences/',
              'delegate' => 'Save',
              'callback' => 'savePreferences'
          )
      );
    }

   /**
     * install
     *
     * @access public
     * @return void
     */
    public function install()
    {
        Symphony::Configuration()->setArray(array('memebers_hybridauth' => self::$defaults));
        return Symphony::Configuration()->write();
    }

    /**
     * uninstall
     *
     * @access public
     * @return void
     */
    public function uninstall()
    {
        Symphony::Configuration()->remove('members_hybridauth');
        return Symphony::Configuration()->write();
    }


    /**
     * appendPreferences
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function appendPreferences($context)
    {
      $group = new XMLElement('fieldset');
      $group->setAttribute('class', 'settings');
      $group->appendChild(
        new XMLElement('legend', 'Members HybridAuth')
      );

      $facebookID = Widget::Label('Facebook ID');
      $facebookID->appendChild(Widget::Input(
        'settings[members_hybridauth][facebookid]', Extension_Members_HybridAuth::getFacebookID()
      ));
      $group->appendChild($facebookID);

      $facebookSecret = Widget::Label('Facebook Secret');
      $facebookSecret->appendChild(Widget::Input(
        'settings[members_hybridauth][facebooksecret]', Extension_Members_HybridAuth::getFacebookSecret()
      ));
      $group->appendChild($facebookSecret);

      $googleID = Widget::Label('Google ID');
      $googleID->appendChild(Widget::Input(
        'settings[members_hybridauth][googleid]', Extension_Members_HybridAuth::getGoogleID()
      ));
      $group->appendChild($googleID);

      $googleSecret = Widget::Label('Google Secret');
      $googleSecret->appendChild(Widget::Input(
        'settings[members_hybridauth][googlesecret]', Extension_Members_HybridAuth::getGoogleSecret()
      ));
      $group->appendChild($googleSecret);

      $context['wrapper']->appendChild($group);
    }

    /**
     * savePreferences
     *
     * @param Mixed $context
     * @param Mixed $override
     * @access public
     * @return void
     */
    public function savePreferences($context, $override = false)
    {
        foreach ($context['settings']['members_hybridauth'] as $key => $val) {
            Symphony::Configuration()->set($key, $val, 'members_hybridauth');
        }
        Symphony::Configuration()->write();
    }

  /*-------------------------------------------------------------------------
    Utilities:
  -------------------------------------------------------------------------*/

    public static function getFacebookID() {
      return Symphony::Configuration()->get('facebookid', 'members_hybridauth');
    }

    public static function getFacebookSecret() {
      return Symphony::Configuration()->get('facebooksecret', 'members_hybridauth');
    }

    public static function getGoogleID() {
      return Symphony::Configuration()->get('googleid', 'members_hybridauth');
    }

    public static function getGoogleSecret() {
      return Symphony::Configuration()->get('googlesecret', 'members_hybridauth');
    }
  }