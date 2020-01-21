<?php


namespace App\Model;


use PDO;

class User
{
	protected $conn = null;
	protected $logger = null;

	public function __construct($container)
	{
		$this->conn = $container->get('db');

		$this->logger = $container->get('logger');
	}
	public function check($username, $password){

	$sql = "select username, password from users where username='$username', password='$password'" ;

	$stmt = $this->conn->prepare( $sql );
	$stmt->execute();
	$result = $stmt->fetchAll();

	if ($result)
		return json_encode(['code'=>200,'message'=>'user: '. $username.' exists ' ]);

	return json_encode(['code'=>404,'message'=>'user does not exist: ' . $username]);

	}
	public function checkPassword($username, $password){
//print 310dcbbf
		//test 202cb962
		//tmhenga Ap-N7T
		$sql = "SELECT password,type from users WHERE username='$username'";

		$stmt = $this->conn->prepare( $sql );

		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$hash = trim($result['password']);
		$type = trim($result['type']);


		if(md5($password) === $hash){
			return $type;
		}
		return false;



	}
	public function createUser($username, $password, $type='user'){


		try{
			$sql = "INSERT INTO users (username, password, type) VALUES (:username,:password,:type)";
			$stmt = $this->conn->prepare($sql);

			$stmt->bindValue( "username", $username,PDO::PARAM_STR );
			$stmt->bindValue( "password", $password, PDO::PARAM_STR );
			$stmt->bindValue( "type", $type, PDO::PARAM_STR );
			$result = $stmt->execute();

			if ($result)
				return true;
		}
		catch (\Exception $e){
			error_log('User not created '.$e->getMessage(),3, __DIR__."/../../log/error.log");
			return false;
		}
	}

}