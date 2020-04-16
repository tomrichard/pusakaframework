<?php 
namespace Pusaka\Database;

define('DB_ROOT_SYSTEM_FOLDER', strtr(__DIR__, ['\\' => '/']) . '/' );

$driver_installed = ['mysql', 'mongodb']; 

/*
 * EXCEPTIONS
 * ----------------------------------------------------- */
include(DB_ROOT_SYSTEM_FOLDER . 'exceptions/DatabaseException.php');
include(DB_ROOT_SYSTEM_FOLDER . 'exceptions/ConnectionException.php');
include(DB_ROOT_SYSTEM_FOLDER . 'exceptions/SqlException.php');

/*
 * FACTORY
 * ----------------------------------------------------- */
include(DB_ROOT_SYSTEM_FOLDER . "factory/DriverInterface.php");
include(DB_ROOT_SYSTEM_FOLDER . "factory/BuilderInterface.php");
include(DB_ROOT_SYSTEM_FOLDER . "factory/ResultInterface.php");

/*
 * BLUEPRINT
 * ----------------------------------------------------- */
include(DB_ROOT_SYSTEM_FOLDER . "blueprint/Column.php");
include(DB_ROOT_SYSTEM_FOLDER . "blueprint/Value.php");
include(DB_ROOT_SYSTEM_FOLDER . "blueprint/Table.php");

/*
 * DRIVERS
 * ----------------------------------------------------- */
foreach ($driver_installed as $driver) :
include(DB_ROOT_SYSTEM_FOLDER . "drivers/{$driver}/builder.php");
include(DB_ROOT_SYSTEM_FOLDER . "drivers/{$driver}/factory.php");
include(DB_ROOT_SYSTEM_FOLDER . "drivers/{$driver}/result.php");
include(DB_ROOT_SYSTEM_FOLDER . "drivers/{$driver}/driver.php");
endforeach;

include(DB_ROOT_SYSTEM_FOLDER . 'Constant.php');
include(DB_ROOT_SYSTEM_FOLDER . 'Model.php');
include(DB_ROOT_SYSTEM_FOLDER . 'Manager.php');