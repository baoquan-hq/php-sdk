<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:38 AM
 */

namespace com\baoquan\sdk\pojo\response;


class BaseResponse
{
    private $request_id;

    /**
     * @return string request id
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @param string $request_id
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }

    /**
     * parse json
     * @param array $json
     */
    public function parse($json) {
        if (!is_array($json)) {
            throw new \InvalidArgumentException('parse failed, json is not array');
        }
        if (isset($json['request_id'])) {
            $this->request_id = $json['request_id'];
        }
    }
}