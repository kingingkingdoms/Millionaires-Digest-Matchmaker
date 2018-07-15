<?php
/**
 * Plugin Name: BuddyPress Matchmaking
 * Plugin URI: https://meshpros.com/
 * Description: Custom Plugin for BuddyPress profile matching
 * Author: Muhammad Kashif
 * Author URI: http://muhammadkashif.com/
 * Version: 1.1.5
 */

  require_once('class-mp-bp-match.php');

  if(class_exists('Mp_BP_Match')){
       new Mp_BP_Match();
  }
