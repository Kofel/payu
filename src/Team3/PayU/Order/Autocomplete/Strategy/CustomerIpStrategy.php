<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\PayU\Order\Autocomplete\Strategy;

use Team3\PayU\Configuration\ConfigurationInterface;
use Team3\PayU\Order\Model\OrderInterface;

class CustomerIpStrategy implements AutocompleteStrategyInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function supports(OrderInterface $order)
    {
        return null === $order->getCustomerIp();
    }

    /**
     * @param OrderInterface         $order
     * @param ConfigurationInterface $configuration
     */
    public function autocomplete(
        OrderInterface $order,
        ConfigurationInterface $configuration
    ) {
        $order->setCustomerIp(
            getenv('REMOTE_ADDR')
        );
    }
}
