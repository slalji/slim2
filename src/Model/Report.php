<?php


namespace App\Model;


use PDO;

class Report
{
	protected $conn = null;
	protected $logger = null;

	public function __construct($container)
	{
		$this->conn = $container->get('db');

		$this->logger = $container->get('logger');
	}
	public function redeemedByCID($cid){

	$sql = "SELECT title, campaign, CONCAT(prefix,'-',voucher,'-',sufix) as qr, vouchers.rate, redeem_date from vouchers JOIN campaigns on vouchers.campaign= campaigns.id where redeem != 0 and campaign =$cid ORDER BY `campaign` DESC" ;

	$stmt = $this->conn->prepare( $sql );
	$stmt->execute();
	$result = $stmt->fetchAll();
	return json_encode($result);
	}

}