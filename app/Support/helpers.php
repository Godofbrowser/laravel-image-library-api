<?php

if(!function_exists('current_auth_user')){
    function current_auth_user(){
        $guards = ['web', 'api'];
        foreach($guards as $guard) {
            if ($user = request()->user($guard))
                return $user;
        }

        return null;
    }
}

if(!function_exists('log_all')){
	function log_all(){
		$messages = func_get_args();
		foreach($messages as $message) {
			logger()->debug($message);
		}
	}
}