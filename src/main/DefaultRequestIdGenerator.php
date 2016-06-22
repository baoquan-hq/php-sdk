<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:43 AM
 */

namespace com\baoquan\sdk;


class DefaultRequestIdGenerator implements RequestIdGenerator
{
    /**
     * create request id
     * @return string
     */
    function createRequestId()
    {
        return md5(uniqid("", true));
    }
}