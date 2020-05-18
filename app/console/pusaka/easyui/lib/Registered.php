<?php 
namespace Pusaka\EasyUi\Lib;

use Pusaka\Utils\IOUtils;

class Registered {

	static function directiveList() {

		$directives = [
			[
				'begin' 	=> '/@url\s*\((.*?)\s*\)/',
				'resolve'	=> function($match) {

					return '<?php echo url('.$match[1].') ?>';

				}
			],
			[
				'begin' 	=> '/@d\s*\((.*?)\s*\)/',
				'resolve'	=> function($match) {

					return '<?php d('.$match[1].') ?>';

				}
			]
		];

		return $directives;

	}

	static function pipeList() {
		
		$pipes = [
		];

		return $pipes;

	}

	static function componentList() {
		
		$dir 		= ROOTDIR . 'app/web/components/';

		$components = [];

		IOUtils::directory($dir)
			->filter('/.*\.easyui\.php$/')
			->scan(true, function($file) use (&$components, $dir) {

				$pattern   		= '/^'.strtr($dir, ['/' => '\/']).'/'; 

				$namespace 		= preg_replace($pattern, '', $file);

				$namespace 		= preg_replace('/\.easyui\.php$/', '', $namespace);

				$namespace 		= strtr($namespace, ['/' => '.']);

				$components[$namespace] = file_get_contents($file);

			});

		return $components;

	}

}