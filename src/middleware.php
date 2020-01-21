<?php


use GuzzleHttp\Client;



class AuthMiddleware  {
	private $container;

	public function __construct($container) {
		$this->container = $container;

	}
  public function __invoke($request, $response, $next)
  {
		if(empty($_SESSION))
			session_start();


			if($this->login_check($request)){

				$response = $next($request, $response);
				return $response;
			}


		return  $response->withStatus(200)->withHeader('Location', '../');
  }
  public function login_check($request) {

		if (isset($_SESSION['auth']) && $_SESSION['time'] > time() ){
      return true;
    }
		else{
			$username = $request->getParam('username');
			$password = $request->getParam('password');
			$class = new \App\Model\User($this->container);
			$check = $class->checkPassword($username, $password);

				if($check){
				session_start();
					$_SESSION['user'] = $username;
					$_SESSION['auth'] = $check;
					$_SESSION['time'] = time()+1000;

					return true;
				}

			}

		return false;



  }
}


