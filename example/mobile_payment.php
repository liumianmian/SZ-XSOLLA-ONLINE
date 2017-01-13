<?php
require_once __DIR__.'/../vendor/autoload.php';

use Xsolla\SDK\Api\ApiFactory;
use Xsolla\SDK\Invoice;
use Xsolla\SDK\Project;
use Xsolla\SDK\User;
use \Guzzle\Http\Client;

$user = new User('username');
$user->setPhone('79120000000');

$demoProject = new Project(
    '14582',//demo project id
    'rW7cl4gPZwc2ntBJ'//demo project secret key
);

$apiFactory = new ApiFactory($demoProject);

$mobilePaymentApi = $apiFactory->getMobilePaymentApi();

$invoice = $mobilePaymentApi->calculateAmount($user, 1000);
echo 'Cost of 1000 units of virtual currency: ' . $invoice->getAmount() . PHP_EOL;

$invoice = $mobilePaymentApi->calculateVirtualCurrencyAmount($user, 100);
echo 'Amount of virtual currency that can be bought for 100 rubles: ' . $invoice->getVirtualCurrencyAmount() . PHP_EOL;

$invoice = $mobilePaymentApi->createInvoice($user, new Invoice(null, 100));
echo 'Issue an invoice for a virtual currency for 100 rubles. Your invoice number: ' . $invoice->getId() . PHP_EOL;
