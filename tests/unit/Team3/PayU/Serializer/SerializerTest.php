<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\PayU\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Team3\PayU\Order\Model\OrderInterface;
use Team3\PayU\Order\Model\OrderStatus;
use tests\unit\Team3\PayU\Serializer\OrderHelper;

/**
 * Class SerializerTest
 * @package Team3\PayU\Serializer
 * @group serializer
 * @group money
 */
class SerializerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SerializationContext
     */
    protected $serializationContext;

    /**
     * @var OrderInterface
     */
    protected $order;

    protected function _before()
    {
        $this->serializer = new Serializer(
            SerializerBuilder::create()->build(),
            new GroupsSpecifier($this->getLogger()),
            $this->getLogger()
        );

        $this->serializationContext = SerializationContext::create();

        $this->order = OrderHelper::getOrderWithDeliveryAndInvoice();
    }

    public function testIfResultIsJson()
    {
        $serialized = $this->serializer->toJson($this->order, $this->serializationContext);
        $this->assertNotEmpty($serialized);
        $this->assertJson($serialized);
    }

    public function testIfResultIsFull()
    {
        $serialized = $this->serializer->toJson($this->order);

        $this->assertEquals(
            OrderHelper::getOrderWithDeliveryAndInvoiceAsJson(),
            $serialized
        );
    }

    public function testResultWithoutDeliveryAndInvoice()
    {
        $serialized = $this->serializer->toJson(
            OrderHelper::getOrderWithoutDeliveryAndInvoice(),
            $this->serializationContext
        );

        $this->assertEquals(
            OrderHelper::getOrderAsJson(),
            $serialized
        );
    }

    public function testDeserialization()
    {
        $serializedString = '{
    "orderId": "{orderId}",
    "extOrderId": "358766",
    "orderCreateDate": "2014-10-27T14:58:17.443+01:00",
    "notifyUrl": "http://localhost/OrderNotify/",
    "customerIp": "127.0.0.1",
    "merchantPosId": "145227",
    "description": "New order",
    "currencyCode": "PLN",
    "totalAmount": "3200",
    "shippingMethods": [
        {
            "country": "PL",
            "name": "Some method",
            "price": "120"
        }
    ],
    "status": "NEW",
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
}';
        /** @var OrderInterface $deserializedObject */
        $deserializedObject = $this->serializer->fromJson($serializedString, 'Team3\PayU\Order\Model\Order');

        $this->assertEquals(
            $deserializedObject->getOrderId(),
            '358766'
        );

        $this->assertEquals(
            $deserializedObject->getNotifyUrl(),
            'http://localhost/OrderNotify/'
        );

        $this->assertEquals(
            $deserializedObject->getTotalAmount()->getValue(),
            32
        );

        $this->assertCount(
            2,
            $deserializedObject->getProductCollection()
        );

        $this->assertEquals(
            OrderStatus::NEW_ORDER,
            $deserializedObject->getStatus()->getValue()
        );

        $this->assertTrue(
            $deserializedObject->getStatus()->isNew()
        );
    }

    /**
     * @expectedException \Team3\PayU\Serializer\SerializerException
     */
    public function testWrongDeserialization()
    {
        $this->serializer->fromJson('{}', 'NonExistenceClass');
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    private function getLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }
}
