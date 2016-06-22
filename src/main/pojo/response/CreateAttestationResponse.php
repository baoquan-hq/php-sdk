<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:40 AM
 */

namespace com\baoquan\sdk\pojo\response;


use com\baoquan\sdk\pojo\response\data\CreateAttestationData;

class CreateAttestationResponse extends BaseResponse
{
    private $data;

    /**
     * @return CreateAttestationData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param CreateAttestationData $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * parse json
     * @param array $json
     */
    public function parse($json)
    {
        parent::parse($json);

        if (isset($json['data'])) {
            $this->data = new CreateAttestationData();
            $this->data->parse($json['data']);
        }
    }
}