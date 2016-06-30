<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 9:49 AM
 */

namespace com\baoquan\sdk;


use com\baoquan\sdk\exception\ClientException;
use com\baoquan\sdk\exception\ServerException;
use com\baoquan\sdk\util\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BaoquanClient
{
    private $host = 'https://baoquan.com';

    private $version = 'v1';

    private $access_key;

    private $request_id_generator;

    private $pem_path;

    private $private_key_data;

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @param string $access_key
     */
    public function setAccessKey($access_key)
    {
        $this->access_key = $access_key;
    }

    /**
     * @return RequestIdGenerator
     */
    public function getRequestIdGenerator()
    {
        return $this->request_id_generator;
    }

    /**
     * @param RequestIdGenerator $request_id_generator
     */
    public function setRequestIdGenerator($request_id_generator)
    {
        if (is_null($request_id_generator)) {
            throw new \InvalidArgumentException('requestIdGenerator can not be null');
        }
        $this->request_id_generator = $request_id_generator;
    }

    /**
     * @return string
     */
    public function getPemPath()
    {
        return $this->pem_path;
    }

    /**
     * @param string $pem_path
     */
    public function setPemPath($pem_path)
    {
        if (empty($pem_path)) {
            throw new \InvalidArgumentException('pem path can not be empty');
        }
        $key_data = file_get_contents($pem_path);
        if ($key_data === false) {
            throw new ClientException('load private key failed, key file path may be wrong');
        }
        $this->pem_path = $pem_path;
        $this->private_key_data = $key_data;
    }

    function __construct()
    {
        $this->request_id_generator = new DefaultRequestIdGenerator();
    }

    /**
     * create attestation with attachments, one factoid can have more than one attachments
     * @param array $payload
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    public function createAttestation($payload, $attachments = null) {
        $this->checkCreateAttestationPayload($payload);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        $payload['attachments'] = $this->buildChecksum($payload, $attachments);
        return $this->post('attestations', $payload, $stream_body_map);
    }

    /**
     * add factoids to attestation with attachments, one factoid can have more than one attachments
     * @param array $payload
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    public function addFactoids($payload, $attachments = null) {
        $this->checkAddFactoidsPayload($payload);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        $payload['attachments'] = $this->buildChecksum($payload, $attachments);
        return $this->post('factoids', $payload, $stream_body_map);
    }

    /**
     * apply ca
     * @param array $payload
     * @param array $seal
     * @throws ServerException
     * @return array
     */
    public function applyCa($payload, $seal = null) {
        $this->checkApplyCaPayload($payload);
        if ($payload['type'] == 'ENTERPRISE') {
            $this->checkSeal($seal);
        }
        $stream_body_map = null;
        if ($seal != null) {
            $stream_body_map['seal'] = [$seal];
        }
        return $this->post('cas', $payload, $stream_body_map);
    }

    /**
     * @param array $payload
     */
    private function checkCreateAttestationPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['template_id'])) {
            throw new \InvalidArgumentException('payload.templateId can not be empty');
        }
        if (!is_array($payload['identities']) || empty($payload['identities'])) {
            throw new \InvalidArgumentException('payload.identities can not be empty');
        }
        if (!is_array($payload['factoids']) || empty($payload['factoids'])) {
            throw new \InvalidArgumentException('payload.factoids can not be empty');
        }
    }

    /**
     * @param array $payload
     */
    private function checkAddFactoidsPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['ano'])) {
            throw new \InvalidArgumentException('payload.ano can not be empty');
        }
        if (empty($payload['factoids'])) {
            throw new \InvalidArgumentException('payload.factoids can not be empty');
        }
    }

    /**
     * @param array $payload
     */
    private function checkApplyCaPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['type'])) {
            throw new \InvalidArgumentException('payload.type can not be null');
        }
        if ($payload['type'] == 'ENTERPRISE') {
            if (empty($payload['name'])) {
                throw new \InvalidArgumentException('payload.name can not be empty');
            }
            if (empty($payload['ic_code'])) {
                throw new \InvalidArgumentException('payload.ic_code can not be empty');
            }
            if (empty($payload['org_code'])) {
                throw new \InvalidArgumentException('payload.org_code can not be empty');
            }
            if (empty($payload['tax_code'])) {
                throw new \InvalidArgumentException('payload.tax_code can not be empty');
            }
        }
        if (empty($payload['link_name'])) {
            throw new \InvalidArgumentException('payload.link_name can not be empty');
        }
        if (empty($payload['link_id_card'])) {
            throw new \InvalidArgumentException('payload.link_id_card can not be empty');
        }
        if (empty($payload['link_phone'])) {
            throw new \InvalidArgumentException('payload.link_phone can not be empty');
        }
        if (empty($payload['link_email'])) {
            throw new \InvalidArgumentException('payload.link_email can not be empty');
        }
    }

    /**
     * @param array $seal
     */
    private function checkSeal($seal) {
        if (!is_array($seal)) {
            throw new \InvalidArgumentException('seal can not be null when ca type is enterprise');
        }
        $filename = $seal['resource_name'];
        if (!is_string($filename) || strpos($filename, '.') === false) {
            throw new \InvalidArgumentException('seal file name must be like xxx.png or xxx.jpg');
        }
        $file_type = substr($filename, strpos($filename, '.') + 1);
        if ($file_type != 'jpg' && $file_type != 'png') {
            throw new \InvalidArgumentException('seal file name extension must be png or jpg');
        }
    }

    /**
     * @param array $payload
     * @param array $attachments
     * @return array
     */
    private function buildChecksum(&$payload, $attachments) {
        $payload_attachments = null;
        if (!empty($attachments)) {
            $payload_attachments = new \stdClass();
            $signs = $payload['signs'];
            unset($payload['signs']);
            for($i = 0; $i < count($payload['factoids']); $i++) {
                $i_attachments = isset($attachments[$i]) ? $attachments[$i] : null;
                $i_signs = isset($signs[$i]) ? $signs[$i] : null;
                if (!empty($i_attachments)) {
                    $objects = [];
                    for($j = 0; $j < count($i_attachments); $j++) {
                        $j_attachment = $i_attachments[$j];
                        $checksum = Utils::checksum($j_attachment['resource']);
                        $j_signs = isset($i_signs[$j]) ? $i_signs[$j] : null;
                        if (is_null($j_signs)) {
                            $objects[] = $checksum;
                        } else {
                            $objects[] = [
                                'checksum'=>$checksum,
                                'sign'=>$j_signs
                            ];
                        }
                    }
                    $payload_attachments->{''.$i} = $objects;
                }
            }
        }
        return $payload_attachments;
    }

    /**
     * @param array $attachments
     * @return array
     */
    private function buildStreamBodyMap($attachments) {
        if (is_null($attachments)) {
            return null;
        }
        if (!is_array($attachments)) {
            throw new \InvalidArgumentException('attachments should be array');
        }
        $multipart_files = [];
        if (count($attachments) > 0) {
            foreach($attachments as $i => $file_list) {
                if (!is_array($file_list)) {
                    throw new \InvalidArgumentException(sprintf('attachments[%d] should be array', $i));
                }
                foreach($file_list as $j => $attachment) {
                    if (!is_array($attachment)) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d] should be array', $i, $j));
                    }
                    if (!is_resource($attachment['resource'])) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d].resource should be resource', $i, $j));
                    }
                    if (empty($attachment['resource_name'])) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d].resource_name can not be empty', $i, $j));
                    }
                }
                $multipart_files[sprintf('attachments[%s][]', $i)] = $file_list;
            }
        }
        return $multipart_files;
    }

    /**
     * @param string $api_name
     * @param array $payload
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    private function post($api_name, $payload, $attachments) {
        $path = sprintf('/api/%s/%s', $this->version, $api_name);
        $request_id = $this->request_id_generator->createRequestId();
        if (empty($request_id)) {
            throw new ClientException('request id can not be empty');
        }
        if (empty($this->access_key)) {
            throw new ClientException('accessKey can not be empty');
        }
        $tonce = time();
        $payloadString = json_encode($payload);
        if (is_null($payloadString)) {
            throw new ClientException('convert payload object to json string failed');
        }
        // build the data to sign
        $data = 'POST'.$path.$request_id.$this->access_key.$tonce.$payloadString;
        $signature = Utils::sign($this->private_key_data, $data);
        // build post request
        $base_uri = sprintf('%s/api/%s/', $this->host, $this->version);
        $http_client = new Client([
            'base_uri' => $base_uri,
            'timeout' => 0,
            'http_errors' => false
        ]);
        $multipart = [
            [
                'name'=>'request_id',
                'contents'=>$request_id
            ],
            [
                'name'=>'access_key',
                'contents'=>$this->access_key
            ],
            [
                'name'=>'tonce',
                'contents'=>$tonce
            ],
            [
                'name'=>'payload',
                'contents'=>$payloadString,
                'headers'=>[
                    'charset'=>'utf-8' // avoid chinese garbled
                ]
            ],
            [
                'name'=>'signature',
                'contents'=>$signature
            ],
        ];
        if (!empty($attachments)) {
            foreach($attachments as $name=>$list) {
                foreach($list as $attachment) {
                    $multipart[] = [
                        'name'=>$name,
                        'contents'=>$attachment['resource'],
                        'filename'=>$attachment['resource_name'],
                        'headers'=>[
                            'charset'=>'utf-8' // avoid chinese garbled
                        ]
                    ];
                }
            }
        }
        $http_response = null;
        try {
            $http_response = $http_client->post($api_name, [
                'multipart'=>$multipart
            ]);
        } catch (RequestException $e) {
            throw new ClientException('http post failed, please check your host or network', $e);
        }
        $contents = json_decode($http_response->getBody()->getContents(), true);
        if ($http_response->getStatusCode() != 200) {
            if (is_array($contents) &&
                isset($contents['message']) &&
                isset($contents['timestamp'])) {
                throw new ServerException($request_id, $contents['message'], $contents['timestamp']);
            } else {
                throw new ServerException($request_id, 'Unknown error', time() * 1000);
            }
        } else {
            return $contents;
        }
    }
}