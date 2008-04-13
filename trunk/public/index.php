<?php

/**
 * Scoutpad
 *
 * LICENSE
 *
 * This source file is subject to the New-BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   /
 * @package    Scoutpad
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */

if ( version_compare(phpversion(), '5.2.0', '<') ) {
	echo  ' <div style="font:12px/1.35em arial, helvetica, sans-serif;">
				<div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
					<h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">Whoops, it looks like you have an invalid PHP version.</h3>
				</div>
				<p>Scoutpad application request PHP 5.2.0 or newer.</p>
			</div>';
	exit;
}


// Es: home/workspace/Scout/ScoutPad indica dove si trova il direttorio dell'applicazione!!
define('BASE_DIRECTORY',dirname(dirname(__FILE__))); 

require_once(BASE_DIRECTORY.'/application/Scoutpad.php');

Scoutpad::getInstance()->run();

?>