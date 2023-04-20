<?php 

 trigger_error('Using deprecated environment', E_USER_DEPRECATED);

/**
 * Environments are a way to store multiple settings for a single application
 * and several machines. We can even set automatic environment detection to 
 * make the process of switching the settings for several servers seamless.
 * 
 * @package Spitfire.settings
 * @author  César de la Cal <cesar@magic3w.com>
 */

class_alias('\spitfire\core\Environment', '\spitfire\environment');