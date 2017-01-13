<?php

namespace Xsolla\SDK\Api;

use Xsolla\SDK\Exception\InternalServerException;
use Xsolla\SDK\Exception\InvalidArgumentException;
use Xsolla\SDK\Exception\SecurityException;
use Xsolla\SDK\Invoice;
use Xsolla\SDK\User;
use Guzzle\Http\Client;
use Xsolla\SDK\Project;
use Xsolla\SDK\Validator\Xsd;

/**
 * @link http://xsolla.github.io/en/APImobile.html
 */
class MobilePaymentApi
{
    const CODE_SUCCESS = 0;
    const CODE_ERROR_WRONG_SIGN = 3;
    const CODE_ERROR_INTERNAL_SERVER = 1;

    protected $xsd_path_calculate = '/../../resources/schema/mobilepayment/calculate.xsd';
    protected $xsd_path_invoice = '/../../resources/schema/mobilepayment/invoice.xsd';

    protected $client;
    protected $project;
    protected $url = 'mobile/payment/index.php';

    public function __construct(Client $client, Project $project)
    {
        $this->client = $client;
        $this->project = $project;
    }

    public function createInvoice(User $user, Invoice $invoice)
    {
        $email = $user->getEmail();
        $queryParams = array(
            'command' => 'invoice',
            'project' => $this->project->getProjectId(),
            'v1' => $user->getV1(),
            'v2' => $user->getV2(),
            'v3' => $user->getV3(),
            'sum' => $invoice->getAmount(),
            'out' => $invoice->getVirtualCurrencyAmount(),
            'phone' => $user->getPhone(),
            'userip' => $user->getUserIP()
        );

        if (!empty($email)) {
            $queryParams['email'] = $email;
        }

        $result = $this->send($queryParams, __DIR__ . $this->xsd_path_invoice);

        $this->checkCodeResult($result);

        $resultInvoice = new Invoice();
        $resultInvoice->setId($result->invoice);

        return $resultInvoice;

    }

    protected  function calculate(User $user, array $operationParams)
    {
        $queryParams = array(
            'command' => 'calculate',
            'project' => $this->project->getProjectId(),
            'phone' => $user->getPhone()
        );
        $queryParams = array_merge($queryParams, $operationParams);

        $result = $this->send(
            $queryParams,
            __DIR__ . $this->xsd_path_calculate
        );

        $this->checkCodeResult($result);

        return new Invoice($result->out, $result->sum);
    }

    public function calculateVirtualCurrencyAmount(User $user, $amount)
    {
        $operationParams = array('sum' => $amount);
        return $this->calculate($user, $operationParams);
    }

    public function calculateAmount(User $user, $virtualCurrencyAmount)
    {
        $operationParams = array('out' => $virtualCurrencyAmount);
        return $this->calculate($user, $operationParams);
    }

    protected function createSignString(array $params)
    {
        $signString = '';
        foreach ($params as $value) {
            $signString .= $value;
        }

        return $signString;
    }

    protected function send(array $queryParams, $schemaFilename)
    {
        $signString = $this->createSignString($queryParams);
        $queryParams['md5'] = md5($signString . $this->project->getSecretKey());
        $request = $this->client->get($this->url, array(), array('query' => $queryParams));

        $xsollaResponse = $request->send()->getBody();
        $xsd = new Xsd();
        $xsd->check($xsollaResponse, $schemaFilename);
        $result = new \SimpleXMLElement($xsollaResponse);

        return $result;
    }

    protected function checkCodeResult($result)
    {
        if ($result->result == self::CODE_ERROR_WRONG_SIGN) {
            throw new SecurityException((string) $result->comment, (int) $result->result);
        } elseif ($result->result == self::CODE_ERROR_INTERNAL_SERVER) {
            throw new InternalServerException((string) $result->comment, (int) $result->result);
        } elseif ($result->result != self::CODE_SUCCESS) {
            throw new InvalidArgumentException((string) $result->comment, (int) $result->result);
        }
    }
}
