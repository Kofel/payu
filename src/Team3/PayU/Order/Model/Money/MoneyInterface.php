<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\PayU\Order\Model\Money;

interface MoneyInterface
{
    /**
     * @return float|int
     */
    public function getValue();

    /**
     * When precision is set to 2 this method will transforms 12.34 into 1234.
     * @param int $precision
     *
     * @return int
     */
    public function getValueWithoutSeparation($precision = 2);

    /**
     * @param MoneyInterface $money
     *
     * @return MoneyInterface
     */
    public function add(MoneyInterface $money);

    /**
     * @param double $multiplier
     *
     * @return MoneyInterface
     */
    public function multiply($multiplier);
}
