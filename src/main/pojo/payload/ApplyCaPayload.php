<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:20 AM
 */

namespace com\baoquan\sdk\pojo\payload;


class ApplyCaPayload
{
    private $type;

    private $name;

    private $ic_code;

    private $org_code;

    private $tax_code;

    private $link_name;

    private $link_id_card;

    private $link_phone;

    private $link_email;

    /**
     * @return string ca type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * enterprise name, must not be empty when type is enterprise
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * license, must not be empty when type is enterprise
     * @return string
     */
    public function getIcCode()
    {
        return $this->ic_code;
    }

    /**
     * @param string $ic_code
     */
    public function setIcCode($ic_code)
    {
        $this->ic_code = $ic_code;
    }

    /**
     * organization code, must not be empty when type is enterprise
     * @return string
     */
    public function getOrgCode()
    {
        return $this->org_code;
    }

    /**
     * @param string $org_code
     */
    public function setOrgCode($org_code)
    {
        $this->org_code = $org_code;
    }

    /**
     * tax code, must not be empty when type is enterprise
     * @return string
     */
    public function getTaxCode()
    {
        return $this->tax_code;
    }

    /**
     * @param string $tax_code
     */
    public function setTaxCode($tax_code)
    {
        $this->tax_code = $tax_code;
    }

    /**
     * linkman name when type is enterprise; personal name when type is personal
     * @return string
     */
    public function getLinkName()
    {
        return $this->link_name;
    }

    /**
     * @param string $link_name
     */
    public function setLinkName($link_name)
    {
        $this->link_name = $link_name;
    }

    /**
     * linkman identity card when type is enterprise; personal identity card when type is personal
     * @return string
     */
    public function getLinkIdCard()
    {
        return $this->link_id_card;
    }

    /**
     * @param string $link_id_card
     */
    public function setLinkIdCard($link_id_card)
    {
        $this->link_id_card = $link_id_card;
    }

    /**
     * linkman phone number when type is enterprise; personal phone number when type is personal
     * @return string
     */
    public function getLinkPhone()
    {
        return $this->link_phone;
    }

    /**
     * @param string $link_phone
     */
    public function setLinkPhone($link_phone)
    {
        $this->link_phone = $link_phone;
    }

    /**
     * linkman email when type is enterprise; personal email when type is personal
     * @return string
     */
    public function getLinkEmail()
    {
        return $this->link_email;
    }

    /**
     * @param string $link_email
     */
    public function setLinkEmail($link_email)
    {
        $this->link_email = $link_email;
    }
}