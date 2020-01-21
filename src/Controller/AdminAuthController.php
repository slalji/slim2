<?php

namespace App\Controller;


use App\Model\User;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * AuthController
 * get access server info on puid
 *
 */
class AdminAuthController extends Controller

{
	public function __invoke(Request $request, Response $response)
	{

		return  $response->withStatus(302)->withHeader('Location', '/home');

	}
	public function  logout($request, $response, $args)
	{


		session_destroy();


		return  $response->withHeader('Location', 'home');
	}
	public function create($request, $respnse){
		$password = $request->getParam('password');
		$confirm = $request->getParam('confirm');
		$check = false;

		if($password === $confirm){
			$user = $request->getParam('username');
			$type = $request->getParam('type');
			$pass_hash = md5($request->getParam('password'));

			$class = new \App\Model\User($this->container);
			$check = $class->createUser($user, $pass_hash, $type);

		}
		if($check){
			print 'Successfully created new user';
		}
		else
		print 'Error User not created';



	}
	public function user($request,$response){
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');


		$page_data = [
			'page_h1' => 'Authentication',
			'content' => '<p>Create New User.</p>',
			'admin' => $_SESSION['auth']
		];
		return $this->view->render($response, 'user/user.twig', $page_data);
	}
}

