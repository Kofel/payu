<?php
namespace Team3\Communication\RequestProcess;

use Buzz\Message\MessageInterface;
use Buzz\Message\Response;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Team3\Communication\ClientInterface;
use Team3\Communication\Process\RequestProcess;
use Team3\Communication\Process\RequestProcessException;
use Team3\Communication\RequestBuilder\CreateOrderRequestBuilder;
use Team3\Communication\RequestBuilder\OrderStatusRequestBuilder;
use Team3\Communication\Response\CreateOrderResponse;
use Team3\Communication\Response\OrderStatusResponse;
use Team3\Configuration\Configuration;
use Team3\Configuration\ConfigurationInterface;
use Team3\Configuration\Credentials\TestCredentials;
use Team3\Order\Model\Order;
use Team3\Order\Serializer\GroupsSpecifier;
use Team3\Order\Serializer\Serializer;
use Team3\Order\Serializer\SerializerException;
use Team3\Order\Serializer\SerializerInterface;

/**
 * Class RequestProcessTest
 * @package Team3\Communication\RequestProcess
 * @group communication
 */
class RequestProcessTest extends \Codeception\TestCase\Test
{
    const ORDER_ID = 'WZHF5FFDRJ140731GUEST000P01';
    const EXT_ORDER_ID = '123';
    const REDIRECT_URI = 'http://localhost';
    const ORDER_STATUS = 'NEW';

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    protected function _before()
    {
        $this->serializer = new Serializer(
            SerializerBuilder::create()->build(),
            new GroupsSpecifier($this->getLogger()),
            $this->getLogger()
        );

        $this->configuration = new Configuration(new TestCredentials());
    }

    public function testCreateOrderRequestProcess()
    {
        $createOrderRequestBuilder = new CreateOrderRequestBuilder($this->serializer);

        $response = $this
            ->getRequestProcess($this->getCreateOrderCurlResponse())
            ->process(
                $createOrderRequestBuilder->build(new Order()),
                $this->configuration
            );

        $this->assertInstanceOf(
            '\Team3\Communication\Response\CreateOrderResponse',
            $response
        );
        $this->assertEquals(
            self::ORDER_ID,
            $response->getOrderId()
        );
        $this->assertEquals(
            self::EXT_ORDER_ID,
            $response->getExtOrderId()
        );
        $this->assertEquals(
            self::REDIRECT_URI,
            $response->getRedirectUri()
        );

        $this->assertTrue(
            $response->getRequestStatus()->isSuccess()
        );
    }

    public function testOrderStatusRequestProcess()
    {
        $orderStatusRequestBuilder = new OrderStatusRequestBuilder();

        /** @var OrderStatusResponse $response */
        $response = $this
            ->getRequestProcess($this->getOrderStatusCurlResponse())
            ->process(
                $orderStatusRequestBuilder->build(new Order()),
                $this->configuration
            );

        $this->assertInstanceOf(
            'Team3\Communication\Response\OrderStatusResponse',
            $response
        );
        $this->assertEquals(
            self::ORDER_ID,
            $response->getFirstOrder()->getPayUOrderId()
        );
        $this->assertEquals(
            self::EXT_ORDER_ID,
            $response->getFirstOrder()->getOrderId()
        );
        $this->assertTrue(
            $response->getRequestStatus()->isSuccess()
        );
        $this->assertTrue(
            $response->getFirstOrder()->getStatus()->isNew()
        );
    }

    /**
     * @expectedException \Team3\Communication\Process\NoResponseObjectException
     */
    public function testNoResponseObjectException()
    {
        $client = $this->getMock('\Team3\Communication\ClientInterface');
        $client
            ->expects($this->any())
            ->method('sendRequest')
            ->withAnyParameters()
            ->willReturn(
                new Response()
            );

        $requestProcess = new RequestProcess(
            $this->serializer,
            $client
        );

        $requestProcess->process(
            $this->getMock('\Team3\Communication\Request\PayURequestInterface'),
            $this->configuration
        );
    }

    /**
     * @expectedException \Team3\Communication\Process\RequestProcessException
     */
    public function testSerializerException()
    {
        $message = new Response();
        $message->setContent('123');
        $response = $this
            ->getMockBuilder('\Team3\Communication\Response\ResponseInterface')
            ->getMock();
        $response
            ->expects($this->any())
            ->method('supports')
            ->withAnyParameters()
            ->willReturn(true);

        $client = $this->getMock('\Team3\Communication\ClientInterface');
        $client
            ->expects($this->any())
            ->method('sendRequest')
            ->withAnyParameters()
            ->willReturn($message);
        $serializer = $this
            ->getMockBuilder('\Team3\Order\Serializer\SerializerInterface')
            ->getMock();
        $serializer
            ->expects($this->any())
            ->method('fromJson')
            ->withAnyParameters()
            ->willThrowException(new SerializerException());

        $requestProcess = new RequestProcess(
            $serializer,
            $client
        );
        $requestProcess->addResponse($response);

        $requestProcess->process(
            $this->getMock('\Team3\Communication\Request\PayURequestInterface'),
            $this->configuration
        );
    }

    /**
     * @param Response $curlResponse
     *
     * @return RequestProcess
     */
    private function getRequestProcess(Response $curlResponse)
    {
        $requestProcess = new RequestProcess(
            $this->serializer,
            $this->getClient($curlResponse)
        );

        $requestProcess
            ->addResponse(new OrderStatusResponse())
            ->addResponse(new CreateOrderResponse());

        return $requestProcess;
    }

    /**
     * @param MessageInterface $message
     *
     * @return ClientInterface
     */
    private function getClient(MessageInterface $message)
    {
        $client = $this
            ->getMockBuilder('Team3\Communication\ClientInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $client
            ->expects($this->any())
            ->method('sendRequest')
            ->withAnyParameters()
            ->willReturn($message);

        return $client;
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this
            ->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();
    }

    /**
     * @return Response
     */
    private function getCreateOrderCurlResponse()
    {
        $response = new Response();
        $response->setContent(sprintf('{
   "status":{
      "statusCode":"SUCCESS"
   },
   "redirectUri":"%s",
   "orderId":"%s",
   "extOrderId":"%s"
}',
            self::REDIRECT_URI,
            self::ORDER_ID,
            self::EXT_ORDER_ID
        ));

        return $response;
    }

    /**
     * @return Response
     */
    private function getOrderStatusCurlResponse()
    {
        $response = new Response();
        $response->setContent('{
        "orders": [
            {
                "orderId": "'.self::ORDER_ID.'",
                "extOrderId": "'.self::EXT_ORDER_ID.'",
                "orderCreateDate": "2014-10-27T14:58:17.443+01:00",
                "notifyUrl": "http://localhost/OrderNotify/",
                "customerIp": "127.0.0.1",
                "merchantPosId": "145227",
                "description": "New order",
                "currencyCode": "PLN",
                "totalAmount": "3200",
                "status": "'.self::ORDER_STATUS.'",
                "products": [
                    {
                        "name": "Product1",
                        "unitPrice": "1000",
                        "quantity": "1"
                    },
                    {
                        "name": "Product2",
                        "unitPrice": "2200",
                        "quantity": "1"
                    }
                ]
            }
        ],
        "status": {
            "statusCode": "SUCCESS",
            "statusDesc": "Request processing successful"
        }
}');

        return $response;
    }
}
