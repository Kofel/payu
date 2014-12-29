<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */

namespace Team3\Order\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Team3\Order\Model\OrderInterface;
use Team3\Order\Serializer\SerializerInterface as PayUSerializerInterface;

class Serializer implements PayUSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var GroupsSpecifierInterface
     */
    protected $groupsSpecifier;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param SerializerInterface      $serializer
     * @param GroupsSpecifierInterface $groupsSpecifier
     * @param LoggerInterface          $logger
     */
    public function __construct(
        SerializerInterface $serializer,
        GroupsSpecifierInterface $groupsSpecifier,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->groupsSpecifier = $groupsSpecifier;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface       $serializable
     * @param SerializationContext $serializationContext
     *
     * @return string
     */
    public function toJson(
        OrderInterface $serializable,
        SerializationContext $serializationContext = null
    ) {
        if (null == $serializationContext) {
            $serializationContext = new SerializationContext();
        }

        $serializationResult = $this
            ->serializer
            ->serialize(
                $serializable,
                'json',
                $this->getSerializationContext($serializable, $serializationContext)
            );
        $this->logSerializationResult($serializable, $serializationResult);

        return $serializationResult;
    }

    /**
     * @param string $data
     * @param string $type
     *
     * @return array|object
     * @throws SerializerException
     */
    public function fromJson($data, $type)
    {
        try {
            $result = $this
                ->serializer
                ->deserialize(
                    $data,
                    $type,
                    'json'
                );
        } catch (\Exception $exception) {
            $adaptedException = new SerializerException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
            $this->logException($adaptedException);
            throw $adaptedException;
        }

        return $result;
    }

    /**
     * @param OrderInterface       $order
     * @param SerializationContext $serializationContext
     *
     * @return SerializationContext
     */
    private function getSerializationContext(
        OrderInterface $order,
        SerializationContext $serializationContext
    ) {
        $serializationContext->setGroups(
            $this->groupsSpecifier->specifyGroups($order)
        );

        return $serializationContext;
    }

    /**
     * @param OrderInterface $order
     * @param string         $result
     */
    private function logSerializationResult(
        OrderInterface $order,
        $result
    ) {
        $this
            ->logger
            ->debug(sprintf(
                'Order with id %s was serialized to "%s"',
                $order->getOrderId(),
                $result
            ));
    }

    /**
     * @param \Exception $exception
     */
    private function logException(\Exception $exception)
    {
        $this->logger->error(sprintf(
            '%s exception occurred on deserialization with message "%s"',
            get_class($exception),
            $exception->getMessage()
        ));
    }
}
