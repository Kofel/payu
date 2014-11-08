<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\Order\Model\General;

interface GeneralInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return General
     */
    public function setDescription($description);

    /**
     * @string
     */
    public function getAdditionalDescription();

    /**
     * @param string $additionalDescription
     *
     * @return General
     */
    public function setAdditionalDescription($additionalDescription);

    /**
     * @inheritdoc
     */
    public function getCurrencyCode();

    /**
     * @param string $currencyCode
     *
     * @return General
     */
    public function setCurrencyCode($currencyCode);

    /**
     * @inheritdoc
     */
    public function getCustomerIp();

    /**
     * @param string $customerIp
     *
     * @return General
     */
    public function setCustomerIp($customerIp);

    /**
     * @inheritdoc
     */
    public function getMerchantPosId();

    /**
     * @param string $merchantPosId
     *
     * @return General
     */
    public function setMerchantPosId($merchantPosId);

    /**
     * @inheritdoc
     */
    public function getOrderId();

    /**
     * @param string $orderId
     *
     * @return General
     */
    public function setOrderId($orderId);

    /**
     * @inheritdoc
     */
    public function getSignature();

    /**
     * @param string $signature
     *
     * @return General
     */
    public function setSignature($signature);

    /**
     * @inheritdoc
     */
    public function getTotalAmount();

    /**
     * @param int $totalAmount
     *
     * @return General
     */
    public function setTotalAmount($totalAmount);
}