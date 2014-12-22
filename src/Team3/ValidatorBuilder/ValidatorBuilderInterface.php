<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */
namespace Team3\ValidatorBuilder;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\Validator\ValidatorInterface;

interface ValidatorBuilderInterface
{
    /**
     * @param Reader $reader
     *
     * @return ValidatorInterface
     */
    public function getValidator(Reader $reader = null);
}
