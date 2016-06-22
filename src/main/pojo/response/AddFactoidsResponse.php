<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:41 AM
 */

namespace com\baoquan\sdk\pojo\response;


use com\baoquan\sdk\pojo\response\data\AddFactoidsData;

class AddFactoidsResponse extends BaseResponse
{
    private $data;

    /**
     * @return AddFactoidsData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param AddFactoidsData $data
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
            $this->data = new AddFactoidsData();
            $this->data->parse($json['data']);
        }
    }
}