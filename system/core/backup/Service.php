<?php 
if(ENVIRONMENT === 'DEVELOPMENT' AND RUNSERVICE) {

	$__services = $config['service'];

	foreach ($__services as $cmd) {
		shell_exec('pusaka ' . $cmd . ' 2>&1');
	}

}