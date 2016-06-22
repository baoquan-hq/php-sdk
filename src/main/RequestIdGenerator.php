<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:43 AM
 */

namespace com\baoquan\sdk;


interface RequestIdGenerator
{
    /**
     * create request id
     * @return string
     */
   function createRequestId();
}