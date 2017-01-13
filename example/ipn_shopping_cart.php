<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Xsolla\SDK\Validator\IpChecker;
use Xsolla\SDK\Protocol\Storage\Pdo\PaymentShoppingCartStorage;
use Xsolla\SDK\Project;
use Xsolla\SDK\Protocol\ProtocolFactory;

$demoProject = new Project(
    '4783',//demo project id
    'key'//demo project secret key
);
$pdo = new \PDO(sprintf('mysql:dbname=%s;host=%s;charset=utf8', 'YOUR_DB_NAME', 'YOUR_DB_HOST'), 'YOUR_DB_USER', 'YOUR_DB_PASSWORD');
$paymentStorage = new PaymentShoppingCartStorage($pdo);
$ipChecker = new IpChecker;
$protocolBuilder = new ProtocolFactory($demoProject, $ipChecker);
$protocol = $protocolBuilder->getShoppingCartProtocol($paymentStorage);

$request = Request::createFromGlobals();
$response = $protocol->run($request);
$response->send();
