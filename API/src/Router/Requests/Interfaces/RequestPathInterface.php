<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Requests\Interfaces;

/**
 * Interface for RequestPath
 */
interface RequestPathInterface
{
    /**
     * Gets the path as array
     */
    public function getArray(): array;
}
