<?php
namespace Team3\PayU\SignatureCalculator;

use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Team3\PayU\Configuration\Credentials\Credentials;
use Team3\PayU\Configuration\Credentials\TestCredentials;
use Team3\PayU\Order\Model\Money\Money;
use Team3\PayU\Order\Model\Order;
use Team3\PayU\Order\Model\Products\Product;
use Team3\PayU\Serializer\GroupsSpecifier;
use Team3\PayU\Serializer\Serializer;
use Team3\PayU\SignatureCalculator\Encoder\Algorithms\Md5Algorithm;
use Team3\PayU\SignatureCalculator\Encoder\Encoder;
use Team3\PayU\SignatureCalculator\Encoder\EncoderException;
use Team3\PayU\SignatureCalculator\Encoder\Strategy\Md5Strategy;
use Team3\PayU\SignatureCalculator\ParametersSorter\ParametersSorter;
use Team3\PayU\SignatureCalculator\ParametersSorter\ParametersSorterInterface;

/**
 * Class OrderSignatureCalculatorTest
 * @package Team3\PayU\SignatureCalculator
 * @group signature
 */
class OrderSignatureCalculatorTest extends \Codeception\TestCase\Test
{
    const ENCODED_STRING = 'encodedString';
    const ALGORITHM = 'algorithm';
    const MERCHANT_POS_ID = '123';
    const EXCEPTION_MESSAGE = 'Exception message';

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ParametersSorterInterface
     */
    protected $parametersSorter;

    /**
     * @var OrderSignatureCalculatorInterface
     */
    protected $signatureCalculator;

    protected function _before()
    {
        $this->parametersSorter = $this
            ->getMockBuilder('Team3\PayU\SignatureCalculator\ParametersSorter\ParametersSorter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->parametersSorter
            ->expects($this->any())
            ->method('getSortedParameters')
            ->withAnyParameters()
            ->willReturn([
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]);

        $encoder = $this
            ->getMockBuilder('Team3\PayU\SignatureCalculator\Encoder\Encoder')
            ->disableOriginalConstructor()
            ->getMock();

        $encoder
            ->expects($this->any())
            ->method('encode')
            ->withAnyParameters()
            ->willReturn(self::ENCODED_STRING);

        $this->signatureCalculator = new OrderSignatureCalculator(
            $encoder,
            $this->parametersSorter,
            $this->getLogger()
        );
    }

    public function testSignature()
    {
        $credentials = new Credentials(self::MERCHANT_POS_ID, '456');
        $order = new Order();
        $algorithm = $this
            ->getMockBuilder('Team3\PayU\SignatureCalculator\Encoder\Algorithms\AlgorithmInterface')
            ->getMock();
        $algorithm
            ->expects($this->any())
            ->method('getName')
            ->willReturn(self::ALGORITHM);

        $signature = $this->signatureCalculator->calculate($order, $credentials, $algorithm);
        $this->assertEquals(
            sprintf(
                OrderSignatureCalculator::SIGNATURE_FORMAT,
                self::ENCODED_STRING,
                self::ALGORITHM,
                self::MERCHANT_POS_ID
            ),
            $signature
        );
    }

    /**
     * @expectedException \Team3\PayU\SignatureCalculator\SignatureCalculatorException
     * @expectedExceptionMessage Exception message
     */
    public function testSignatureCalculatorException()
    {
        $credentials = new TestCredentials();
        $order = new Order();

        $algorithm = $this
            ->getMockBuilder('\Team3\PayU\SignatureCalculator\Encoder\Algorithms\AlgorithmInterface')
            ->getMock();

        $encoder = $this
            ->getMockBuilder('\Team3\PayU\SignatureCalculator\Encoder\Encoder')
            ->disableOriginalConstructor()
            ->getMock();
        $encoder
            ->expects($this->any())
            ->method('encode')
            ->withAnyParameters()
            ->willThrowException(new EncoderException(self::EXCEPTION_MESSAGE));

        $signatureCalculator = new OrderSignatureCalculator(
            $encoder,
            $this->parametersSorter,
            $this->getLogger()
        );

        $signatureCalculator->calculate($order, $credentials, $algorithm);
    }

    public function testRealExample()
    {
        $credentials = new TestCredentials();
        $order = $this->getOrder();
        $algorithm = new Md5Algorithm();
        $encoder = new Encoder($this->getLogger());
        $encoder->addStrategy(new Md5Strategy());
        $signatureCalculator = new OrderSignatureCalculator(
            $encoder,
            $this->getParametersSorter(),
            $this->getLogger()
        );

        $this->assertEquals(
            'signature=0fcbdfd920b218edd56366966bef2dcc;algorithm=MD5;sender=145227',
            $signatureCalculator->calculate($order, $credentials, $algorithm)
        );
    }

    /**
     * @return ParametersSorter
     */
    private function getParametersSorter()
    {
        $parametersSorter = new ParametersSorter(
            new Serializer(
                SerializerBuilder::create()->build(),
                new GroupsSpecifier($this->getLogger()),
                $this->getLogger()
            )
        );

        return $parametersSorter;
    }

    /**
     * Will return initialized order with parameters taken from
     * {@link http://developers.payu.com/pl/restapi.html#payment_form}
     * @return Order
     */
    private function getOrder()
    {
        $order = new Order();
        $order->setCustomerIp('123.123.123.123');
        $order->setMerchantPosId('145227');
        $order->setDescription('Order description');
        $order->setTotalAmount(new Money(10));

        /**
         * Documentation was mistaken in currency code.
         * Correct code was taken from {@link http://jsfiddle.net/FDrsF/177/}
         */
        $order->setCurrencyCode('PLN');
        $order->setContinueUrl('http://localhost/continue');
        $order->setNotifyUrl('http://shop.url/notify.json');
        $order
            ->getProductCollection()
            ->addProduct(
                (new Product())
                    ->setName('Product 1')
                    ->setUnitPrice(new Money(10))
                    ->setQuantity(1)
            );

        return $order;
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }
}
