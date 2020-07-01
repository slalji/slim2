<?php


use App\Controller\AdminAuthController;
use App\Controller\CampaignController;
use App\Controller\HomeController;
use App\Controller\PrintController;
use App\Controller\ReportController;

$container = $app->getContainer();


// SITE ROUTES (Views)
//$app->get('/', App\Controller\HomeController::class)->setName('home');
$app->get('/', App\Controller\CampaignController::class )->setName('home');
$app->post('/start', App\Controller\CampaignController::class . ':start')->setName('start');
$app->post('/stop', App\Controller\CampaignController::class . ':stop')->setName('stop');
$app->get('/redeem', App\Controller\CampaignController::class . ':redeem')->setName('redeem');
$app->get('/user/new', App\Controller\AdminAuthController::class .':user'  )->setName('user');
$app->post('/user/create', App\Controller\AdminAuthController::class .':create'  )->setName('user-create');
$app->post('/voucher', App\Controller\CampaignController::class . ':checkVoucher')->setName('redeem');

$app->group('/admin', function () {
	$this->get('/home', App\Controller\HomeController::class)->setName('home');
	$this->post('/sign', App\Controller\HomeController::class  )->setName('sign');
	$this->get('/logout', App\Controller\AdminAuthController::class .':logout'  )->setName('logout');


	$this->get('/campaign', App\Controller\CampaignController::class .':campaign')->setName('campaign');
	$this->post('/campaign/create', App\Controller\CampaignController::class . ':create');
	$this->get('/campaign/edit/{id}', App\Controller\CampaignController::class . ':edit')->setName('edit');
	$this->get('/campaign/delete/{id}', App\Controller\CampaignController::class . ':delete')->setName('delete');
	$this->post('/campaign/edit/update', App\Controller\CampaignController::class . ':update')->setName('update');

	$this->get('/campaign/list', App\Controller\CampaignController::class . ':listit')->setName('listit');
	$this->get('/print', App\Controller\PrintController::class)->setName('print')->setName('print');
	$this->get('/print/select', App\Controller\PrintController::class .':select')->setName('select');
//$this->post('/print/print', App\Controller\PrintController::class .':printit')->setName('printit');
	$this->get('/print/print[/{page}]', App\Controller\PrintController::class .':printit')->setName('printit');
	$this->get('/print/printall[/{page}]', App\Controller\PrintController::class .':printit')->setName('printall');

	$this->get('/report/{cid}', App\Controller\ReportController::class )->setName('report');
	$this->get('/report/all/{cid}', App\Controller\ReportController::class . ":all")->setName('all');
	$this->get('/report/name/{cid}', App\Controller\ReportController::class .":name")->setName('report');
	$this->get('/report/name/download{cid}', App\Controller\ReportController::class .":name")->setName('report');
	$this->post('/report/search/{cid}', App\Controller\ReportController::class .":search")->setName('search');

})->add(new AuthMiddleware($container));