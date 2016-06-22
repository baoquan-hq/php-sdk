<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:12 AM
 */

namespace com\baoquan\sdk\pojo\payload;


class Sign
{
    private $ca_id;

    private $keywords;

    /**
     * @return string ca id used to sign attachment
     */
    public function getCaId()
    {
        return $this->ca_id;
    }

    /**
     * @param string $ca_id
     */
    public function setCaId($ca_id)
    {
        $this->ca_id = $ca_id;
    }

    /**
     * @return array the keyword to locate where to sign
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param array $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
}