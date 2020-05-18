<?php 
include('include.constants.php');

/*
 * Core
 * ----------------------------------------- */
include(ROOTDIR . 'system/core/Functions.php');
include(ROOTDIR . 'system/core/Loader.php');

/*
 * Config Files
 * ----------------------------------------- */
include(ROOTDIR . 'config/date.php');
include(ROOTDIR . 'config/security.php');
include(ROOTDIR . 'config/databases.php');
include(ROOTDIR . 'config/mail.php');
include(ROOTDIR . 'config/vendor.php');

/*
 * UTILS
 * ----------------------------------------- */
include(ROOTDIR . 'system/utils/Date.php');
include(ROOTDIR . 'system/utils/Byte.php');
include(ROOTDIR . 'system/utils/IO.php');
include(ROOTDIR . 'system/utils/File.php');
include(ROOTDIR . 'system/utils/Directory.php');

/*
 * VIEW
 * ----------------------------------------- */
include(ROOTDIR . 'system/view/EasyUI.php');

/*
 * CONSOLE
 * ----------------------------------------- */
include(ROOTDIR . 'system/console/Console.php');
include(ROOTDIR . 'system/console/Command.php');
include(ROOTDIR . 'system/console/Output.php');
include(ROOTDIR . 'system/console/output/Progress.php');

/*
 * AUTOLOADER
 * ----------------------------------------- */
include(ROOTDIR . 'system/database/Autoloader.php');

/*
 * CONSOLE SERVICE
 * ----------------------------------------- */
Pusaka\Console\Console::run($argv);
