<?php
namespace App\Controller;


use Slim\Http\Response;
use Slim\Http\Request;

/**
 * HomeController
 *
 */
class HomeController extends Controller

{
    public function __invoke(Request $request, Response $response)
    {
        $view = $this->container->get('view');

        $page_data = [
            'page_h1'=>'Vouchers',
            'title' => 'KL Coffee Estate',
            'content' => "Create new group of vouchers for distribution.
            <p>Print Page creates
            pages of vouchers in a card format for the printers.</p>
            <p>Redeem Voucher allows the administrator
            to scan the printed barcode and verify to authorize payment.</p>
            <p>Vouchers can only be redeemed once. Each payment requires a new 
            voucher and barcode.</p> 
            <p>Administrator and Printer must sign-in with passwords given.</p>",
					'admin' => $_SESSION['auth'],
					'time' => date('H:i:s',$_SESSION['time'])
        ];
        return $view->render($response, 'index.twig', $page_data);
    }
}