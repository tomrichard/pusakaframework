<?php 
namespace Pusaka\View;

class EasyUI {

	private $compiled 		= '';

	private $current 		= '';
	private $level 			= 0;
	private $stack 			= [];

	private $is_component 	= false;
	private $lines 			= [];

	private $components 	= [];
	private $pipes 			= [];
	private $directives 	= [];

	function __construct( $lines ) {

		$this->lines 		= $lines;

	}

	function getPipes() {
		
		return $this->pipes;

	}

	function registerPipes($pipes) {

		$this->pipes 		= $pipes;

	}

	function getComponents() {
		
		return $this->components;

	}

	function registerComponents($components) {

		$this->components 	= $components;

	}

	function getDirectives() {
		
		return $this->directives;

	}

	function registerDirectives($directives) {

		$this->directives 	= $directives;

	}

	function componentCompiler( $lines ) {

		$name 	= '';
		$vars	= [];
		$value  = [];
		$body 	= '';

		if( preg_match('/@component\:(.+)/', $lines[0], $match) > 0 ) {
			$name = trim($match[1]);
		}

		if( !isset($this->components[$name]) ) {
			return '';
		}

		$body 	= $this->components[$name];

		array_shift($this->lines);

		foreach ($lines as $line) {
			
			if( preg_match('/@(\w+)\s*=\s*(.*)/', $line, $match) > 0) {
				$vars[] 	= trim('$'.$match[1]);
				$value[]  	= trim($match[2]);
			}

		}

		$component = 
			''."\r\n".'<?php $component{uniqe} = function({param}) { ?>
				'."\r\n".'<?php // component : {function_name} ?>
				'."\r\n".'{function_body}
			'."\r\n".'<?php }; $component{uniqe}({value}); ?>';

		$component = strtr($component, [
			'{function_name}' 	=> $name,
			'{uniqe}' 			=> strtoupper(uniqid()),
			'{param}'			=> implode(',', $vars),
			'{value}'			=> implode(',', $value),
			'{function_body}'	=> $body
		]);

		return $component;

	}

	function structureCompiler( $line ) {

		// comment 
		//-----------------------------------------------
		$comment 	= [
			
			'begin' 	=> '/<!!\s*(.*?)\s*!!>/',
			'resolve'	=> function($match) {

				return '';

			}

		];

		// common 
		//-----------------------------------------------
		$basic_echo 	= [
			
			'begin' 	=> '/<<\s*(.*?)\s*>>/',
			'resolve'	=> function($match) {

				return '<?php echo '.$match[1].' ?>';

			}

		];

		// pipes
		//-----------------------------------------------
		$pipes 			= $this->pipes;

		// directives
		//-----------------------------------------------
		$directives 	= $this->directives;

		// structure control
		//----------------------------------------------
		$if 			= [
			
			'begin' 	=> '/@if\s*\((.*)\)/',
			'end' 		=> '/@endif;/',
			'resolve'	=> function($match) {
				
				$this->current 	 = 'if';
				$this->level 	+= 1;
				array_push($this->stack, $match);

				return '<?php if('.$match[1].') : ?>';
			
			},
			'over'		=> function($match) {
				
				$this->level 	-= 1;

				array_pop($this->stack);

				return '<?php endif; ?>';
			
			}

		];
		//----------------------------------------------
		$elseif 		= [
			
			'begin' 	=> '/@elseif\s*\((.*)\)/',
			'resolve'	=> function($match) {

				return '<?php elseif('.$match[1].') : ?>';
			
			}
		];
		//----------------------------------------------
		$else 			= [
		
			'begin' 	=> '/@else/',
			'resolve'	=> function($match) {

				return '<?php else : ?>';
			
			}
		];
		// isset
		//----------------------------------------------
		$isset 			= [
			
			'begin' 	=> '/@isset\s*\((.*)\)/',
			'end' 		=> '/@endisset;/',
			'resolve'	=> function($match) {

				return '<?php if( isset('.$match[1].') ) : ?>';
			
			},
			'over'		=> function($match) {

				return '<?php endif; ?>';
			
			}

		];
		// empty
		//----------------------------------------------
		$empty 			= [
			
			'begin' 	=> '/@empty(\s*\((.*)\))?/',
			'end' 		=> '/@endempty;/',
			'resolve'	=> function($match) {

				$prev 	= '';
				$param 	= '';

				if(isset($match[1])) {
					$param = $match[1];
				}else {

					if($this->current === 'forelse') {
						$stack 	= $this->stack[0];
						$prev 	= 'endforeach;';
						$param 	= '(' . $stack[2] . ')';
					}

				}

				return '<?php '.$prev.' if( empty'.$param.' ) : ?>';
			
			},
			'over'		=> function($match) {

				return '<?php endif; ?>';
			
			}

		];
		// for
		//----------------------------------------------
		$for 			= [
			
			'begin' 	=> '/@for\s*\((.*)\)/',
			'end' 		=> '/@endfor;/',
			'resolve'	=> function($match) {

				return '<?php for('.$match[1].') : ?>';
			
			},
			'over'		=> function($match) {

				return '<?php endfor; ?>';
			
			}

		];
		// foreach
		//----------------------------------------------
		$foreach 			= [
			
			'begin' 	=> '/@foreach\s*\((.*)\)/',
			'end' 		=> '/@endforeach;/',
			'resolve'	=> function($match) {
				
				$this->current 	 = 'foreach';
				$this->level 	+= 1;
				array_push($this->stack, $match);

				return '<?php foreach('.$match[1].') : ?>';
			
			},
			'over'		=> function($match) {
				
				$this->level 	-= 1;

				array_pop($this->stack);

				return '<?php endforeach; ?>';
			
			}

		];
		// forelse
		//----------------------------------------------
		$forelse 			= [
			
			'begin' 	=> '/@forelse\s*\((\s*(.*)\s+as\s*(.*))\)/',
			'end' 		=> '/@endforelse;/',
			'resolve'	=> function($match) {
				
				$this->current 	 = 'forelse';
				$this->level 	+= 1;
				array_push($this->stack, $match);

				return '<?php foreach('.$match[1].') : ?>';
			
			},
			'over'		=> function($match) {
				
				$this->level 	-= 1;

				array_pop($this->stack);

				return '<?php endif; ?>';
			
			}

		];
		//----------------------------------------------
		// while
		//----------------------------------------------
		$while 			= [
			
			'begin' 	=> '/@while\s*\((.*)\)/',
			'end' 		=> '/@endwhile;/',
			'resolve'	=> function($match) {

				return '<?php while('.$match[1].') : ?>';
			
			},
			'over'		=> function($match) {

				return '<?php endwhile; ?>';
			
			}

		];
		//----------------------------------------------

		// pipes
		//------------------------------------------------------------------------
		foreach ($pipes as $key => $pipe) {
			
			$pattern = '/<<\s*(.*?)\s*\|\s*{begin}\s*>>/';

			try {
				$pattern 	= strtr($pattern, ['{begin}' => $pipe['begin']]);
				$line 		= preg_replace_callback( $pattern, $pipe['resolve'], $line);
			}catch(\Exception $e) {
				file_put_contents($e->getMessage(), LOGDIR . 'log_easyui_compiler_error_' . uniqid() . '.txt');
			}

		}

		// directives
		//------------------------------------------------------------------------
		foreach ($directives as $key => $directive) {	

			try {
				$pattern 	= $directive['begin'];
				$line 		= preg_replace_callback( $pattern, $directive['resolve'], $line);
			}catch(\Exception $e) {
				file_put_contents($e->getMessage(), LOGDIR . 'log_easyui_compiler_error_' . uniqid() . '.txt');
			}

		}

		// comment
		//------------------------------------------------------------------------
		$line = preg_replace_callback($comment['begin'], 		$comment['resolve'], 	$line);

		// common
		//------------------------------------------------------------------------
		$line = preg_replace_callback($basic_echo['begin'], 	$basic_echo['resolve'], $line);

		// if
		//------------------------------------------------------------------------
		$line = preg_replace_callback($if['begin'], 			$if['resolve'], 	$line);
		$line = preg_replace_callback($if['end'], 	 			$if['over'], 	 	$line);
		// elseif
		//------------------------------------------------------------------------
		$line = preg_replace_callback($elseif['begin'], 		$elseif['resolve'], $line);
		// else
		//------------------------------------------------------------------------
		$line = preg_replace_callback($else['begin'], 			$else['resolve'], 	$line);
		
		// isset
		//------------------------------------------------------------------------
		$line = preg_replace_callback($isset['begin'], 			$isset['resolve'], 	$line);
		$line = preg_replace_callback($isset['end'], 			$isset['over'], 	$line);
		// empty
		//------------------------------------------------------------------------
		$line = preg_replace_callback($empty['begin'], 			$empty['resolve'], 	$line);
		$line = preg_replace_callback($empty['end'], 			$empty['over'], 	$line);

		// for
		//------------------------------------------------------------------------
		$line = preg_replace_callback($for['begin'], 			$for['resolve'], 		$line);
		$line = preg_replace_callback($for['end'], 	 			$for['over'], 	 		$line);

		// foreach
		//------------------------------------------------------------------------
		$line = preg_replace_callback($foreach['begin'], 		$foreach['resolve'], 	$line);
		$line = preg_replace_callback($foreach['end'], 	 		$foreach['over'], 	 	$line);

		// forelse
		//------------------------------------------------------------------------
		$line = preg_replace_callback($forelse['begin'], 		$forelse['resolve'], 	$line);
		$line = preg_replace_callback($forelse['end'], 	 		$forelse['over'], 	 	$line);

		// while
		//------------------------------------------------------------------------
		$line = preg_replace_callback($while['begin'], 			$while['resolve'], 		$line);
		$line = preg_replace_callback($while['end'], 	 		$while['over'], 	 	$line);

		return $line;

	}

	function compile() {

		//==============================================

		$compiled 			= '';

		$component_lines 	= [];

		$lines 				= $this->lines;

		foreach ($lines as $line) {

			if( preg_match('/@component:.*/', $line) > 0 ) {
				
				$this->is_component = true;
				$component_lines[] 	= $line;
				continue;

			}
			
			if( preg_match('/@endcomponent;/', $line) > 0 ) {

				$component 		    = $this->componentCompiler( $component_lines );

				if($component !== '' AND is_string($component)) {

					$engine 		= new EasyUI( preg_split('/\n/', $component) );
					
					$engine->registerPipes( $this->getPipes() );
					$engine->registerComponents( $this->getComponents() );
					$engine->registerDirectives( $this->getDirectives() );

					$component 		= $engine->compile()->getCompiled();

					unset($engine);
				
				}

				$compiled 		   .= $component;
				$this->is_component = false;
				$component_lines 	= [];
				continue;

			}

			if( $this->is_component ) {

				$component_lines[] 	= $line;
				continue;

			}

			$line 			 = $this->structureCompiler( $line );

			$compiled  		.= $line;

		}

		$this->compiled 	 = $compiled;

		return $this;

	}

	function getCompiled() {

		return $this->compiled;

	}

}