<?php

/**
 * CKEditor MyBB Plugin
 * Copyright 2014 My-BB.Ir Group, All Rights Reserved
 *
 * Website: http://my-bb.ir
 *
 * $Id AliReza_Tofighi$
 */

// Disallow direct access to this file for security reasons
if (! defined('IN_MYBB')) {
    exit('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

define('CKEDITOR_PLUGINROOT', MYBB_ROOT.'inc/plugins/ckeditor/');

require_once MYBB_ROOT.'inc/plugins/ckeditor/core.php';
require_once MYBB_ROOT.'inc/plugins/ckeditor/hooks.php';
require_once MYBB_ROOT.'inc/plugins/ckeditor/info.php';
require_once MYBB_ROOT.'inc/plugins/ckeditor/install.php';
