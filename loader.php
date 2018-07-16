<?php
/**
 * Plugin Name: Millionaire's Digest Matchmaker
 * Description: Give users the ability to find friends and accounts based on their same passions and interests.
 * Author: K&L (Founder of the Millionaire's Digest)
 * Author URI: https://millionairedigest.com/
 * Version: 1.0.0
 */

  require_once('class-mp-bp-match.php');

  if(class_exists('Mp_BP_Match')){
       new Mp_BP_Match();
  }
