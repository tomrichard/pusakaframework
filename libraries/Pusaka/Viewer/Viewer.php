<?php 
namespace Pusaka\Library;

include('SqlFormatter.php');
include('Parsedown.php');

use SqlFormatter;
use Parsedown;

class Viewer {

	public static $sql 		= SqlFormatter::class;

	public static $markdown = Parsedown::class;

}