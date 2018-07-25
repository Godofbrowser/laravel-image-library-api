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