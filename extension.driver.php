<?php

  class Extension_Members_HybridAuth extends Extension{

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
  }