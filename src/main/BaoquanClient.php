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
use com\baoquan\sdk\pojo\payload\CreateAttestationPayload;
use com\baoquan\sdk\pojo\response\BaseResponse;
use com\baoquan\sdk\pojo\response\CreateAttestationResponse;
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
        $this->pem_path = $pem_path;
    }

    function __construct()
    {
        $this->request_id_generator = new DefaultRequestIdGenerator();
    }

    /**
     * create attestation with attachments, one factoid can have more than one attachments
     * @param CreateAttestationPayload $payload
     * @param array $attachments
     * @return CreateAttestationResponse $response
     */
    public function createAttestation(CreateAttestationPayload $payload, $attachments = null) {
        $this->checkCreateAttestationPayload($payload);
        $payload_map = $this->buildCreateAttestationPayloadMap($payload, $attachments);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        $response = new CreateAttestationResponse();
        $this->post('attestations', $payload_map, $stream_body_map, $response);
        return $response;
    }

    /**
     * @param CreateAttestationPayload $payload
     * @throws \InvalidArgumentException
     */
    private function checkCreateAttestationPayload(CreateAttestationPayload $payload) {
        if (is_null($payload)) {
            throw new \InvalidArgumentException("payload can not be null");
        }
        if (empty($payload->getTemplateId())) {
            throw new \InvalidArgumentException("payload.templateId can not be empty");
        }
        if (empty($payload->getIdentities())) {
            throw new \InvalidArgumentException("payload.identities can not be empty");
        }
        if (empty($payload->getFactoids())) {
            throw new \InvalidArgumentException("payload.factoids can not be empty");
        }
    }

    /**
     * @param CreateAttestationPayload $payload
     * @param array $attachments
     * @return array
     */
    private function buildCreateAttestationPayloadMap(CreateAttestationPayload $payload, $attachments) {
        $payload_map = [];
        $payload_map['template_id'] = $payload->getTemplateId();
        $payload_map['identities'] = $payload->getIdentities();
        $payload_map['factoids'] = $payload->getFactoids();
        $payload_map['completed'] = $payload->getCompleted();
        $payload_map['attachments'] = $this->buildChecksum($payload, $attachments);
        return $payload_map;
    }

    /**
     * @param CreateAttestationPayload $payload
     * @param array $attachments
     * @return array
     */
    private function buildChecksum(CreateAttestationPayload $payload, $attachments) {
        $payload_attachments = null;
        if (!empty($attachments)) {
            $signs = $payload->getSigns();
            $factoids_count = count($payload->getFactoids());
            for($i = 0; $i < $factoids_count; $i++) {
                $is = "".$i;
                $i_attachments = $attachments[$is];
                $i_signs = $signs[$is];
                if (!empty($i_attachments)) {
                    $objects = [];
                    $i_attachments_count = count($i_attachments);
                    for($j = 0; $j < $i_attachments_count; $j++) {
                        $js = "".$j;
                        $j_attachment = $i_attachments[$j];
                        $checksum = Utils::checksum($j_attachment->getResource());
                        $j_signs = null;
                        if (!is_null($i_signs)) {
                            $j_signs = $i_signs[$js];
                        }
                        if (is_null($j_signs)) {
                            $objects[] = $checksum;
                        } else {
                            $objects[] = [
                                'checksum'=>$checksum,
                                'sign'=>$j_signs
                            ];
                        }
                    }
                    $payload_attachments[$is] = $objects;
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
        $multipart_files = [];
        if (!empty($attachments)) {
            foreach($attachments as $i => $file_list) {
                foreach($file_list as $file) {
                    $multipart_files[sprintf('attachments[%s][]', $i)] = $file;
                }
            }
        }
        return $multipart_files;
    }

    /**
     * @param string $api_name
     * @param array $payload
     * @param array $attachments
     * @param BaseResponse $response
     * @throws ServerException
     */
    private function post($api_name, $payload, $attachments, BaseResponse $response) {
        $path = sprintf("/api/%s/%s", $this->version, $api_name);
        $request_id = $this->request_id_generator->createRequestId();
        if (empty($request_id)) {
            throw new ClientException("request id can not be empty");
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
        $signature = Utils::sign($this->pem_path, $data);
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
            foreach($attachments as $name=>$attachment) {
                $multipart[] = [
                    'name'=>$name,
                    'contents'=>$attachment->getResource(),
                    'filename'=>$attachment->getResourceName(),
                    'headers'=>[
                        'charset'=>'utf-8' // avoid chinese garbled
                    ]
                ];
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
                throw new ServerException($request_id, "Unknown error", time() * 1000);
            }
        } else {
            $response->parse($contents);
        }
    }
}