<?php
namespace App\Controller;


use App\Model\Report;
use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Type\Square\QrCode;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 *
 *
 */


class ReportController extends Controller

{
    protected $view = null;
    protected $pdo = null;
    protected $c_id = '';

  public function __invoke(Request $request, Response $response)
    {
			$this->view = $this->container->get('view');
			$this->pdo = $this->container->get('db');
			$cid = $request->getAttribute('cid');
			$date = date('Y-m-d');

			$class = new Report($this->container);
			$report = ['By Receipt Numbers'=>'all/'.$cid,'By Name'=>'name/'.$cid,'By Receipt'=>'receipt/'.$cid];


        $page_data = [
            'page_h1' => 'Reports ',
            'cid' => $cid,
						'content'=>$report,
						'admin' => $_SESSION['auth'],
						'time' => date('H:i:s',$_SESSION['time'])
        ];
        //var_dump($page_data['content']); die;
        return $this->view->render($response, 'report_home.twig', $page_data);
    }
	public function name(Request $request, Response $response)
	{
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');
		$cid = $request->getAttribute('cid');
		$download = $request->getParam('download');
		$date = date('Y-m-d');

		$class = new Report($this->container);
		$results = json_decode($class->redeemedByName($cid), true);
		$headings = array_keys($results[0]);
		if ($download != 'Download'){
			$page_data = [
				'page_h1' => 'Reports ',
				'cid' => $cid,
				'content'=>$results,
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])
			];
			//var_dump($page_data['content']); die;
			return $this->view->render($response, 'report.twig', $page_data);
		}
		else{
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=export.csv');
			$filename = 'export.csv';

			$data = fopen($filename, 'w');
			foreach($headings as $h){

				echo  $h . ", ";
				fputcsv($data, $h, ',', '"');
			}
			foreach($results as $item) {
				echo "\r\n";
				foreach($item as $row){

					echo  $row . ", ";
					fputcsv($data, $row, ',', '"');
				}

			}
			fclose($data);

		}


	}
	public function receipt(Request $request, Response $response)
	{
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');
		$cid = $request->getAttribute('cid');
		$download = $request->getParam('download');
		$r_date = $request->getParam('r_date');
		
		$class = new Report($this->container);
		$results = json_decode($class->redeemedByReceipt($r_date), true);
		$r_dates = json_decode($class->getRedeemedDates(), true);
//
		
		$headings = array_keys($results[0]);
		if ($download != 'Download'){
		
			$page_data = [
				'page_h1' => 'Reports',
				'cid' => $cid,
				'content'=>$results,
        'r_dates'=>$r_dates,
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])
			];
			return $this->view->render($response, 'report_receipt.twig', $page_data);
		}
		
		else{
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=export.csv');
			$filename = 'export.csv';

			$data = fopen($filename, 'w');
			foreach($headings as $h){

				echo  $h . ", ";
				fputcsv($data, $h, ',', '"');
			}
			foreach($results as $item) {
				echo "\r\n";
				foreach($item as $row){

					echo  $row . ", ";
					fputcsv($data, $row, ',', '"');
				}

			}
			fclose($data);

		}


	}

	public function CampaignID(Request $request, Response $response)
	{
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');
		$cid = $request->getAttribute('cid');

		$class = new Report($this->container);
		$report = json_decode($class->redeemedByCID($cid), true);


		$page_data = [
			'page_h1' => 'Report Campaign By ID',
			'content' => $report,
			'admin' => $_SESSION['auth'],
			'time' => date('H:i:s',$_SESSION['time'])
		];
		//var_dump($page_data['content']); die;
		return $this->view->render($response, 'report.twig', $page_data);
	}

    /**
     * @param $request
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function search(Request $request, Response $response)
    {
    	  $content = [];
        $this->view = $this->container->get('view');
        $params = $request->getParams();
        $c_id = $params['cid'];
        $needle = $request->getParam('needle');
        $class = new Report($this->container);

        try {
        	unset($params['submit']);

            $results = $class->search($needle);

            $results = json_decode($results,true);
					 $titles = (array_keys($results['message'][0])); 
					//echo $results['message'];
					

					

					 $page_data = [
						'page_h1' => 'Search Reports',
						'content'=> $results['message'],
						'titles'=> $titles,
						'cid' => $c_id,
						'item' => $needle,
						'admin' => $_SESSION['auth'],
						'time' => date('H:i:s',$_SESSION['time'])
					];  
					 return $this->view->render($response, 'report_search.twig', $page_data);



				} catch (\Exception $e) {
            return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
        public function searchVoucher(Request $request, Response $response)
    {
    	  $content = [];
        $this->view = $this->container->get('view');
        $params = $request->getParams();
        $c_id = $params['cid'];
        //$needle = $request->getParam('needle');
        $class = new Report($this->container);
        //MB-5e94aaf7bc32e-YL
        $needle = explode('-', $request->getParam('needle'));

if (empty($needle))
	return json_encode(['code' => 404, 'message' => 'Error input type']);

//var_dump($needle); die();

        try {
        	unset($params['submit']);

            $results = $class->search($needle[1]);

            $results = json_decode($results,true);
					 $titles = (array_keys($results['message'][0]));

					 $page_data = [
						'page_h1' => 'Search Reports',
						'content'=> $results['message'],
						'titles'=> $titles,
						'admin' => $_SESSION['auth'],
						'time' => date('H:i:s',$_SESSION['time'])
					]; 
					 return $this->view->render($response, 'report_search.twig', $page_data);



				} catch (\Exception $e) {
            return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
	public function results($request, $response, $args)
	{
		$content = [];
		$this->view = $this->container->get('view');
		$params = $request->getParams();
		$c_id = $params['cid'];
		$needle = $params['search'];


		$class = new Report($this->container);

		try {
			unset($params['submit']);

			$results = $class->search($params);
			$results = json_decode($results)->message;
			$len = $total = count((array)$results);


			$page_data = [
				'page_h1' => 'Search Reports',
				'len' => $len,
				'item' => $params['search'],
				'qr'=>$results->qr,
				'rate'=>$results->rate,
				'redeem_id'=>$results->redeem_id,
				'name'=>$results->user_name,
				'phone'=>$results->user_phone,
				'comment'=>$results->user_comment,
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])
			];
			//var_dump($page_data['content']); die;
			return $this->view->render($response, 'search_report.twig', $page_data);



		} catch (\Exception $e) {
			return json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
		}
	}
	public function all($request, $response, $args){
		$this->view = $this->container->get('view');
		$this->pdo = $this->container->get('db');
		$class = new Report($this->container);


		$c_id = $args['cid'];
		$download = $request->getParam('download');

		$results = $class->redeemedByCID($c_id);
		$results = json_decode($results, true);
		$headings = array_keys($results[0]);


		if ($download != 'Download'){
			$page_data = [
				'page_h1' => 'All Reports',
				'content'=>$results,
				'cid'=>$c_id,
				'admin' => $_SESSION['auth'],
				'time' => date('H:i:s',$_SESSION['time'])
			];
			//var_dump($page_data['content']); die;
			return $this->view->render($response, 'report_campaign_all.twig', $page_data);
		}
		else{
			//var_dump($download); die;
			//@header("Content-Disposition: attachment; filename=export.csv");
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=export.csv');
			$filename = 'export.csv';


			$data = fopen($filename, 'w');
			foreach($headings as $h){

				echo  $h . ", ";
				fputcsv($data, $h, ',', '"');
			}
			foreach($results as $item) {
				echo "\r\n";
				foreach($item as $row){

					echo  $row . ", ";
					fputcsv($data, $row, ',', '"');
				}

			}

			fclose($data);

		}


	}



}
