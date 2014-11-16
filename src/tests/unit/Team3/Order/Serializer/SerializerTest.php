<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\Order\Serializer;

use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Team3\Order\Model\OrderInterface;
use tests\unit\Team3\Order\Serializer\OrderHelper;

/**
 * Class SerializerTest
 * @package Team3\Order\Serializer
 * @group serializer
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
     * @var OrderInterface
     */
    protected $order;

    protected function _before()
    {
        new ExclusionPolicy(['value' => 'all']);
        new VirtualProperty();
        new Type();
        new SerializedName(['value' => 'name']);
        new AccessorOrder();
        new Groups();

        $this->serializer = new Serializer(
            SerializerBuilder::create()->build()
        );

        $this->order = OrderHelper::getOrderWithDeliveryAndInvoice();
    }

    public function testIfResultIsJson()
    {
        $serialized = $this->serializer->toJson($this->order);
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
        $serialized = $this->serializer->toJson(OrderHelper::getOrderWithoutDeliveryAndInvoice());

        $this->assertEquals(
            OrderHelper::getOrderAsJson(),
            $serialized
        );
    }
}