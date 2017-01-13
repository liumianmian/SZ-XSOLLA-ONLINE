<?php

namespace Xsolla\SDK\Tests\Api;

use Xsolla\SDK\Api\NumberApi;

class NumberApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var Number
     */
    protected $number;

    public function setUp()
    {
        $this->projectMock = $this->getMock('\Xsolla\SDK\Project', array(), array(), '', false);
        $this->projectMock->expects($this->once())->method('getProjectId')->will($this->returnValue('projectId'));

        $this->userMock = $this->getMock('\Xsolla\SDK\User', array(), array(), '', false);
        $this->userMock->expects($this->once())
            ->method('getV1')
            ->will($this->returnValue('v1'));
        $this->userMock->expects($this->once())
            ->method('getV2')
            ->will($this->returnValue('v2'));
        $this->userMock->expects($this->once())
            ->method('getV3')
            ->will($this->returnValue('v3'));
        $this->userMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('email'));

        $this->clientMock = $this->getMock('\Guzzle\Http\Client', array(), array(), '', false);
        $this->requestMock = $this->getMock('\Guzzle\Http\Message\RequestInterface', array(), array(), '', false);
        $this->responseMock = $this->getMock('\Guzzle\Http\Message\Response', array(), array(), '', false);

        $this->requestMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($this->responseMock));
        $this->clientMock->expects($this->once())->method('get')->with(
            '/xsolla_number.php',
            array(),
            array(
                'query' => array(
                    'project' => 'projectId',
                    'v1' => 'v1',
                    'v2' => 'v2',
                    'v3' => 'v3',
                    'email' => 'email',
                    'format' => 'json'
                )
            )
        )->will($this->returnValue($this->requestMock));

        $this->number = new NumberApi($this->clientMock, $this->projectMock);
    }

    public function testGetNumber()
    {
        $this->responseMock->expects($this->once())
            ->method('json')
            ->will($this->returnValue(array('result' => 0, 'number' => 'number')));

        $this->assertEquals('number', $this->number->getNumber($this->userMock));
    }

    public function testGetNumberInternalException()
    {
        $this->setExpectedException('\Xsolla\SDK\Exception\InternalServerException', 'description', 10);

        $this->responseMock->expects($this->once())
            ->method('json')
            ->will($this->returnValue(array('result' => 10, 'description' => 'description')));

        $this->assertEquals('number', $this->number->getNumber($this->userMock));
    }

    public function testGetNumberInvalidArgumentException()
    {
        $this->setExpectedException('\Xsolla\SDK\Exception\InvalidArgumentException', 'description', 1);

        $this->responseMock->expects($this->once())
            ->method('json')
            ->will($this->returnValue(array('result' => 1, 'description' => 'description')));

        $this->assertEquals('number', $this->number->getNumber($this->userMock));
    }
}
