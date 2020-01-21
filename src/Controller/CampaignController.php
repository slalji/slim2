<?php
namespace App\Controller;


use App\Model\Campaign;
use PDO;
use Slim\Http\Response;
use Slim\Http\Request;
/**
 *
 *
 */


class CampaignController extends Controller

{
    protected $view = null;
    protected $pdo = null;
    protected $c_id = '';

    public function __invoke(Request $request, Response $response)
    {
    	$this->view = $this->container->get('view');
    	$this->pdo = $this->container->get('db');
			$class = new Campaign($this->container);
			$result = $class->allCampagins();
			//$byUser = $class->campaginsByUser($_SESSION['user']);
			$time = strtotime("now");

			$page_data = [
				'page_h1' => 'Redeem',
				'content' => '<p>Start Redeem QR </p>',
				'campagins' => json_decode($result,true),
				'admin' => $_SESSION['auth'],
				'time' => $time,
				'now' => date('Y-m-d H:i:s', $time)
			];
        return $this->view->render($response, 'start.twig', $page_data);
    }
	public function start(Request $request, Response $response)
	{
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');

		session_start();
		session_destroy();
		session_commit();

		$page_data = [
			'page_h1' => 'Campaign',
			'content' => '<p>Start Redeem</p>',
			'redeem_id' => $request->getParam('redeem_id'),
			'redeem_date' => $request->getParam('redeem_date')

		];
		return $this->view->render($response, 'redeem.twig', $page_data);
	}
	public function stop(Request $request, Response $response)
	{
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');
		$params = $request->getParams();
		var_dump('stop');
		var_dump($params);
		die;
/* from receipt where id=xx (session?)
show on page list
vouchers
rate select rate from rates where campign_title=ctitle and rate => $rate (first item is the rate)
header id and date
print button
*/


		$page_data = [
			'page_h1' => 'Campaign',
			'content' => '<p>Create New Campaign.</p>',
			'admin' => $_SESSION['auth']
		];
		return $this->view->render($response, 'receipt.twig', $page_data);
	}

    /**
     * @param $request
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function create($request, $response)
    {
        $content = [];
        $date = '';
        $period = '';
        $this->view = $this->container->get('view');
        $params = $request->getParams();


        foreach ($params as $key => $value) {

            if ($key === 'created_date') {
            	$date = ($value);
							$content['created_date']= $value;

						} else if ($key === 'expiry') {
                $period = $value;
                $expire = date('Y-m-d H:i:s', strtotime('+' . $period, strtotime($date)));
                $content['expiry_date'] = $expire;

            }

            $content[$key] = $value;
        }

        //$password = "5xKu1WjoEJj4qptK";
        $class = new Campaign($this->container);

        //$params = ['title' => $content['title'], 'prefix' => $content['prefix'], 'sufix' => $content['sufix'], 'rate' => $content['rate'], 'created_date' => $content['created_date'], 'expiry_date' => $content['expiry_date']];
			$campagin = $content;
			unset($campagin['expiry']);
			unset($campagin['quantity']);

			$campagin['user'] = $_SESSION['user'];



        try {
            $result = $class->validate($campagin);


            if (json_decode($result)->code != 200) {
                throw new \Exception(json_decode($result)->message, json_decode($result)->code);
            }

            $result = $class->check($params['title']);
						if (json_decode($result)->code == 200) {
							throw new \Exception(json_decode($result)->message, json_decode($result)->code);
						}

            //$result = $class->insertCampaign(['title' => $content['title'], 'prefix' => $content['prefix'], 'sufix' => $content['sufix'], 'created_date' => $content['dateTime'], 'expiry_date' => $content['expiry']]);
							$result = $class->insertCampaign($campagin);

            if (json_decode($result)->code == 200) {
                $this->c_id = json_decode($result)->message;
            }


            //$result = $class->insertVouchers(['c_id' => $this->c_id, 'quantity' => $content['quantity'], 'prefix' => $content['prefix'], 'sufix' => $content['sufix'], 'created_date' => $content['created_date'], 'expiry_date' => $content['expiry_date']]);
						$content['c_id']=$this->c_id;
						$result = $class->insertVouchers($content);

            if (json_decode($result)->code == 200) {
                $page_data = [
                    'page_h1' => 'New Campaign Created',
                    'content' => $content,
                    'id' => $this->c_id,
									'admin' => $_SESSION['auth'],
									'time' => date('H:i:s',$_SESSION['time'])


                ];
                return $this->view->render($response, 'success.twig', $page_data);
            } else {
                $page_data = [
                    'page_h1' => 'Error while creating Vouchers',
                    'content' => $content,
                    'id' => $this->c_id,
									'admin' => $_SESSION['auth'],
									'time' => date('H:i:s',$_SESSION['time'])
                ];
                return $this->view->render($response, 'error.twig', $page_data);
            }

        } catch (\Exception $e) {
					//return $this->view->render($response, 'error.twig', $page_data);
					$page_data = [
						'page_h1' => 'Error ',
						'result' => $e->getMessage(),
						'admin' => $_SESSION['auth'],
						'time' => date('H:i:s',$_SESSION['time'])

					];
					return $this->view->render($response, 'error.twig', $page_data);
        }
    }

    public function edit($request, $response, $args)
	{
		$content = [];
		$this->view = $this->container->get('view');
		$params = $request->getParams();
		$id = $args['id'];
		$class = new Campaign($this->container);


		try {

			$result = $class->getCampaignVouchers($id);

			if ($result) {
				$page_data = [
					'page_h1' => 'Edit Campaign',
					'content' => $result,
					'id' => $id,
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])


				];
				//var_dump($result); die;
				return $this->view->render($response, 'campaign_edit.twig', $page_data);
			} else {
				$page_data = [
					'page_h1' => 'Error while creating Vouchers',
					'content' => $content,
					'id' => $this->c_id,
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
				];
				return $this->view->render($response, 'error.twig', $page_data);
			}

		} catch (\Exception $e) {
			//return $this->view->render($response, 'error.twig', $page_data);
			$page_data = [
				'page_h1' => 'Error ',
				'result' => $e->getMessage(),
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])

			];
			return $this->view->render($response, 'error.twig', $page_data);
		}
	}
	public function listit($request, $response)
	{
		$content = [];
		$date = '';
		$period = '';
		$this->view = $this->container->get('view');
		$params = $request->getParams();


		foreach ($params as $key => $value) {

			if ($key === 'created_date') {
				$date = ($value);

			} else if ($key === 'expire') {
				$period = $value;
				$expire = date('Y-m-d H:i:s', strtotime('+' . $period, strtotime($date)));
				$content['expiry'] = $expire;
			}

			$content[$key] = $value;

		}

		//$password = "5xKu1WjoEJj4qptK";
		//SELECT voucher, COUNT(voucher) FROM vouchers GROUP BY voucher HAVING COUNT(voucher) > 1
		$class = new Campaign($this->container);
		$params = ['title' => $content['title'], 'prefix' => $content['prefix'], 'sufix' => $content['sufix'], 'created_date' => $content['dateTime'], 'expiry_date' => $content['expiry']];

		try {
			$result = $class->allCampagins();


			if (!empty($result)) {
				$page_data = [
					'page_h1' => 'Campagins Listed',
					'content' => json_decode($result),
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
				];
				//var_dump(json_decode($result)); die();
				return $this->view->render($response, 'list.twig', $page_data);
			} else {
				$page_data = [
					'page_h1' => 'Error while listing Campaigns',
					'content' => $result,
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
				];
				return $this->view->render($response, 'error.twig', $page_data);
			}

		} catch (\Exception $e) {
			//return $this->view->render($response, 'error.twig', $page_data);
			$page_data = [
				'page_h1' => 'Error ',
				'result' => $e->getMessage(),
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])

			];
			return $this->view->render($response, 'error.twig', $page_data);
		}
	}
	public function update($request, $response)
	{
		$content = [];

		$this->view = $this->container->get('view');
		$params = $request->getParams();
		$rate = $params['rate'];
		$expiry = $params['expiry_date'];
		$id = $params['id'];


		//$password = "5xKu1WjoEJj4qptK";
		//SELECT voucher, COUNT(voucher) FROM vouchers GROUP BY voucher HAVING COUNT(voucher) > 1
		$class = new Campaign($this->container);

		try {
			$result = $class->update($id, $rate, $expiry);

			if (!empty($result)) {
				$page_data = [
					'page_h1' => 'Campagins Listed',
					'content' => json_decode($result),
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
				];
				//var_dump(json_decode($result)); die();
				return $this->view->render($response, 'list.twig', $page_data);
			} else {
				$page_data = [
					'page_h1' => 'Error while updating Campaigns',
					'content' => $result,
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
				];
				return $this->view->render($response, 'error.twig', $page_data);
			}

		} catch (\Exception $e) {
			//return $this->view->render($response, 'error.twig', $page_data);
			$page_data = [
				'page_h1' => 'Error ',
				'result' => $e->getMessage(),
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])

			];
			return $this->view->render($response, 'error.twig', $page_data);
		}
	}



	public function redeem($request, $response)
    {
        $this->view = $this->container->get('view');
        $this->pdo = $this->container->get('db');
        $params = $request->getParams();


        $page_data = [
        	'page_h1' => 'Redeem Voucher',
					'admin' => $_SESSION['auth'],
					'redeem_id' => $params['redeem_id'],
					'redeem_date' => $params['redeem_date'], //date('H:i:s',$_SESSION['time'])
        ];

        return $this->view->render($response, 'redeem.twig', $page_data);

    }

    /** @param $request
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function checkVoucher($request, $response)
    {
        $date = '';
        $period = '';
        $voucher = '';
        $this->view = $this->container->get('view');
				$params = $request->getParams();

			if($params['submit'] == 'Reset'){

				return $response->withRedirect('./');
			}

        elseif ($params['submit'] == 'Stop'){
        	var_dump('clicked stop');
        	var_dump($request->getParams()); die();
		}
		session_start();
		$v = explode('-', $params['voucher']);

		
        //$password = "5xKu1WjoEJj4qptK";
        $class = new Campaign($this->container);
        $result = $class->getCampaignId($v[1]);

					try {
							$updated = $class->redeemVoucher($result);
							$msg = json_decode($updated)->message;
							$rate = json_decode($updated)->rate;

							$key = 	array_search($params['voucher'],$v);


							if (!$key )
								$_SESSION['cart'][]=['voucher'=>$params['voucher'],'rate'=>0,'msg'=>$msg];

							$page_data = [
								'page_h1' => 'Voucher Redeemed',
								'results' => $_SESSION['cart'],//['voucher'=>$params['voucher'],'rate'=>0,'msg'=>''],
								'redeem_id' => $params['redeem_id'],
								'redeem_date' => $params['redeem_date']
							];

							return $this->view->render($response, 'redeem.twig', $page_data);


					}
					catch (\Exception $e) {
						var_dump($e->getMessage()); die;

						if ($_SESSION['cart']){
							//var_dump($_SESSION['cart']);
							$key = array_search($params['voucher'], array_column($_SESSION['cart'], 'voucher'));

						}

						if (!$key)
							$_SESSION['cart'][]=['voucher'=>$params['voucher'],'rate'=>0,'msg'=>''];

						//var_dump($_SESSION);
						$page_data = [
							'page_h1' => 'Error while redeeming Vouchers ',
							'results' => $_SESSION['cart'],//['voucher'=>$params['voucher'],'rate'=>0,'msg'=>$e->getMessage()],
							'redeem_id' => $params['redeem_id'],
							'redeem_date' => $params['redeem_date']
						];

						return $this->view->render($response, 'redeem.twig', $page_data);
					}
    }
}