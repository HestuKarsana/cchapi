<?php

use \Firebase\JWT\JWT;
class Jwtgenerator{
	public function generateToken($array){

		require_once("./vendor/firebase/phpjwt/src/JWT.php");
		$key = "anobaka";
		
		$jwt = JWT::encode($array, $key);

		return $jwt;
	}	
}
?>