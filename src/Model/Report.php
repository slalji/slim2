<?php


namespace App\Model;

use http\Exception;
use http\Params;
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
	private function _pdoBindArray(&$poStatement, &$paArray)
    {
        foreach ($paArray as $k => $v) {
            @$poStatement->bindValue(':' . $k, $v);

        } // foreach
        return $poStatement;
    }
    public function checkExists($redeem_id){
			$sql = "Select id from receipt where redeem_id = '$redeem'";
            $stmt = $this->conn->prepare($sql);

            $state = $this->_pdoBindArray($stmt, $data);
            $state->execute();
            $result = $stmt_res->fetchAll( PDO::FETCH_ASSOC );
            $result = $result[0];
            return json_encode(['code' => 200, 'message' => $result]);
    }
	public function insertReceiptValues($params)
    {
        $data = array();

        foreach ($params as $key => $val) {
            $data["$key"] = $val;
        }

        $cols = null;
        $vals = null;

        try {
            foreach ($data as $key => $val) {
                $cols .= $key . ', ';
                $vals .= ':' . $key . ', ';
            }
            $cols = rtrim($cols, ', ');
            $vals = rtrim($vals, ', ');

            $sql = "INSERT INTO receipt (" . $cols . ") VALUES (" . $vals . ")";
            $stmt = $this->conn->prepare($sql);

            $state = $this->_pdoBindArray($stmt, $data);
            $state->execute();
            $id = $this->conn->lastInsertId();
            return json_encode(['code' => 200, 'message' => $id]);

        } catch (Exception $e) {
            $this->logger->info('err insertCampaign', json_decode($e, true));
            return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);

        }
        return false;
    }

}