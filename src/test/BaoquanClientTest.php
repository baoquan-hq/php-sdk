<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 3:53 PM
 */

namespace com\baoquan\sdk\test;

use com\baoquan\sdk\BaoquanClient;
use com\baoquan\sdk\exception\ServerException;
use com\baoquan\sdk\pojo\payload\AddFactoidsPayload;
use com\baoquan\sdk\pojo\payload\CreateAttestationPayload;
use com\baoquan\sdk\pojo\payload\IdentityType;

$loader = require_once '../../vendor/autoload.php';
$test_dir = dirname(__FILE__);
$src_dir = dirname($test_dir);
$main_path = $src_dir.'/main';
$test_path = $test_dir;
$loader->addPsr4('com\\baoquan\\sdk\\',[
    $main_path, $test_path
]);

class BaoquanClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaoquanClient
     */
    private $client;

    protected function setUp()
    {
        $this->client = new BaoquanClient();
        $this->client->setHost('http://localhost:8080');
        $this->client->setAccessKey('fsBswNzfECKZH9aWyh47fc');
        $this->client->setPemPath($GLOBALS['test_dir']."/resources/private_key.pem");
    }

    /**
     * payload.templateId can not be empty
     */
    public function testCreateAttestation0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.templateId can not be empty');
        $this->client->createAttestation(new CreateAttestationPayload());
    }

    /**
     * payload.identities can not be empty
     */
    public function testCreateAttestation1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.identities can not be empty');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $this->client->createAttestation($payload);
    }

    /**
     * payload.factoids can not be empty
     */
    public function testCreateAttestation2() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.factoids can not be empty');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $payload->setIdentities([
            IdentityType::ID, '42012319800127691X',
            IdentityType::MO, '15857112383',
        ]);
        $this->client->createAttestation($payload);
    }

    /**
     * template should be exist
     */
    public function testCreateAttestation3() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('模板不存在');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJ");
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'user',
                'data'=>[
                    'name'=>'张三',
                    'phone_number'=>'13234568732',
                    'registered_at'=>'2015.06.23',
                    'username'=>'tom'
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->createAttestation($payload);
    }

    /**
     * factoid data should meet with template schema
     * when you edit template schemas on line and set user.phone_number is required
     * you must give a valid phone_number value in user factoid
     */
    public function testCreateAttestation4() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'user',
                'data'=>[
                    'name'=>'张三',
                    'registered_at'=>'2015.06.23',
                    'username'=>'tom'
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->createAttestation($payload);
    }

    /**
     * factoid data type should be in template schemas
     */
    public function testCreateAttestation5() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid factoid type: product corresponding schema not exist');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $payload->setCompleted(false);
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->createAttestation($payload);
    }

    /**
     * factoid data should meet with template schema
     * when user.phone_number is required but you only upload product
     * you must call addFactoids api to upload user later
     */
    public function testCreateAttestation6() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("5Yhus2mVSMnQRXobRJCYgt");
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->createAttestation($payload);
    }

    /**
     * payload.ano can not be empty
     */
    public function testAddFactoids0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.ano can not be empty');
        $this->client->addFactoids(new AddFactoidsPayload());
    }

    /**
     * payload.factoids can not be empty
     */
    public function testAddFactoids1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.factoids can not be empty');
        $payload = new AddFactoidsPayload();
        $payload->setAno('D58FFFD28A8949969611883B6EABA148');
        $this->client->addFactoids($payload);
    }

    /**
     * attestation must be exist
     */
    public function testAddFactoids2() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('保全不存在');
        $payload = new AddFactoidsPayload();
        $payload->setAno('D58FFFD28A8949');
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->addFactoids($payload);
    }

    /**
     * attestation completed and can not add factoids
     */
    public function testAddFactoids3() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('保全已完成,不能继续追加陈述');
        $payload = new AddFactoidsPayload();
        $payload->setAno('4E6457A5A9B94FBFB64E0D08BDFA2BD4');
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->addFactoids($payload);
    }

    /**
     * when complete attestation, factoids should meet with schemas
     */
    public function testAddFactoids4() {
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("5Yhus2mVSMnQRXobRJCYgt");
        $payload->setCompleted(false);
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $response = $this->client->createAttestation($payload);
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getRequestId());
        $this->assertNotEmpty($response->getData());
        $this->assertNotEmpty($response->getData()->getNo());

        $no = $response->getData()->getNo();
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $payload = new AddFactoidsPayload();
        $payload->setAno($no);
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->addFactoids($payload);
    }

    /**
     * create attestation and then add factoid
     */
    public function testAddFactoids5() {
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("5Yhus2mVSMnQRXobRJCYgt");
        $payload->setCompleted(false);
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'product',
                'data'=>[
                    'name'=>'浙金网',
                    'description'=>'p2g理财平台',
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $response = $this->client->createAttestation($payload);
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getRequestId());
        $this->assertNotEmpty($response->getData());
        $this->assertNotEmpty($response->getData()->getNo());

        $no = $response->getData()->getNo();
        $payload = new AddFactoidsPayload();
        $payload->setAno($no);
        $factoids = [
            [
                'type'=>'user',
                'data'=>[
                    'name'=>'张三',
                    'phone_number'=>'13234568732',
                    'registered_at'=>'2015.06.23',
                    'username'=>'tom'
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $response = $this->client->addFactoids($payload);
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getRequestId());
        $this->assertNotEmpty($response->getData());
        $this->assertTrue($response->getData()->getSuccess());
    }


}