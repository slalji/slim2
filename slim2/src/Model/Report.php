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

		//$sql = "SELECT title, campaign, CONCAT(prefix,'-',voucher,'-',sufix) as qr, vouchers.rate, redeem_date, vouchers.redeem_id from vouchers JOIN campaigns on vouchers.campaign= campaigns.id where redeem != 0 and campaign = :cid ORDER BY `campaign` DESC" ;
		$sql = "SELECT title as campaign, campaign as id, SUM(vouchers.rate) as total, vouchers.rate, redeem_date, vouchers.redeem_id as receipt from vouchers JOIN campaigns on vouchers.campaign= campaigns.id where redeem != 0 and campaign = :cid group by redeem_id";
		$stmt = $this->conn->prepare($sql);

		$stmt->bindParam( ":cid", $cid, PDO::PARAM_INT );
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return json_encode($result);


	}
	public function redeemedByName( $cid){

		//$sql = "SELECT title, campaign, CONCAT(prefix,'-',voucher,'-',sufix) as qr, sum (vouchers.rate) as total, redeem_date, redeem_id from vouchers JOIN campaigns on vouchers.campaign= campaigns.id where redeem != 0 and campaign =$cid and redeem_id = $rid ORDER BY `campaign` DESC" ;
		//$sql = "SELECT redeem_date, redeem_id, user_name, user_phone, total from receipt";
		$sql = "SELECT CONCAT(prefix,'-',voucher,'-',sufix) as qr,title, campaign,  SUM(vouchers.rate) as total, vouchers.rate, vouchers.redeem_date, vouchers.redeem_id, user_name, user_phone, user_comment from vouchers JOIN campaigns on vouchers.campaign= campaigns.id 
join receipt on receipt.redeem_id = vouchers.redeem_id
where redeem != 0 and campaign =:cid group by redeem_id order by user_name ASC";
		$stmt = $this->conn->prepare( $sql );
		$stmt->bindParam( ":cid", $cid, PDO::PARAM_INT );
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return json_encode($result);
	}
	public function redeemedByReceipt( $cid){

		$sql = "select campaigns.id, campaigns.rate, vouchers.redeem_id, vouchers.redeem_date, count(campaigns.rate) as count ,sum(campaigns.rate) as total from campaigns join vouchers on campaigns.id=vouchers.campaign where vouchers.redeem=1 group by vouchers.redeem_id, campaigns.rate ORDER BY `vouchers`.`redeem_id` DESC";
		$stmt = $this->conn->prepare( $sql );
		$stmt->bindParam( ":cid", $cid, PDO::PARAM_INT );
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return json_encode($result);
	}
	public function getRedeemedDates(){

		$sql = "select distinct vouchers.redeem_date from campaigns join vouchers on campaigns.id=vouchers.campaign where vouchers.redeem=1 ";
		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
		try{
			$sql = "Select id from receipt where redeem_id = '$redeem_id'";
			$stmt = $this->conn->prepare($sql);

			$state = $this->_pdoBindArray($stmt, $data);
			$state->execute();
			$result = $state->fetchAll( PDO::FETCH_ASSOC );

			$result = $result[0];

			if ($result){
				return json_encode(['code' => 200, 'message' => $result]);
			}
			return json_encode(['code' => 404, 'message' => 'Receipt Not Found']);
		}
		catch (\Exception $e){
			$this->logger->info('err insertCampaign', json_decode($e, true));
			return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);

		}

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
	public function updatetReceiptValues($params, $rid)
	{
		$data = array();

		foreach ($params as $key => $val) {
			$data["$key"] = $val;
		}
		$str = null;
		try {
			foreach ($data as $key => $val) {
				$str .= $key ."= '". $val."', ";

			}
			$str = substr($str, 0, -2);

			$sql = "UPDATE receipt SET $str WHERE redeem_id = '$rid'";

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

	public function searchUser($needle){

		//$cid = $params['cid'];
		$search = $needle;

		try{
			$sql = "select CONCAT(prefix,'-',voucher,'-',sufix) as qr, campaigns.rate, receipt.redeem_id, receipt.user_name, receipt.user_phone, receipt.user_comment from vouchers join receipt on receipt.redeem_id = vouchers.redeem_id join campaigns on vouchers.campaign= campaigns.id where vouchers.campaign = :cid && (receipt.redeem_id like :search || receipt.user_name  like :search || receipt.user_phone like :search) group by vouchers.redeem_id";
			$stmt = $this->conn->prepare($sql);

			//$stmt->bindParam( ":cid", $cid, PDO::PARAM_INT );
			$stmt->bindParam( ":search", $search, PDO::PARAM_STR );
			$stmt->execute();



			$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
			//$result = $stmt->fetchAll();

			$result = $result[0];

			return json_encode(['code' => 200, 'message' => $result]);
		}
		catch (\Exception $e){
			return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage() . ' '.$e->getFile() . ' '.$e->getLine()]);

		}

	}
	public function search($needle){

		//$cid = $params['cid'];
		$search = $needle;

		try{
			$sql = "select * from vouchers join campaigns on vouchers.campaign= campaigns.id where vouchers.id = :search";
			$stmt = $this->conn->prepare($sql);

			//$stmt->bindParam( ":cid", $cid, PDO::PARAM_INT );
			$stmt->bindParam( ":search", $search, PDO::PARAM_STR );
			$stmt->execute();



			$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
			//$result = $stmt->fetchAll();

			$result = $result[0];

			return json_encode(['code' => 200, 'message' => $result]);
		}
		catch (\Exception $e){
			return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage() . ' '.$e->getFile() . ' '.$e->getLine()]);

		}

	}

}
