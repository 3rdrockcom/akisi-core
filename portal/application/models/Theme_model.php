<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Theme_model extends CI_Model {

function config(){
      /**
       * Default settings
       */
      $this->lang->load('labels', "english");
      $settings_info = array(
        'smart-styles'        => 'smart-style-0',
        'fixed-header'        => 'fixed-header',
        'fixed-navigation'    => 'fixed-navigation',
        'fixed-ribbon'        => 'fixed-ribbon',
        'fixed-footer'        => '',
        'fixed-container'     => '',
        'rtl'                 => '',
        'topmenu'             => 'menu-on-top',
        'colorblind-friendly' => '',
        'background'          => '',
        'jarviswidget_color'  => 'jarviswidget-color-blue',
        'button_color'        => 'bg-color-blue',
        'button_txt_color'    => 'txt-color-white'
      );


      $template = array(
      'name'           => 'VIA CARTE',
      'version'        => '1.0',
      'author'         => 'Robert Ram Bolista',
      /**
       * css theme selection:
       * smart-style-0
       * smart-style-1
       * smart-style-2
       * smart-style-3
       * smart-style-4
       * smart-style-5
      */
      'smart-styles'     => $settings_info['smart-styles'],
      /**
       * fixed-header selection:
       * fixed-header
       * ''
      */
      'fixed-header'         => $settings_info['fixed-header'],
      /**
       * fixed-navigation selection:
       * fixed-navigation
       * ''
      */
      'fixed-navigation'     => $settings_info['fixed-navigation'],
      /**
       * fixed-ribbon selection:
       * ''
       * fixed-ribbon
      */
      'fixed-ribbon' => $settings_info['fixed-ribbon'],

      /**
       * fixed-footer selection:
       * ''
       * 'fixed-page-footer'
      */
      'fixed-footer'     => $settings_info['fixed-footer'],

      /**
       * fixed-container selection:
       * ''
       * 'container'
      */
      'fixed-container'     => $settings_info['fixed-container'],

      /**
       * rtl selection:
       * ''
       * 'smart-rtl'
      */
      'rtl'     => $settings_info['rtl'],

      /**
       * topmenu selection:
       * ''
       * 'menu-on-top'
      */
      'topmenu'     => $settings_info['topmenu'],

      /**
       * colorblind-friendly selection:
       * '' 
       * colorblind-friendly
      */
      'colorblind-friendly'     => $settings_info['colorblind-friendly'],

      /**
       * background url selection:
       * ''
       * img/pattern/graphy.png
       * img/pattern/tileable_wood_texture.png
       * img/pattern/sneaker_mesh_fabric.png
       * img/pattern/nistri.png
       * img/pattern/paper.png
      */
      'background'     => $settings_info['background'],

      /**
       * jarviswidget-color selection:
       * ''
       * jarviswidget-color-green
       * jarviswidget-color-greenDark
       * jarviswidget-color-greenLight
       * jarviswidget-color-purple
       * jarviswidget-color-magenta
       * jarviswidget-color-pink
       * jarviswidget-color-pinkDark
       * jarviswidget-color-blueLight
       * jarviswidget-color-teal
       * jarviswidget-color-blue
       * jarviswidget-color-blueDark
       * jarviswidget-color-darken
       * jarviswidget-color-yellow
       * jarviswidget-color-orange
       * jarviswidget-color-orangeDark
       * jarviswidget-color-red
       * jarviswidget-color-redLight
      */
      'jarviswidget_color'     => $settings_info['jarviswidget_color'],

      /**
       * button-color selection:
       * bg-color-green
       * bg-color-greenDark
       * bg-color-greenLight
       * bg-color-purple
       * bg-color-magenta
       * bg-color-pink
       * bg-color-pinkDark
       * bg-color-blueLight
       * bg-color-teal
       * bg-color-blue
       * bg-color-blueDark
       * bg-color-darken
       * bg-color-yellow
       * bg-color-orange
       * bg-color-orangeDark
       * bg-color-red
       * bg-color-redLight
       * btn-default
       * btn-primary
      */
      'button_color'     => $settings_info['button_color'],

      /**
       * button-txt-color selection:
       * ''
       * txt-color-green
       * txt-color-greenDark
       * txt-color-greenLight
       * txt-color-purple
       * txt-color-magenta
       * txt-color-pink
       * txt-color-pinkDark
       * txt-color-blueLight
       * txt-color-teal
       * txt-color-blue
       * txt-color-blueDark
       * txt-color-darken
       * txt-color-yellow
       * txt-color-orange
       * txt-color-orangeDark
       * txt-color-red
       * txt-color-redLight
      */
      'button_txt_color'     => $settings_info['button_txt_color']
      );
      return $template;
   }

   
}

/* End of file setup.php */
/* Location: ./application/models/setup.php */