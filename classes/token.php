<?php
    class Token {
        public static function generate() {
            return Session::put(Config::get('session/token_name'), md5(uniqid()));
        }

        // check if the token existed in the session
        public static function check($token) {
            $tokenName = Config::get('session/token_name');

            if(Session::exists($tokenName) && $token === Session::get($tokenName)) {
                Session::delete($tokenName);
                return true;
            }
            return false;
        }
    }
?>