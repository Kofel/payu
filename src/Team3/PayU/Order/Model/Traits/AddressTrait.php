<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\PayU\Order\Model\Traits;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait AddressTrait
{
    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     * @JMS\SerializedName("postalCode")
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     * @JMS\SerializedName("countryCode")
     * @Assert\Country()
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     * @JMS\SerializedName("recipientName")
     */
    protected $recipientName;

    /**
     * @var string
     * @JMS\SerializedName("recipientEmail")
     * @Assert\Email()
     */
    protected $recipientEmail;

    /**
     * @var string
     * @JMS\SerializedName("recipientPhone")
     */
    protected $recipientPhone;

    /**
     * Return true if given object is filled
     *
     * @return bool
     */
    public function isFilled()
    {
        return $this->getStreet()
            && $this->getCity()
            && $this->getCountryCode()
            && $this->getPostalCode();
    }

    /**
     * @param ExecutionContextInterface $executionContext
     * @Assert\Callback()
     */
    public function validate(
        ExecutionContextInterface $executionContext
    ) {
        if (!$this->getStreet()
            && !$this->getCity()
            && !$this->getCountryCode()
            && !$this->getPostalCode()) {
            return;
        }

        if (!$this->getStreet()
            || !$this->getCity()
            || !$this->getCountryCode()
            || !$this->getPostalCode()) {
            $executionContext
                ->buildViolation(
                    sprintf('Object %s is not filled correctly', get_class($this))
                )
                ->addViolation();
        }
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * @param string $recipientEmail
     *
     * @return $this
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }

    /**
     * @param string $recipientName
     *
     * @return $this
     */
    public function setRecipientName($recipientName)
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientPhone()
    {
        return $this->recipientPhone;
    }

    /**
     * @param string $recipientPhone
     *
     * @return $this
     */
    public function setRecipientPhone($recipientPhone)
    {
        $this->recipientPhone = $recipientPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }
}
