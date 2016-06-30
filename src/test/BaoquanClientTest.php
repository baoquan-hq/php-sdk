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
use com\baoquan\sdk\util\Utils;
use Faker\Factory;

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

    private $faker;

    protected function setUp()
    {
        $this->client = new BaoquanClient();
        $this->client->setHost('http://localhost:8080');
        $this->client->setAccessKey('fsBswNzfECKZH9aWyh47fc');
        $this->client->setPemPath($GLOBALS['test_dir'].'/resources/private_key_encoded.pem');

        $this->faker = Factory::create('zh_CN');
    }

    /**
     * payload.templateId can not be empty
     */
    public function testCreateAttestation0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.templateId can not be empty');
        $this->client->createAttestation([]);
    }

    /**
     * payload.identities can not be empty
     */
    public function testCreateAttestation1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.identities can not be empty');
        $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4'
        ]);
    }

    /**
     * payload.factoids can not be empty
     */
    public function testCreateAttestation2() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.factoids can not be empty');
        $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ]
        ]);
    }

    /**
     * template should be exist
     */
    public function testCreateAttestation3() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('模板不存在');
        $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJ',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ]
        ]);
    }

    /**
     * factoid data should meet with template schema
     * when you edit template schemas on line and set user.phone_number is required
     * you must give a valid phone_number value in user factoid
     */
    public function testCreateAttestation4() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ]
        ]);
    }

    /**
     * factoid data type should be in template schemas
     */
    public function testCreateAttestation5() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid factoid type: product corresponding schema not exist');
        $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ],
            'completed'=>false
        ]);
    }

    /**
     * factoid data should meet with template schema
     * when user.phone_number is required but you only upload product
     * you must call addFactoids api to upload user later
     */
    public function testCreateAttestation6() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $this->client->createAttestation([
            'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ]
        ]);
    }

    /**
     * payload.ano can not be empty
     */
    public function testAddFactoids0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.ano can not be empty');
        $this->client->addFactoids([]);
    }

    /**
     * payload.factoids can not be empty
     */
    public function testAddFactoids1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.factoids can not be empty');
        $this->client->addFactoids([
            'ano'=>'D58FFFD28A8949969611883B6EABA148'
        ]);
    }

    /**
     * attestation must be exist
     */
    public function testAddFactoids2() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('保全不存在');
        $this->client->addFactoids([
            'ano'=>'D58FFFD28A8949',
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ]
        ]);
    }

    /**
     * attestation completed and can not add factoids
     */
    public function testAddFactoids3() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('保全已完成,不能继续追加陈述');
        $this->client->addFactoids([
            'ano'=>'4E6457A5A9B94FBFB64E0D08BDFA2BD4',
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ]
        ]);
    }

    /**
     * when complete attestation, factoids should meet with schemas
     */
    public function testAddFactoids4() {
        $response = $this->client->createAttestation([
            'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ],
            'completed'=>false
        ]);
        $no = $response['data']['no'];
        $this->assertNotEmpty($no);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('invalid data : user.phone_number required');
        $this->client->addFactoids([
            'ano'=>$no,
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ],
            'completed'=>true
        ]);
    }

    /**
     * create attestation and then add factoid
     */
    public function testAddFactoids5() {
        $response = $this->client->createAttestation([
            'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台',
                    ]
                ]
            ],
            'completed'=>false
        ]);
        $no = $response['data']['no'];
        $this->assertNotEmpty($no);

        $response = $this->client->addFactoids([
            'ano'=>$no,
            'factoids'=>[
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ],
            'completed'=>true
        ]);
        $this->assertTrue($response['data']['success']);
    }

    /**
     * payload.type can not be null
     */
    public function testApplyCa0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.type can not be null');
        $this->client->applyCa([]);
    }

    /**
     * payload.linkName can not be empty
     */
    public function testApplyCa1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.link_name can not be empty');
        $this->client->applyCa([
            'type'=>'PERSONAL'
        ]);
    }

    /**
     * payload.name can not be empty when type is enterprise
     */
    public function testApplyCa2() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.name can not be empty');
        $this->client->applyCa([
            'type'=>'ENTERPRISE'
        ]);
    }

    /**
     * seal can not be null when ca type is enterprise
     */
    public function testApplyCa3() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('seal can not be null when ca type is enterprise');
        $this->client->applyCa([
            'type'=>'ENTERPRISE',
            'name'=>'浙金网',
            'ic_code'=>'91330105311263043J',
            'org_code'=>'311263043',
            'tax_code'=>'330105311263043',
            'link_name'=>$this->faker->name,
            'link_id_card'=>Utils::randomIdCard(),
            'link_phone'=>$this->faker->phoneNumber,
            'link_email'=>$this->faker->email,
        ]);
    }

    /**
     * apply personal ca
     */
    public function testApplyCa4() {
        $response = $this->client->applyCa([
            'type'=>'PERSONAL',
            'link_name'=>$this->faker->name,
            'link_id_card'=>Utils::randomIdCard(),
            'link_phone'=>$this->faker->phoneNumber,
            'link_email'=>$this->faker->email,
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }

    /**
     * apply enterprise ca
     */
    public function testApplyCa5() {
        $response = $this->client->applyCa([
            'type'=>'ENTERPRISE',
            'name'=>'浙金网',
            'ic_code'=>'91330105311263043J',
            'org_code'=>'311263043',
            'tax_code'=>'330105311263043',
            'link_name'=>$this->faker->name,
            'link_id_card'=>Utils::randomIdCard(),
            'link_phone'=>$this->faker->phoneNumber,
            'link_email'=>$this->faker->email,
        ], [
            'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
            'resource_name'=>'seal.png'
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }

    /**
     * add one factoid with one attachment that need to sign
     */
    public function testSign0() {
        $response = $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ],
            'signs'=>[
                0=>[
                    0=>[
                        'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
                        '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
                    ]
                ]
            ],
            'completed'=>true
        ], [
            0=>[
                [
                    'resource'=>fopen(__DIR__.'/resources/contract.pdf', 'r'),
                    'resource_name'=>'contract.pdf'
                ]
            ]
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }

    /**
     * add one factoid with two attachments and one of them need to sign
     */
    public function testSign1() {
        $response = $this->client->createAttestation([
            'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ],
            'signs'=>[
                0=>[
                    0=>[
                        'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
                        '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
                    ]
                ]
            ],
            'completed'=>true
        ], [
            0=>[
                [
                    'resource'=>fopen(__DIR__.'/resources/contract.pdf', 'r'),
                    'resource_name'=>'contract.pdf'
                ],
                [
                    'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
                    'resource_name'=>'seal.png'
                ]
            ]
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }

    /**
     * add two factoids with attachments and one of the factoids has attachment need to sign
     */
    public function testSign2() {
        $response = $this->client->createAttestation([
            'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台'
                    ]
                ],
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ],
            'signs'=>[
                1=>[
                    1=>[
                        'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
                        '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
                    ]
                ]
            ],
            'completed'=>true
        ], [
            0=>[
                [
                    'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
                    'resource_name'=>'seal.png'
                ]
            ],
            1=>[
                [
                    'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
                    'resource_name'=>'seal.png'
                ],
                [
                    'resource'=>fopen(__DIR__.'/resources/contract.pdf', 'r'),
                    'resource_name'=>'contract.pdf'
                ]
            ]
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }

    /**
     * add two factoids with attachments and one of the factoids has attachment need to sign
     */
    public function testSign3() {
        $response = $this->client->createAttestation([
            'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            'factoids'=>[
                [
                    'type'=>'product',
                    'data'=>[
                        'name'=>'浙金网',
                        'description'=>'p2g理财平台'
                    ]
                ],
                [
                    'type'=>'user',
                    'data'=>[
                        'name'=>'张三',
                        'phone_number'=>'13234568732',
                        'registered_at'=>'1466674609',
                        'username'=>'tom'
                    ]
                ]
            ],
            'signs'=>[
                1=>[
                    1=>[
                        'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
                        '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
                    ]
                ]
            ],
            'completed'=>true
        ], [
            1=>[
                [
                    'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
                    'resource_name'=>'seal.png'
                ],
                [
                    'resource'=>fopen(__DIR__.'/resources/contract.pdf', 'r'),
                    'resource_name'=>'contract.pdf'
                ]
            ]
        ]);
        $this->assertNotEmpty($response['data']['no']);
    }
}