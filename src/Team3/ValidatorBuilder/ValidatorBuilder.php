<?php
/**
 * @author Krzysztof Gzocha <krzysztof.gzocha@xsolve.pl>
 */

namespace Team3\ValidatorBuilder;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorBuilder implements ValidatorBuilderInterface
{
    /**
     * @param Reader $reader
     *
     * @return ValidatorInterface
     */
    public function getValidator(Reader $reader = null)
    {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping($reader)
            ->getValidator();
    }
}