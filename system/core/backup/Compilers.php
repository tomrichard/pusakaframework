<?php 
namespace Pusaka\Wapps;

class Compilers {

	public static function css($xcss = []) {

		if(is_null($xcss)) {
			return '';
		}

		if(!is_array($xcss)) {
			return '';
		}

		if(!is_assoc($xcss)) {
			return '';
		}

		$make = function($before, $parent, $xcss, $closure, &$css) {

			if(is_string($xcss)) {
				return $xcss;
			}

			foreach ($xcss as $key => $value) {

				// selector
				//--------------------
				if(!is_string($value)) {
					$before  = $parent;
					$parent .= $key . ' ';
					$closure($before, $parent, $xcss[$key], $closure, $css);
					$parent  = $before;	
				}else {

					foreach ($xcss as $k => $v) {

						if(is_string($v)) {
							$css[$parent][] = str_replace_first('-', '', $k) . ' : ' . $v;
							unset($xcss[$k]);
						}
					
					}

				}

				// // selector : class
				// //-----------------

				// if( 
				// 	preg_match('/'.$selector.'/', $key) > 0 
				// ) {
				// 	$before  = $parent;
				// 	$parent .= $key . ' ';
				// 	$closure($before, $parent, $xcss[$key], $closure, $css);
				// 	$parent  = $before;
				// }

				// // values
				// //-----------------
				// else {

				// 	foreach ($xcss as $k => $v) {
						
				// 		if(preg_match('/^\-[\-|\w]+/', $k) > 0) {
				// 			$css[$parent][] = str_replace_first('-', '', $k) . ' : ' . $v;
				// 			unset($xcss[$k]);
				// 		}

				// 	}

				// }

			}

			return $css;

		};

		$render = function($schemacss) {

			$rcss = '';

			foreach ($schemacss as $key => $value) {
				
				$rcss .= 
					$key 
					.'{'. 	PHP_EOL
					.		implode(';'. PHP_EOL, $value) . ';' . PHP_EOL  
					.'}'. 	PHP_EOL
				;

				unset($schemacss[$key]);
			
			}

			unset($schemacss);

			return $rcss;

		};

		$css = [];
		$css = $make('', '', $xcss, $make, $css);
		$css = $render($css);
		
		return $css;

	} 

}