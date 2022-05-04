<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces;

/**
 * Interface for ApiPath
 */
interface ApiPathInterface
{
    /**
     * Gets the path as array
     */
    public function getArray(): array;
}
