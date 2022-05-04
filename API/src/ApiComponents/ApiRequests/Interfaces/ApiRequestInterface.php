<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Interfaces;

/**
 * Interface for Request
 */
interface ApiRequestInterface
{
    /**
     * Gets the Query in a key-value format.
     * 
     * @return array<string,string>
     */
    public function getQuery(): array;
}
