# Baoquan.com API SDK

Welcome to use Baoquan.com API SDK.

## Create Baoquan Client

```php
$client = new BaoquanClient();
$client->setHost('https://baoquan.com');
$client->setAccessKey('fsBswNzfECKZH9aWyh47fc'); // replace it with your access key
$client->setPemPath('path/to/rsa_private.pem');
```

## Create attestation

### Create attestation without sign

```php
try {
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
       ]
    ],
    'completed'=>false
  ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Create attestation with sign

```php
try {
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
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Create attestation with hash

```php
try {
    $response = $client->createAttestationHash([
            // 设置保全唯一码
            'unique_id'=>'5bf54bc4-ec69-4a5d-b6e4-a3f670f795f4',
            // 设置模板id
            'template_id'=>'v137Mok8WPreDoorWcuZaH',
            // 设置保全所有者的身份标识
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            // 陈述对象列表
            'factoids'=>[
                // user陈述
                [
                    'unique_id'=>'c83d838e-3844-4689-addf-ca0f01171e7c',
                    'type'=>'file',

                    'data'=>[
                        'file_name'=>'123.txt'
                    ]
                ]
            ],
            // 设置陈述是否上传完成，如果设置成true，则后续不能继续追加陈述
            'completed'=>true
            ]
            ,"064eb22a4e3bff9f478eb94b87b5b2cb65063de6a3585d44903e814c0aaf1356"
    );
    echo $response['data']['no'];
} catch (ServerException $e) {
    echo $e->getMessage();
}
```

### Create attestation with url###

```php
try {
    $response = $client->createAttestationURL([
            // 设置保全唯一码
            'unique_id'=>'5bf54bc4-ec69-4a5d-b6e4-a3f670f795f5',
            // 设置模板id
            'template_id'=>'uiqAvzh5uLKYBd4Jp9Upr1',
            // 设置保全所有者的身份标识
            'identities'=>[
                'ID'=>'42012319800127691X',
                'MO'=>'15857112383',
            ],
            // 陈述对象列表
            'factoids'=>[
                // user陈述
                [
                    'unique_id'=>'c83d838e-3844-4689-addf-ca0f01171e7c',
                    'type'=>'content',

                    'data'=>[
                        'url'=>'$url'
                    ]
                ]
            ],
            // 设置陈述是否上传完成，如果设置成true，则后续不能继续追加陈述
            'completed'=>true
            ]
            ,"http://www.baidu.com/"
    );
    echo $response['data']['no'];
} catch (ServerException $e) {
    echo $e->getMessage();
}
```



## Add factoid

```php
try {
  $response = $this->client->addFactoids([
     'ano'=>'7F189BBB5FA1451EA8601D0693E36FE7',
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
  echo $response['data']['success'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Get attestation data

```php
try {
  $response = $client->getAttestation('DB0C8DB14E3C44C7B9FBBE30EB179241', ['factoids']);
  var_dump($response['data']);
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

The second param value of getAttestation can be null, empty array, array of field "identities", "factoids", "attachments"
if null, the response contains all filed value
if empty array, the response contains all filed value except for field "identities", "factoids", "attachments"
if array of one or more value in "identities", "factoids", "attachments", the response contains the respond field value
if you just want to get the block chain hash you should set null of the second param, because server need more time to connect database, decrypt data when you want to get "identities", "factoids", "attachments"

## Download attestation

```php
try {
  $response = $client->downloadAttestation('DB0C8DB14E3C44C7B9FBBE30EB179241');
  $file = fopen($response['file_name'], 'w');
  // $response['file'] is a \Psr\Http\Message\StreamInterface object
  fwrite($file, $response['file']->getContents());
  fclose($file);
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Apply Ca

### Apply personal Ca

```php
try {
  $response = $this->client->applyCa([
     'type'=>'PERSONAL',
     'link_name'=>'张三',
     'link_id_card'='432982198405237845',
     'link_phone'=>'13578674532',
     'link_email'=>'13578674532@qq.com',
    ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Apply enterprise Ca

```php
try {
  $response = $this->client->applyCa([
    'type'=>'ENTERPRISE',
    'name'=>'浙金网',
    'ic_code'=>'91330105311263043J',
    'org_code'=>'311263043',
    'tax_code'=>'330105311263043',
    'link_name'=>'张三',
    'link_id_card'='432982198405237845',
    'link_phone'=>'13578674532',
    'link_email'=>'13578674532@qq.com',
    ], [
     'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
     'resource_name'=>'seal.png'
    ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

You can look at the unit test for more examples.