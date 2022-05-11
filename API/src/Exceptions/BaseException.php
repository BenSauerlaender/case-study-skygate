<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;

//abstract class, that all exceptions inherit from
abstract class BaseException extends Exception
{
    public function __toString()
    {
        $prev = $this->getPrevious();

        //cut the stacktrace if not verbose
        if ($_ENV["VERBOSE_LOGGING"] !== true) {
            $prev = explode("trace:", "$prev")[0];
            $prev = explode("called in", "$prev")[0];
        }

        $path = explode('\\', get_class($this));
        $class = array_pop($path);
        return "$class([{$this->code}]: {$this->message}) -> caused by $prev";
    }
}
