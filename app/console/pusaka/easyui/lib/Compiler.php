<?php 
namespace Pusaka\Easyui\Lib;

class Compiler {

	static function compile($text) {

		$tokens   = [
			// if
			[
				'pattern' => '/@d\s*(\([\w\s\(\)\=\$\"\'><-]+\))/',
				'replace' => '<pre><?php print_r($1); ?></pre>'
			],
			// echo 
			[
				'pattern' => '/<<\s*([\[\]\w\s\(\)\=\$\"\'><-]+)\s*>>/',
				'replace' => '<?php echo $1; ?>'
			],
			// if
			[
				'pattern' => '/@if\s*(\([\w\s\(\)\=\$\"\'><-]+\))/',
				'replace' => '<?php if $1 : ?>'
			],
			// else if
			[
				'pattern' => '/@elif\s*(\([\w\s\(\)\=\$\"\'><-]+\))/',
				'replace' => '<?php elseif $1 : ?>'
			],
			// else
			[
				'pattern' => '/@else/',
				'replace' => '<?php else : ?>'
			],
			// else
			[
				'pattern' => '/@endif;/',
				'replace' => '<?php endif; ?>'
			],
			// isset
			[
				'pattern' => '/@isset\s*\(([\w\s\(\)\=\$\"\'><-]+)\)/',
				'replace' => '<?php if ( isset($1) ) : ?>'
			],
			// endisset
			[
				'pattern' => '/@endisset;/',
				'replace' => '<?php endif; ?>'
			],
			// empty
			[
				'pattern' => '/@empty\s*\(([\w\s\(\)\=\$\"\'><-]+)\)/',
				'replace' => '<?php if ( empty($1) ) : ?>'
			],
			// endempty
			[
				'pattern' => '/@endempty;/',
				'replace' => '<?php endif; ?>'
			],
			// foreach
			[
				'pattern' => '/@foreach\s*(\([\w\s\(\)\=\$\"\'><-]+\))/',
				'replace' => '<?php foreach $1 : ?>'
			],
			// endforeach
			[
				'pattern' => '/@endforeach;/',
				'replace' => '<?php endforeach; ?>'
			],
			// for
			[
				'pattern' => '/@for\s*(\([\w\s\(\)\=\$\"\'><+-;,]+\))/',
				'replace' => '<?php for $1 : ?>'
			],
			// endfor
			[
				'pattern' => '/@endfor;/',
				'replace' => '<?php endfor; ?>'
			],
			// for
			[
				'pattern' => '/@while\s*(\([\w\s\(\)\=\$\"\'><+-;,]+\))/',
				'replace' => '<?php while $1 : ?>'
			],
			// endfor
			[
				'pattern' => '/@endwhile;/',
				'replace' => '<?php endwhile; ?>'
			],
			// break
			[
				'pattern' => '/@break;/',
				'replace' => '<?php break; ?>'
			],
			[
				'pattern' => '/@json\s*(\([\w\s\(\)\=\$\"\'><-]+\))/',
				'replace' => '<?php echo json_encode$1; ?>'
			],
			[
				'pattern' => '/("\s*)@url\s*(\(.*\))(\s*")/',
				'replace' => '"<?php echo url$2; ?>"'
			],
			/*
			[
				'pattern' => '/@url\s*(\(.*\))/',
				'replace' => '<?php echo url$1; ?>'
			]
			*/
			
		];

		$compiled = $text;

		$lines 	  = preg_split("/((\r?\n)|(\r\n?))/", $text);

		$custom   = false;

		foreach ($lines as $i => $line) {

			// custom component
			//--------------------------------------------------------------
			if( preg_match('/@custom::(\w+(\\\\\w+)*)/', $line) ) {

				$custom   	= true;

				$lines[$i] 	= preg_replace('/@custom::(\w+(\\\\\w+)*)/', '<?php new App\\Component\\\\$1([', $line);

				file_put_contents('oke.txt', $line);

			}

			if( $custom === true ) {

				if( preg_match( '/(@\w+)\s*(=)\s*(.+)/', $line ) ) {

					$lines[$i] = preg_replace('/@(\w+)\s*=\s*(.+)/', '\'$1\' => $2,', $line);	

				}

				if( preg_match('/@end;/', $line) ) {

					$lines[$i] = preg_replace('/(\s*)@end;/', '$1]);'."\n".'$1?>', $line);

					$custom = false;
					continue;
				
				}

			}
			//--------------------------------------------------------------
			
			foreach ($tokens as $token) {
				
				if(preg_match($token['pattern'], $line)) {
					
					$lines[$i] = preg_replace($token['pattern'], $token['replace'], $line);

				}

			}

		}

		$compiled = implode("\n", $lines);

		// compile components
		// preg_match_all(, subject, matches)

		return $compiled;

	}

}