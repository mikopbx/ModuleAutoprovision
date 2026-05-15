<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 8 2020
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Controllers;
use MikoPBX\Common\Models\Extensions;
use MikoPBX\Common\Models\Sip;
use MikoPBX\Common\Models\Users;
use MikoPBX\Core\System\Network;
use MikoPBX\Core\System\SystemMessages;
use MikoPBX\Modules\PbxExtensionUtils;
use MikoPBX\PBXCoreREST\Controllers\Modules\ModulesControllerBase;
use MikoPBX\Common\Library\Text;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository as FirmwareRepository;
use Modules\ModuleAutoprovision\Lib\Transliterate;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;
use Modules\ModuleAutoprovision\Models\OtherPBX;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;
use Modules\ModuleAutoprovision\Models\TemplatesUsers;
use GuzzleHttp\Client;
use Modules\ModuleUsersGroups\Models\GroupMembers;
use Modules\ModuleUsersGroups\Models\UsersGroups;

class GetController extends ModulesControllerBase
{
    private const LOG_TAG = 'autoprovision-http';

    /**
     * https://standards-oui.ieee.org/oui/oui.txt
     */
    public const PHONES_MAC = [
        '000413' => 'SNOM',
        '44DBD2' => 'YEALINK',
        '0C383E' => 'FANVIL',
    ];

    /**
     * Emits one structured access-log line for a phone provisioning request.
     *
     * Centralising this means every code path logs the same fields — client IP
     * first (the question the operator asks is always "which phone hit us?"),
     * then HTTP code, URI, User-Agent and any path-specific context (matched
     * MAC, bytes streamed, template name). The TFTP and PnP workers already
     * log at this resolution; the HTTP channel now matches.
     */
    private function logHttpRequest(int $code, array $extra = [], int $priority = LOG_NOTICE): void
    {
        $clientIp  = (string)$this->request->getClientAddress(true);
        $userAgent = (string)$this->request->getHeader('User-Agent');
        $uri       = (string)($_REQUEST['_url'] ?? '');

        $parts = ["ip={$clientIp}", "code={$code}", "uri={$uri}"];
        foreach ($extra as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $parts[] = "{$k}={$v}";
        }
        $parts[] = 'ua=' . ($userAgent !== '' ? $userAgent : '-');

        SystemMessages::sysLogMsg(self::LOG_TAG, implode(' ', $parts), $priority);
    }

    /**
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/1/2/3'
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/phonebook'
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/yealink'
     * curl 'http://127.0.0.1/pbxcore/api/autoprovision-http/grandstream'
     */
    public function getConfigStatic(...$args):void
    {
        $userAgent = $this->request->getHeader('User-Agent');
        $uri = substr('/pbxcore'.$_REQUEST['_url'],  strlen(AutoprovisionConf::BASE_URI));
        if($uri === '/phonebook' || $uri === '/yealink'){
            $this->echoPhoneBookYealink($uri);
            $this->response->sendRaw();
            $this->logHttpRequest(200, ['kind' => 'phonebook-yealink']);
            // exit() short-circuits the Micro afterExecuteRoute / ResponseMiddleware chain.
            // Without it, the custom Response::send() wraps the streamed body with a
            // trailing {"meta":{"timestamp":..,"hash":..}} envelope, which corrupts the
            // XML / config payload the phone is downloading.
            $this->terminateStreamedResponse();
        }elseif ($uri === '/grandstream'){
            $this->echoPhoneBookGrandStream($uri);
            $this->response->sendRaw();
            $this->logHttpRequest(200, ['kind' => 'phonebook-grandstream']);
            $this->terminateStreamedResponse();
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
            'order' => 'TemplatesUri.uri DESC',
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
            // Substitute the vendor-agnostic placeholders before streaming the
            // template. The original code echoed the raw template body, which left
            // any `{FIRMWARE_URL}` / `{PBX_HOST}` token visible to the phone as a
            // literal string and broke firmware-URL emission for URIs that lived
            // outside the per-MAC branch below. Per-user placeholders ({SIP_*}) are
            // not in scope here — URI templates are shared across phones and we
            // intentionally leave them literal so the operator notices when a
            // sip-bearing template is wired to a URI route by mistake. We also log
            // a warning when those tokens slip through so the situation is visible
            // without staring at packet captures.
            $template = (string)$result[0]['template'];

            $vendor   = $this->detectVendorFromUserAgent((string)$userAgent);
            $settings = ModuleAutoprovision::findFirst();
            $genericReplacements = [
                '{FIRMWARE_URL}' => FirmwareRepository::resolveFirmwareUrl($vendor, null),
                '{PBX_HOST}'     => $settings !== null ? (string)($settings->pbx_host ?? '') : '',
            ];
            $rendered = strtr($template, $genericReplacements);
            if (preg_match('/\{SIP_(USER_NAME|NUM|PASS)\}/', $rendered) === 1) {
                $this->logHttpRequest(
                    200,
                    [
                        'kind'     => 'uri-template',
                        'template' => $result[0]['name'] ?? '',
                        'warn'     => 'unresolved-sip-placeholder',
                    ],
                    LOG_WARNING
                );
            }

            $this->response->setHeader('Content-Description', "config file");
            $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri));
            $this->response->setHeader('Content-type', "text/plain");
            $this->response->setHeader('Content-Transfer-Encoding', "binary");
            $this->response->sendHeaders();
            echo $rendered;
            $this->response->sendRaw();
            $this->logHttpRequest(200, [
                'kind'     => 'uri-template',
                'template' => $result[0]['name'] ?? '',
                'bytes'    => strlen($rendered),
            ]);
            $this->terminateStreamedResponse();
        }
        $pattern = '/([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})/i';
        if (preg_match_all($pattern, $_REQUEST['_url'], $matches)) {
            $mac = $matches[0][0]??'';
            if(stripos($userAgent, 'yealink') !== false && stripos($uri, '.boot')!==false){
                $this->logHttpRequest(404, ['kind' => 'yealink-boot-ignored', 'mac' => $mac]);
                $this->response->setStatusCode(404, 'Ignore boot config yealink');
                $this->response->sendRaw();
                $this->terminateStreamedResponse();
            }

            $parameters = [
                'models'     => [
                    'TemplatesUsers' => TemplatesUsers::class,
                ],
                'conditions' => ':mac: LIKE TemplatesUsers.mac',
                'bind' => [
                    'mac'  => $mac,
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

                // Firmware URL placeholder. Vendor is sniffed from the User-Agent
                // (every supported phone family includes its brand there) and the
                // exact model comes from the device row keyed by MAC. Missing data
                // resolves to empty string, which lets templates wrap the line in
                // a vendor-specific conditional if needed.
                $vendor = $this->detectVendorFromUserAgent((string)$userAgent);
                $model  = null;
                if ($mac !== '') {
                    $device = ModuleAutoprovisionDevice::findFirst([
                        'mac = :mac:',
                        'bind' => ['mac' => $mac],
                    ]);
                    if ($device !== null && !empty($device->manufacturer_model)) {
                        $model = $this->extractModel((string)$device->manufacturer_model);
                    }
                }
                $data['{FIRMWARE_URL}'] = FirmwareRepository::resolveFirmwareUrl($vendor, $model);

                $config = str_replace(array_keys($data), array_values($data), $result[0]['template']);
                $this->response->setHeader('Content-Description', "config file");
                $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri));
                $this->response->setHeader('Content-type', "text/plain");
                $this->response->setHeader('Content-Transfer-Encoding', "binary");
                $this->response->sendHeaders();
                echo $config;
                $this->logHttpRequest(200, [
                    'kind'   => 'per-mac',
                    'mac'    => $mac,
                    'vendor' => $vendor !== null ? (string)$vendor : '',
                    'model'  => $model ?? '',
                    'bytes'  => strlen($config),
                ]);
                $this->terminateStreamedResponse();
            }
        }

        $this->logHttpRequest(404, ['mac' => $mac ?? '']);
        $this->response->setStatusCode(404, 'Not found');
        $this->response->sendRaw();
        $this->terminateStreamedResponse();
    }

    /**
     * Stops further response processing after a streaming endpoint has written its
     * body. Without this, the Micro afterExecuteRoute → ResponseMiddleware chain
     * still calls our custom Response::send(), which json_encodes the (empty)
     * content buffer and appends a `{"meta":{"timestamp":..,"hash":..}}` footer.
     * That footer corrupts the strict-format payload a phone is parsing.
     */
    private function terminateStreamedResponse(): void
    {
        // Flush PHP's output buffers so anything still queued lands on the wire
        // before we tear down. FPM cleans up the worker normally after exit.
        while (ob_get_level() > 0 && @ob_end_flush()) {
            // noop — drain until empty or ob_end_flush refuses.
        }
        flush();
        exit;
    }

    private function echoPhoneBookGrandStream($uri):void
    {
        $bookUsers = [];
        $phoneBook = "<?xml version='1.0' encoding='UTF-8'?>".PHP_EOL;
        $phoneBook.= "<AddressBook>".PHP_EOL;
        $manager = $this->di->get('modelsManager');
        if(class_exists('\Modules\ModuleUsersGroups\Models\UsersGroups')
            && PbxExtensionUtils::isEnabled('ModuleUsersGroups')){
            $parameters = [
                'models'     => [
                    'GroupMembers' => GroupMembers::class,
                ],
                'columns'    => [
                    'user_id'   => 'GroupMembers.user_id',
                    'name'      => 'UsersGroups.name',
                    'group_id'  => 'UsersGroups.id',
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
                $bookUsers[$group['user_id']] = $group['group_id'];

                $idText = '<id>'.$group['group_id'].'</id>';
                if(stripos($phoneBook,$idText) !== false){
                    continue;
                }
                $phoneBook.= '<pbgroup>'.PHP_EOL.
                             "\t".$idText.PHP_EOL.
                             "\t".'<name>'.trim($group['name']).'</name>'.PHP_EOL.
                             '</pbgroup>'.PHP_EOL;
            }
        }

        $parameters = [
            'models'     => [
                'Extensions' => Extensions::class,
            ],
            'conditions' => "type = 'SIP'",
            'columns'    => [
                'number'   => 'Extensions.number',
                'callerid' => 'Extensions.callerid',
                'username' => 'Users.username',
                'user_id' => 'Users.id',
            ],
            'joins'      => [
                'Extensions' => [
                    0 => Users::class,
                    1 => 'Extensions.userid = Users.id',
                    2 => 'Users',
                    3 => 'LEFT',
                ],
            ],
        ];
        $users = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
        foreach ($users as $userData) {
            $groupId = $bookUsers[$userData['user_id']]??'';
            $phoneBook.='<Contact>'.PHP_EOL.
                        "\t".'<id>'.$userData['user_id'].'</id>'.PHP_EOL.
                        "\t".'<FirstName>'.trim($userData['username']).'</FirstName>'.PHP_EOL.
                        "\t".'<Phone type="Work">'.PHP_EOL.
                        "\t\t".'<phonenumber>'.$userData['number'].'</phonenumber>'.PHP_EOL.
                        "\t\t".'<accountindex>0</accountindex>'.PHP_EOL.
                        "\t".'</Phone>'.PHP_EOL;

            if(!empty($groupId)){
                $phoneBook.="\t".'<Group>'.$groupId.'</Group>'.PHP_EOL;
            }
            $phoneBook.='</Contact>'.PHP_EOL;
        }
        $phoneBook.= "</AddressBook>".PHP_EOL;

        $this->response->setHeader('Content-Description', "config file");
        $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri).'.xml');
        $this->response->setHeader('Content-type', "text/plain");
        $this->response->setHeader('Content-Transfer-Encoding', "binary");
        $this->response->sendHeaders();
        echo $phoneBook;

    }
    private function echoPhoneBookYealink($uri):void
    {
        $phoneBook = "";
        $nameBook = $_REQUEST['name']??'';
        if(empty($nameBook)){
            $phoneBook.= "<?xml version='1.0' encoding='UTF-8'?>".PHP_EOL;
            // The original code substituted the container/host hostname directly
            // into the XML root tag — producing nonsense like
            // <a4706e46d81dIPPhoneDirectory> on a Docker host. Yealink's spec only
            // recognises the literal "YealinkIPPhoneDirectory" envelope, so we
            // hardcode "Yealink" here for the default-namespace branch.
            $nameBook = 'Yealink';
            $otherPbx = OtherPBX::find()->toArray();
            $client = new Client();
            foreach ($otherPbx as $pbx){
                $address = trim($pbx['address']);
                if($address === '127.0.0.1'){
                    $nameBook = $pbx['name'];
                    continue;
                }
                try {
                    $response = $client->request('GET', "http://$address".AutoprovisionConf::BASE_URI.$uri."?name={$pbx['name']}", ['timeout' => 1, 'connect_timeout' => 1, 'read_timeout' => 1]);
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
                }else{
                    SystemMessages::sysLogMsg(self::LOG_TAG, "Fail get phonebook from $address, code: $code", LOG_WARNING);
                }
            }
            unset($otherPbx);
        }

        $bookUsers = [];
        $tmpPhoneBookArray = [$nameBook=> ''];
        $manager = $this->di->get('modelsManager');
        if(class_exists('\Modules\ModuleUsersGroups\Models\UsersGroups')
            && PbxExtensionUtils::isEnabled('ModuleUsersGroups')){
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


        $parameters = [
            'models'     => [
                'Extensions' => Extensions::class,
            ],
            'conditions' => "type = 'SIP'",
            'columns'    => [
                'number'   => 'Extensions.number',
                'callerid' => 'Extensions.callerid',
                'username' => 'Users.username',
            ],
            'joins'      => [
                'Extensions' => [
                    0 => Users::class,
                    1 => 'Extensions.userid = Users.id',
                    2 => 'Users',
                    3 => 'LEFT',
                ],
            ],
        ];
        $users = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
        // $users    = Extensions::find(["type = 'SIP'", 'columns' => ['number', 'callerid', 'userid']])->toArray();
        foreach ($users as $userData) {
            $book = $bookUsers[$userData['userid']]??$nameBook;
            $tmpPhoneBookArray[$book].= "\t".'<DirectoryEntry>'.PHP_EOL;
            $tmpPhoneBookArray[$book].= "\t\t"."<Name>{$userData['username']}</Name>".PHP_EOL;
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

    /**
     * Best-effort vendor key derived from the User-Agent string sent by the phone.
     *
     * Every supported phone family identifies itself in User-Agent (Yealink, Snom,
     * Fanvil, Grandstream, Htek). Returns '' if no known brand matches — callers
     * then resolve {FIRMWARE_URL} to an empty string instead of guessing.
     */
    private function detectVendorFromUserAgent(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        foreach (['yealink', 'snom', 'fanvil', 'grandstream', 'htek'] as $vendor) {
            if (str_contains($ua, $vendor)) {
                return $vendor;
            }
        }
        return '';
    }

    /**
     * Extracts the model name from ModuleAutoprovisionDevice.manufacturer_model.
     *
     * PnP populates the field as "Vendor / Model" (e.g. "Fanvil / X3SP V2") and
     * some models contain spaces, so we can't just take the last whitespace token
     * — that would shrink "X3SP V2" to "V2". When a "/" separator is present we
     * take everything after it; otherwise we drop a leading vendor word that
     * matches one of the known vendors and keep the rest. Returns null when the
     * field is empty.
     */
    private function extractModel(string $manufacturerModel): ?string
    {
        $raw = trim($manufacturerModel);
        if ($raw === '') {
            return null;
        }

        $slash = strpos($raw, '/');
        if ($slash !== false) {
            $tail = trim(substr($raw, $slash + 1));
            return $tail === '' ? null : $tail;
        }

        // No separator: strip a leading known-vendor token if present.
        $parts = preg_split('/\s+/', $raw) ?: [];
        if ($parts !== [] && in_array(strtolower($parts[0]), ['yealink', 'snom', 'fanvil', 'grandstream', 'htek'], true)) {
            array_shift($parts);
        }
        $tail = implode(' ', $parts);
        $tail = trim($tail);
        return $tail === '' ? null : $tail;
    }

    private function camelize(string $inputString): string
    {
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