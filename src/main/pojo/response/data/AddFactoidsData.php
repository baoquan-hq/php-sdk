<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:26 AM
 */

namespace com\baoquan\sdk\pojo\response\data;


class AddFactoidsData
{
    private $success;

    /**
     * @return boolean true if success
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * parse json
     * @param array $json
     */
    public function parse($json) {
        if (!is_array($json)) {
            throw new \InvalidArgumentException('parse failed, json is not array');
        }
        if (isset($json['success'])) {
            $this->success = $json['success'];
        }
    }
}