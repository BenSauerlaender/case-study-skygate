<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BaseResponse;

/**
 * Response that should be used if the request was bad.
 */
class BadRequestResponse extends BaseResponse
{
    public function __construct(string $msg = "", array $info = [])
    {
        $this->setCode(400);
        $data = [];
        if ($msg !== "") $data["msg"] = $msg;
        if (!empty($info)) {
            foreach ($info as $key => $value) {
                if (is_string($key)) {
                    $data[$key] = $value;
                }
            }
        }
        if (!empty($data)) $this->setData($data);
    }
}
