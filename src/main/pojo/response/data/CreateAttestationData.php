<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:28 AM
 */

namespace com\baoquan\sdk\pojo\response\data;


class CreateAttestationData
{
    private $no;

    /**
     * @return string attestation number
     */
    public function getNo()
    {
        return $this->no;
    }

    /**
     * @param string $no
     */
    public function setNo($no)
    {
        $this->no = $no;
    }

    /**
     * parse json
     * @param array $json
     */
    public function parse($json) {
        if (!is_array($json)) {
            throw new \InvalidArgumentException('parse failed, json is not array');
        }
        if (isset($json['no'])) {
            $this->no = $json['no'];
        }
    }
}