<?php
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 8 2020
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Controllers;
use MikoPBX\Common\Models\Extensions;
use MikoPBX\Common\Models\Sip;
use MikoPBX\Core\System\Network;
use MikoPBX\Modules\PbxExtensionUtils;
use MikoPBX\PBXCoreREST\Controllers\Modules\ModulesControllerBase;
use Modules\ModuleAutoprovision\Lib\Transliterate;
use Modules\ModuleAutoprovision\Models\OtherPBX;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;
use Modules\ModuleAutoprovision\Models\TemplatesUsers;
use GuzzleHttp\Client;
use Modules\ModuleUsersGroups\Models\GroupMembers;
use Modules\ModuleUsersGroups\Models\UsersGroups;
use Phalcon\Text;

class GetController extends ModulesControllerBase
{
    /**
     * https://standards-oui.ieee.org/oui/oui.txt
     */
    public const PHONES_MAC = [
        '000413' => 'SNOM',
        '44DBD2' => 'YEALINK',
        '0C383E' => 'FANVIL',
    ];

    /**
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/1/2/3'
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/phonebook'
     */
    public function getConfigStatic(...$args):void
    {
        $uri = substr('/pbxcore'.$_REQUEST['_url'],  strlen(AutoprovisionConf::BASE_URI));
        if($uri === '/phonebook'){
            $this->echoPhoneBook($uri);
            $this->response->sendRaw();
            return;
        }
        $manager = $this->di->get('modelsManager');
        $parameters = [
            'models'     => [
                'TemplatesUri' => TemplatesUri::class,
            ],
            'conditions' => ':uri: LIKE TemplatesUri.uri',
            'bind' => [
                'uri'  => $uri,
            ],
            'columns'    => [
                'id'            => 'Templates.id',
                'template'      => 'Templates.template',
                'name'          => 'Templates.name',
                'uri'           => 'TemplatesUri.uri',
            ],
            'joins'      => [
                'TemplatesUri' => [
                    0 => Templates::class,
                    1 => 'Templates.id = TemplatesUri.templateId',
                    2 => 'Templates',
                    3 => 'LEFT',
                ],
            ],
        ];
        $result = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
        if(!empty($result)){
            $this->response->setHeader('Content-Description', "config file");
            $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri));
            $this->response->setHeader('Content-type', "text/plain");
            $this->response->setHeader('Content-Transfer-Encoding', "binary");
            $this->response->sendHeaders();
            echo $result[0]['template'];
            $this->response->sendRaw();
            return;
        }
        $pattern = '/([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})/i';
        if (preg_match_all($pattern, $_REQUEST['_url'], $matches)) {
            $parameters = [
                'models'     => [
                    'TemplatesUsers' => TemplatesUsers::class,
                ],
                'conditions' => ':mac: LIKE TemplatesUsers.mac',
                'bind' => [
                    'mac'  => $matches[0][0]??'',
                ],
                'columns'    => [
                    'id'            => 'TemplatesUsers.id',
                    'template'      => 'Templates.template',
                    'userId'        => 'TemplatesUsers.userId',
                ],
                'limit' => 1,
                'joins'      => [
                    'TemplatesUsers' => [
                        0 => Templates::class,
                        1 => 'Templates.id = TemplatesUsers.templateId',
                        2 => 'Templates',
                        3 => 'LEFT',
                    ],
                ],
            ];
            $result = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
            if(!empty($result)) {
                $parameters = [
                    'models'     => [
                        'Extensions' => Extensions::class,
                    ],
                    'conditions' => ':userid: = Extensions.userid',
                    'bind' => [
                        'userid'  => $result[0]['userId'],
                    ],
                    'columns'    => [
                        '{SIP_USER_NAME}'     => 'Extensions.callerid',
                        '{SIP_NUM}'           => 'Extensions.number',
                        '{SIP_PASS}'          => 'Sip.secret',
                    ],
                    'ORDER' => 'mac DESC',
                    'limit' => 1,
                    'joins'      => [
                        'Extensions' => [
                            0 => Sip::class,
                            1 => 'Extensions.number = Sip.extension',
                            2 => 'Sip',
                            3 => 'LEFT',
                        ],
                    ],
                ];
                $resultSip = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
                $data = [ '{SIP_NUM}' => '', '{SIP_USER_NAME}' => '', '{SIP_PASS}' => ''];
                if(!empty($resultSip)){
                    $data = $resultSip[0];
                }
                $config = str_replace(array_keys($data), array_values($data), $result[0]['template']);
                $this->response->setHeader('Content-Description', "config file");
                $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri));
                $this->response->setHeader('Content-type', "text/plain");
                $this->response->setHeader('Content-Transfer-Encoding', "binary");
                $this->response->sendHeaders();
                echo $config;
            }
        }
        $this->response->sendRaw();
    }

    private function echoPhoneBook($uri):void
    {
        $phoneBook = '';
        $nameBook = $_REQUEST['name']??'';
        if(empty($nameBook)){
            ['hostname' => $nameBook] = Network::getHostName();
            $otherPbx = OtherPBX::find()->toArray();
            $client = new Client();
            foreach ($otherPbx as $pbx){
                $address = trim($pbx['address']);
                if($address === '127.0.0.1'){
                    $nameBook = $pbx['name'];
                    continue;
                }
                try {
                    $response = $client->request('GET', "http://$address".AutoprovisionConf::BASE_URI.$uri."?name={$pbx['name']}", ['timeout' => 1, 'connect_timeout' => 1, 'read_timeout' => 1]);;
                    $code = $response->getStatusCode();
                }catch (\Exception $e){
                    $code = 0;
                }
                if ($code === 200) {
                    $xmlContent = $response->getBody()->getContents();
                    $isXmlValid = simplexml_load_string("<test>$xmlContent</test>");
                    if ($isXmlValid !== false) {
                        $phoneBook.= $xmlContent.PHP_EOL;
                    }
                }
            }
            unset($otherPbx);
        }

        $bookUsers = [];
        $tmpPhoneBookArray = [$nameBook=> ''];
        if(class_exists('\Modules\ModuleUsersGroups\Models\UsersGroups')
            && PbxExtensionUtils::isEnabled('ModuleUsersGroups')){
            $manager = $this->di->get('modelsManager');
            $parameters = [
                'models'     => [
                    'GroupMembers' => GroupMembers::class,
                ],
                'columns'    => [
                    'user_id'   => 'GroupMembers.user_id',
                    'name'      => 'UsersGroups.name',
                ],
                'joins'      => [
                    'GroupMembers' => [
                        0 => UsersGroups::class,
                        1 => 'UsersGroups.id = GroupMembers.group_id',
                        2 => 'UsersGroups',
                        3 => 'LEFT',
                    ],
                ],
            ];
            $groupsData = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
            foreach ($groupsData as $group){
                $bookUsers[$group['user_id']] = $this->camelize($group['name']);
                $tmpPhoneBookArray[$group['name']] = '';
            }
        }
        $users    = Extensions::find(["type = 'SIP'", 'columns' => ['number', 'callerid', 'userid']])->toArray();
        foreach ($users as $userData) {
            $book = $bookUsers[$userData['userid']]??$nameBook;
            $tmpPhoneBookArray[$book].= "\t".'<DirectoryEntry>'.PHP_EOL;
            $tmpPhoneBookArray[$book].= "\t\t"."<Name>{$userData['callerid']}</Name>".PHP_EOL;
            $tmpPhoneBookArray[$book].= "\t\t"."<Telephone>{$userData['number']}</Telephone>".PHP_EOL;
            $tmpPhoneBookArray[$book].= "\t".'</DirectoryEntry>'.PHP_EOL;
        }
        foreach ($tmpPhoneBookArray as $key => $value){
            if(empty($value)){
                continue;
            }
            $phoneBook.= "<{$key}IPPhoneDirectory>".PHP_EOL;
            $phoneBook.= $value;
            $phoneBook.= "</{$key}IPPhoneDirectory>".PHP_EOL;
        }
        $this->response->setHeader('Content-Description', "config file");
        $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri).'.xml');
        $this->response->setHeader('Content-type', "text/plain");
        $this->response->setHeader('Content-Transfer-Encoding', "binary");
        $this->response->sendHeaders();
        echo $phoneBook;
    }

    private function camelize($inputString){
        $inputString = preg_replace('/\s+/', '-', $inputString);
        $inputString = Transliterate::ruToLat($inputString);
        $inputString = preg_replace('/[^A-Za-z0-9-]/u', '', $inputString);
        return Text::camelize($inputString);
    }

    /**
     */
    public function getConfig(): void
    {
        $this->callActionForModule('ModuleAutoprovision', 'getProvisionConfig');
        $this->response->sendRaw();
    }

    /**
     */
    public function getImg(): void
    {
        $this->callActionForModule('ModuleAutoprovision', 'getImgFile');
        $this->response->sendRaw();
    }
}