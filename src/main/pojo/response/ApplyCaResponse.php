<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:42 AM
 */

namespace com\baoquan\sdk\pojo\response;


use com\baoquan\sdk\pojo\response\data\ApplyCaData;

class ApplyCaResponse extends BaseResponse
{
    private $data;

    /**
     * @return ApplyCaData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param ApplyCaData $data
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
            $this->data = new ApplyCaData();
            $this->data->parse($json['data']);
        }
    }
}