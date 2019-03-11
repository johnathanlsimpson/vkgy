<?php
include('config-deploy.php');
include('../function-post_deploy.php');

$payload;

function log_git($input) {
	global $config;
	
	$time = date('Y-m-d H:i:s');
	file_put_contents($config['log_file'], $time."\t".$input."\n", FILE_APPEND | LOCK_EX);
	flush();
}

function create_repo() {
	$cmds['create_repo']  = 'git clone --mirror '.$config['remote_repo'];
}

function update_repo() {
	global $config;
	global $payload;
	
	if(isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
		$payload = json_decode(file_get_contents('php://input'));
		
		$cmds['update_repo']  = 'git --git-dir='.$config['path_to_repo'].' fetch';
		$cmds['update_files'] = 'git --git-dir='.$config['path_to_repo'].' --work-tree='.$config['path_to_files'].' checkout -f '.$config['branch_name'];
		$cmds['get_changes']  = 'git --git-dir='.$config['path_to_repo'].' rev-parse --short '.$config['branch_name'];
		
		foreach($cmds as $cmd) {
			system($cmd, $response);
			log_git('COMMAND: '.$cmd);
			log_git('RESPONSE: '.$response);
		}
	}
	
	log_git('SERVER: '.print_r($_SERVER, true));
	log_git('PAYLOAD: '.print_r($payload, true));
	log_git("\n\n".'---'."\n\n");
}

update_repo();
post_deploy();