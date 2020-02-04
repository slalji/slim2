<?php
namespace App\Model;
use http\Exception;
use http\Params;
use PDO;


class Campaign
{

    protected $conn = null;
    protected $logger = null;

    public function __construct($container)
    {
        $this->conn = $container->get('db');

        $this->logger = $container->get('logger');
    }

    private function _pdoBindArray(&$poStatement, &$paArray)
    {
        foreach ($paArray as $k => $v) {
            @$poStatement->bindValue(':' . $k, $v);

        } // foreach
        return $poStatement;
    }

    public function insertCampaign($params)
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

            $sql = "INSERT INTO campaigns (" . $cols . ") VALUES (" . $vals . ")";
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

    public function insertVouchers($params)
    {
        $data = array();


        try {
                $i=0;
                $length = (int)$params['quantity'];
                $campaign = $params['c_id'];
                $rate = $params['rate'];
								$created_date = $params['created_date'];
                if ($length <= 0)
                    throw \Exception('Error, quantity must be greater than zero');

                for($i; $i<$length; $i++) {

                    $token = isset($params['pre']) ? $params['pre'] : '';
                    $token .= self::getToken();
                    $token .= isset($params['self']) ? $params['self'] : '';

                    $sql = "INSERT INTO vouchers (campaign,voucher,rate,created_date) VALUES (:campaign,:voucher,:rate)";
										$stmt = $this->conn->prepare($sql);


										$stmt->bindValue( "campaign", $params['c_id'], PDO::PARAM_STR );
                    $stmt->bindValue( "voucher", $token, PDO::PARAM_STR );
										$stmt->bindValue( "rate", $rate, PDO::PARAM_INT );
										$stmt->bindValue( "created_date", $created_date, PDO::PARAM_STR );
										$stmt->execute();
                }


            return json_encode(['code' => 200, 'message' => $length.' Vouchers successfully created']);

        } catch (Exception $e) {
            $this->logger->info('err insertVouchers', json_decode($e, true));
            return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);

        }
        return false;
    }

    public function redeemVoucher($params)
    {
    	$redeem=1;

			$rate = $params['rate'];
			$id = $params['id'];
			$voucher = $params['voucher'];
			$redeem_id = $params['redeem_id'];
			$redeem_date = $params['redeem_date'];
			$full_voucher = $params['prefix'].'-'.$params['voucher'].'-'.$params['sufix'];

//					var_dump('params');
//					var_dump($params);
//					var_dump(__FILE__.' '.__LINE__);
//					die;

			$result = $this->voucher_exists($full_voucher);

//
			if(json_decode($result)->code == 202)
				return  json_encode(['code' => 202,  'rate'=>0,'message' =>' Voucher Already redeemed ']);

			elseif(json_decode($result)->code == 405)
				return json_encode(['code'=>405,'rate'=>0, 'message'=>'Voucher expired: ' . $params['voucher']]);

			elseif(json_decode($result)->code == 404)
				return json_encode(['code'=>404, 'rate'=>0,'message'=>'Voucher expired: ' . $params['voucher']]);




        try {



					/*Get rate from rates table*/
					//$sql_res="SELECT * FROM `vouchers` v JOIN rates r on v.campaign = r.campaign WHERE v.voucher='$voucher' and r.created_date <= v.created_date order by r.created_date DESC LIMIT 1";
					//$sql_res="SELECT r.rate FROM `vouchers` v JOIN rates r on v.campaign = r.campaign WHERE v.voucher='$voucher' and r.created_date <= v.created_date order by r.created_date DESC LIMIT 1";
					$sql_rate = "select c.rate from vouchers v join campaigns c on v.campaign = c.id where v.voucher='$voucher'";
					$stmt_res = $this->conn->prepare( $sql_rate );
					$stmt_res->execute();
					$result = $stmt_res->fetchAll( PDO::FETCH_ASSOC );


					$result = $result[0];
					$rate = $result['rate']<=0?0:$result['rate'];



        	$sql = "UPDATE `vouchers` SET `rate`=:rate,`redeem`=:redeem,`redeem_id`=:redeem_id, `redeem_date`=:redeem_date WHERE `voucher`=:voucher";

					$stmt = $this->conn->prepare( $sql );

					$stmt->bindValue(':redeem', $redeem, PDO::PARAM_INT);
					$stmt->bindValue(':redeem_date', date('Y-m-d',strtotime($redeem_date)));
					$stmt->bindValue(':redeem_id', $redeem_id);
					$stmt->bindValue(':voucher', $voucher, PDO::PARAM_STR);
					$stmt->bindValue(':rate', $rate, PDO::PARAM_INT);

					$result = $stmt->execute();

					return json_encode(['code' => 200, 'rate'=>$rate,'message' => $params['voucher'].' success ']);




				} catch (\PDOException $e) {

					return json_encode(['code' => 404, 'message' => 'Error update for '.$e->getMessage()]);
            $this->logger->info('err updateVouchers', json_decode($e, true));
        }
        return false;
    }

    public function validate($params)
    {
        $required = ['title', 'expiry_date', 'created_date', 'quantity', 'period'];
        foreach ($params as $key => $val) {
            if ( in_array($key, $required) && $val==='')
                return json_encode(['code'=>405,'message'=>'missing required field: ' . $key]);
        }
        return json_encode(['code'=>200,'message'=>'success ']);

    }
    public function check($title){
        $sql = "select title from campaigns where title='$title'" ;

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll();

        if ($result)
            return json_encode(['code'=>200,'message'=>'title: '. $title.' is already in use, please choose another ' ]);

        return json_encode(['code'=>404,'message'=>'missing: ' . $title]);

    }
		public function allCampagins(){
		$sql = "select id, title, expiry_date from campaigns order by title " ;

		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll( PDO::FETCH_ASSOC );


		return json_encode($result);
		//echo $result;

	}
	public function campaginsByUser($user){
		$sql = "select id, title, expiry_date from campaigns where user='$user' order by title " ;

		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll( PDO::FETCH_ASSOC );


		return json_encode($result);


	}
    public function validateVoucher($params)
    {


        $required = ['voucher'];
        foreach ($params as $key => $val) {
            if ( in_array($key, $required) && $val==='')
                return json_encode(['code'=>201,'message'=>'missing required field: ' . $key]);
        }
        return json_encode(['code'=>200,'message'=>'success ']);

    }
    public function voucher_exists($voucher){
    	try{

    		$v = explode('-',$voucher);

				$sql="SELECT c.id, title, prefix, sufix, c.expiry_date, v.id, campaign, voucher, redeem, redeem_date, expiry_date, date(c.expiry_date)-date(now()) diff FROM `campaigns` c LEFT JOIN vouchers v ON (c.id = v.campaign) 
				WHERE v.voucher='$v[1]' && prefix='$v[0]' && sufix='$v[2]'";
				$stmt = $this->conn->prepare( $sql );
				$stmt->execute();
				$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
				$result = $result[0];

			if(!$result)
				return json_encode(['code'=>404,'message'=>'Does not exist: ' . $voucher]);


				if ($result['redeem'] == 1)
					return json_encode(['code'=>202,'message'=>'Already redeemed : ' . $voucher.' '.$result['redeem'].' '.$result['expiry_date']]);

				else if ($result['diff'] == 0)
					return json_encode(['code'=>405,'message'=>'Voucher expired: ' . $voucher.' '.$result['expiry_date']]);
				else if ($result['voucher'] === $v[1]) {

					return json_encode(['code' => 200, 'message' => 'Voucher exists: ' . $voucher . ' ' . $result['redeem']]);
				}
				else
					return json_encode(['code'=>404,'message'=>'Does not exist: ' . $voucher]);


			}
			catch (\Exception $exception){
				return json_encode(['code'=>404,'message'=>'Err ' . $voucher]);
			}

    }

    public static function getToken()
    {
        return $token = uniqid();
    }
		public function getVouchers($id, $perPage=10, $startAt=0){
		//$v = explode('-',$voucher);

		$sql = "SELECT * FROM `campaigns` c LEFT JOIN vouchers v ON (c.id = v.campaign) WHERE c.id ='$id' && expiry_date >= now() && redeem !=true  LIMIT $startAt, $perPage";


		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll( PDO::FETCH_ASSOC );

		return json_encode($result);



	}
		public function getTotalVouchers($id){

			$sql = "SELECT count(c.id) as total FROM `campaigns` c LEFT JOIN vouchers v ON (c.id = v.campaign) WHERE c.id ='$id' && expiry_date >= now() && redeem !=true";


			$stmt = $this->conn->prepare( $sql );
			$stmt->execute();
			//$result = $stmt->fetchColumn();


		$result = ceil($stmt->fetchColumn()/10);

			return $result;


	}
	public function getCampaignVouchers($id){
		$sql="SELECT c.id, title, prefix, sufix, c.expiry_date,  v.id,  c.rate as crate,v.rate as vrate, campaign, voucher, redeem, redeem_date, expiry_date, date(c.expiry_date)-date(now()) diff FROM `campaigns` c LEFT JOIN vouchers v ON (c.id = v.campaign) 
						WHERE c.id ='$id' ";

		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
		$result = $result[0];
		return $result;
}
	public function getCampaignId($vochure){
		$sql="SELECT c.id, c.rate, c.prefix, c.sufix, voucher FROM `campaigns` c LEFT JOIN vouchers v ON (v.campaign = c.id) 
						WHERE voucher ='$vochure' ";

		$stmt = $this->conn->prepare( $sql );
		$stmt->execute();
		$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
		return $result[0];
	}
	public  function update($id, $rate, $expiry_date){
    	try{
				$sql ="UPDATE campaigns c
									SET c.rate = :rate,
											c.created_date= :now,
											c.expiry_date= :expiry_date
									WHERE c.id = :id";

				$stmt = $this->conn->prepare( $sql );

				$stmt->bindValue(':rate', $rate, PDO::PARAM_INT);
				$stmt->bindValue(':expiry_date', $expiry_date, PDO::PARAM_STR);
				$stmt->bindValue(':id', $id, PDO::PARAM_STR);
				$stmt->bindValue(':now', date('Y-m-d'), PDO::PARAM_STR);

				$result = $stmt->execute();
				return json_encode(['code' => 200, 'message' => 'Voucher updated']);

			}
			catch (\Exception $e){

				return json_encode(['code'=>404,'message'=>$e.getMessage()]);
			}




}
	public  function add_rate($id, $rate){

		try{
			$sql ="insert into rates (campaign,rate, created_date) values (:id, :rate, :now)";

			$stmt = $this->conn->prepare( $sql );

			$stmt->bindValue(':rate', $rate, PDO::PARAM_INT);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->bindValue(':now', date('Y-m-d'), PDO::PARAM_STR);

			$result = $stmt->execute();

			return json_encode(['code' => 200, 'message' => 'Rate updated']);

		}
		catch (\Exception $e){

			return json_encode(['code'=>404,'message'=>$e->getMessage()]);
		}




	}



}
