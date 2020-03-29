<?php
namespace App\Controller;


use App\Model\Campaign;
//use Com\Tecnick\Barcode\Barcode;
//use Com\Tecnick\Barcode\Type\Square\QrCode;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 *
 *
 */


class PrintController extends Controller

{
    protected $view = null;
    protected $conn = null;

	/**
	 * @param $request
	 * @param $response
	 * @return mixed
	 */

    public function __invoke(Request $request, Response $response)
    {
			$this->view = $this->container->get('view');
			$this->conn = $this->container->get('db');
			$class = new Campaign($this->container);
			$result = $class->allCampagins();
			

        $page_data = [
					'page_h1' => 'Print',
					'content' => '<p>Print Campaign Vouchers.</p>',
					'campagins' => json_decode($result,true),
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
        ];
        return $this->view->render($response, 'print.twig', $page_data);
    }

    /**
     * @param $request
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function printit($request, $response, $args)
    {
        $content = [];
        $this->view = $this->container->get('view');
        $params = $request->getParams();
        $c_id = $params['campaign'];
        $method = $params['submit'];

        $page = empty($args['page'])?0:$args['page'];


        $class = new Campaign($this->container);
        $perPage=empty($args['perPage'])?10:$args['perPage'];;
				$startAt = $perPage * ($page);

        try {

            $results = $class->getVouchers($c_id, $perPage,$startAt);
            $total = $class->getTotalVouchers($c_id);
            //var_dump($results); die;

						$barcode = array();

            $results = json_decode($results);

            if($method === 'download') {
							foreach ($results as $res) {
								$barcode[] = $res->prefix . $res->voucher . $res->sufix;
							}

							// output headers so that the file is downloaded rather than displayed
							header('Content-Type: text/csv; charset=utf-8');
							header('Content-Disposition: attachment; export.csv');

							$output = fopen('php://output', 'w');

							fputcsv($output, $barcode);

						}


					$generator = new \Com\Tecnick\Barcode\Barcode();


					foreach ($results as $res){
						$voucher='';
						$voucher .= $res->prefix === ''?'':$res->prefix;
						$voucher .= $res->voucher === ''?'':'-'.$res->voucher;
						$voucher .= $res->sufix === ''?'':'-'.$res->sufix;

						$bobj = $generator->getBarcodeObj(
								'QRCODE,H',                     // barcode type and additional comma-separated parameters
								$voucher,          // data string to encode
								-4,                             // bar width (use absolute or negative value as multiplication factor)
								-4,                             // bar height (use absolute or negative value as multiplication factor)
								'black',                        // foreground color
								array(-2, -2, -2, -2)           // padding (use absolute or negative values as multiplication factors)
							)->setBackgroundColor('white'); // background color

						$img = "<img alt=\"Embedded Image\" src=\"data:image/png;base64,".base64_encode($bobj->getPngData())."\" />";

						$barcode[] = ['id'=>$res->id,'expiry'=>$res->expiry_date,'barcode'=>$bobj->getHtmlDiv(),'data'=>$bobj->getPngData(),'img'=>$img, 'voucher'=>$voucher];

						}

            if (sizeof($barcode) > 0) {
                $page_data = [
                    'page_h1' => 'Print Vouchers',
                    'content' => $barcode,
										'page' => $page,
										'total' => $total,
										'c_id'=>$c_id,
										'admin' => $_SESSION['auth'],
										'time' => date('H:i:s',$_SESSION['time'])

                ];
							//$bobj = $barcode->getBarcodeObj('QRCODE,H', 'hello world', -4, -4, 'black');
							//echo "<img alt=\"Embedded Image\" src=\"data:image/png;base64,".base64_encode($bobj->getPngData())."\" />";


                return $this->view->render($response, 'print_vouchers.twig', $page_data);
            } else {
                $page_data = [
                    'page_h1' => 'Error while printing Vouchers',
                    'content' => $content
                ];

                return $this->view->render($response, 'error.twig', $page_data);
            }

        } catch (\Exception $e) {
            return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }


    /** @param $request
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function checkVoucher($request, $response)
    {

			$voucher = '';
        $this->view = $this->container->get('view');
        $params = $request->getParams();

        $password = "5xKu1WjoEJj4qptK";
        $class = new Campaign($this->container);
            //$params = ['prefix' => $voucher[0], 'voucher' => $voucher[1], 'sufix' => $voucher['sufix'], 'redeem_date' => $redeem_date];

					try {
							$valid = $class->validateVoucher($params);


						 if (json_decode($valid)->code == 200)
						 	$exists = $class->voucher_exists($params['voucher']);


						if (json_decode($exists)->code == 200)
							$updated = $class->updateVouchers($params);


						elseif (json_decode($exists)->code != 200)
							throw new \Exception(json_decode($exists)->message, json_decode($exists)->code);


						$page_data = [
								'page_h1' => 'Voucher Redeemed',
								'result' => json_decode($updated)->message,
								'admin' => $_SESSION['auth'],
								'time' => date('H:i:s',$_SESSION['time'])
						];

						return $this->view->render($response, 'redeem.twig', $page_data);


					}
					catch (\Exception $e) {

						$page_data = [
							'page_h1' => 'Error while redeeming Vouchers ',
							'result' => $voucher.$e->getMessage()
						];
						return $this->view->render($response, 'error.twig', $page_data);
					}
    }


}