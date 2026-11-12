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
use MikoPBX\Common\Models\Users;
use MikoPBX\Core\System\Network;
use MikoPBX\Core\System\SystemMessages;
use MikoPBX\Modules\PbxExtensionUtils;
use MikoPBX\PBXCoreREST\Controllers\Modules\ModulesControllerBase;
use MikoPBX\PBXCoreREST\Lib\PbxExtensionsProcessor;
use Modules\ModuleAutoprovision\Lib\Autoprovision;
use Modules\ModuleAutoprovision\Lib\Phonebook\YealinkPhonebook;
use Modules\ModuleAutoprovision\Lib\ProvisionUrl;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;
use Modules\ModuleAutoprovision\Models\OtherPBX;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;
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
        // TemplatesUri.uri is the LIKE pattern, $uri is the value — entries stored
        // without a leading slash (the historical seeded form: `snom-D785.htm`)
        // never matched the request URI Phalcon hands us (`/snom-D785.htm`).
        // Try the request URI first, then with the leading slash stripped, so
        // both stored forms work.
        $uriCandidates = [$uri];
        $stripped = ltrim($uri, '/');
        if ($stripped !== '' && $stripped !== $uri) {
            $uriCandidates[] = $stripped;
        }
        $result = [];
        foreach ($uriCandidates as $candidate) {
            $parameters = [
                'models'     => [
                    'TemplatesUri' => TemplatesUri::class,
                ],
                'conditions' => ':uri: LIKE TemplatesUri.uri',
                'bind'       => ['uri' => $candidate],
                'columns'    => [
                    'id'       => 'Templates.id',
                    'template' => 'Templates.template',
                    'name'     => 'Templates.name',
                    'uri'      => 'TemplatesUri.uri',
                ],
                'order'      => 'TemplatesUri.uri DESC',
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
            if (!empty($result)) {
                break;
            }
        }
        if(!empty($result)){
            // Substitute the vendor-agnostic placeholders before streaming the
            // template. The original code echoed the raw template body, which left
            // any `{FIRMWARE_URL}` / `{PBX_HOST}` token visible to the phone as a
            // literal string and broke firmware-URL emission for URIs that lived
            // outside the per-MAC branch below. For per-user placeholders ({SIP_*})
            // we try to autodetect the phone via its source IP (matched against
            // ModuleAutoprovisionDevice.host populated by PnP discovery) and
            // substitute its SIP credentials. If nothing is found, the warning
            // line is still logged so a misconfigured URI route remains visible.
            $template = (string)$result[0]['template'];

            $vendor   = $this->detectVendorFromUserAgent((string)$userAgent);
            $sipData  = [];
            $clientIp = (string)$this->request->getClientAddress(true);
            if ($clientIp !== '' && filter_var($clientIp, FILTER_VALIDATE_IP)) {
                $device = ModuleAutoprovisionDevice::findFirst([
                    'host = :host:',
                    'bind' => ['host' => $clientIp],
                ]);
                if ($device !== null && !empty($device->mac)) {
                    $sipData = Autoprovision::lookupSipDataForMac((string)$device->mac);
                }
            }
            $rendered = Autoprovision::applyGenericPlaceholders($template, $vendor, null, $sipData);
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
                'kind'      => 'uri-template',
                'template'  => $result[0]['name'] ?? '',
                'bytes'     => strlen($rendered),
                'sip_auto'  => empty($sipData) ? 'none' : ($clientIp ?: 'unknown'),
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

            // Render via the shared per-MAC renderer — the same function the
            // TFTP worker calls. Keeps the HTTP and TFTP channels byte-identical
            // for any MAC that has a TemplatesUsers mapping.
            $config = Autoprovision::renderTemplateConfig($mac, (string)$userAgent);
            if ($config !== null) {
                $vendor = $this->detectVendorFromUserAgent((string)$userAgent);
                $this->response->setHeader('Content-Description', "config file");
                $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri));
                $this->response->setHeader('Content-type', "text/plain");
                $this->response->setHeader('Content-Transfer-Encoding', "binary");
                $this->response->sendHeaders();
                echo $config;
                $this->logHttpRequest(200, [
                    'kind'   => 'per-mac',
                    'mac'    => $mac,
                    'vendor' => $vendor,
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
        $this->response->setHeader('Content-type', "text/xml; charset=UTF-8");
        $this->response->setHeader('Content-Length', (string)strlen($phoneBook));
        $this->response->setHeader('Content-Transfer-Encoding', "binary");
        $this->response->sendHeaders();
        echo $phoneBook;

    }
    private function echoPhoneBookYealink($uri):void
    {
        $nameBook = $_REQUEST['name'] ?? '';
        if (!is_scalar($nameBook)) {
            // Массивный query-параметр (?name[]=x) не должен фаталить на ключе массива.
            $this->response->setStatusCode(400, 'Bad Request');
            $this->response->sendRaw();
            $this->logHttpRequest(400, ['kind' => 'phonebook-yealink', 'error' => 'name-not-scalar']);
            $this->terminateStreamedResponse();
            return;
        }
        $nameBook   = (string)$nameBook;
        // Дочерний запрос (?name=...) приходит от другой АТС из OtherPBX.
        // Непустое имя запрещает обход OtherPBX, иначе агрегация зациклится.
        $isChildRequest = $nameBook !== '';
        $title = $isChildRequest ? $nameBook : 'Yealink';

        // Локальные контакты: группы ModuleUsersGroups категоризируют общий
        // справочник (это не права доступа — все SIP-контакты выдаются всем).
        $groupsEnabled = class_exists('\Modules\ModuleUsersGroups\Models\UsersGroups')
            && PbxExtensionUtils::isEnabled('ModuleUsersGroups');
        $manager = $this->di->get('modelsManager');
        $userGroupId = [];
        $groupTitles = [];
        if ($groupsEnabled) {
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
                // При членстве в нескольких группах побеждает последняя запись —
                // прежняя семантика, переопределять её без отдельного решения нельзя.
                $gid = (string)$group['group_id'];
                $userGroupId[(string)$group['user_id']] = $gid;
                $groupTitles[$gid] = (string)$group['name'];
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
                'userid'   => 'Extensions.userid',
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
        $commonEntries = [];
        $groupEntries = [];
        foreach ($users as $userData) {
            $entry = [
                'name'      => (string)$userData['username'],
                'telephone' => (string)$userData['number'],
            ];
            $gid = $userGroupId[(string)$userData['userid']] ?? '';
            if ($groupsEnabled && $gid !== '') {
                $groupEntries[$gid][] = $entry;
            } else {
                $commonEntries[] = $entry;
            }
        }
        // Удалённые книги собираются до локальных: локальная запись OtherPBX
        // (127.0.0.1) может переопределить название общей книги.
        $remoteBooks = [];
        if (!$isChildRequest) {
            $otherPbx = OtherPBX::find()->toArray();
            $client = new Client();
            foreach ($otherPbx as $pbx){
                $address = trim($pbx['address']);
                if($address === '127.0.0.1'){
                    // Локальная запись OtherPBX задаёт название книги.
                    $title = (string)$pbx['name'];
                    continue;
                }
                if($address === ''){
                    SystemMessages::sysLogMsg(self::LOG_TAG, "Skip phonebook from empty OtherPBX address (id {$pbx['id']})", LOG_WARNING);
                    continue;
                }
                // Пустое имя (и "0" в старых версиях с empty()) на принимающей
                // стороне означает корневой запрос и снова запускает обход —
                // гарантируем непустой маркер дочернего запроса.
                $remoteName = (string)$pbx['name'];
                if ($remoteName === '' || $remoteName === '0') {
                    $remoteName = 'pbx-'.$pbx['id'];
                }
                try {
                    $response = $client->request('GET', "http://$address".AutoprovisionConf::BASE_URI.$uri, [
                        'timeout' => 1,
                        'connect_timeout' => 1,
                        'read_timeout' => 1,
                        'query' => ['name' => $remoteName],
                    ]);
                    $code = $response->getStatusCode();
                }catch (\Throwable $e){
                    $code = 0;
                }
                if ($code === 200) {
                    try {
                        $sourceId = 'otherpbx:'.$pbx['id'];
                        $remoteBooks = array_merge(
                            $remoteBooks,
                            YealinkPhonebook::import($response->getBody()->getContents(), $sourceId, (string)$pbx['name'])
                        );
                    }catch (\UnexpectedValueException $e){
                        // Отказ одного источника не должен ломать выдачу остальных.
                        SystemMessages::sysLogMsg(self::LOG_TAG, $e->getMessage(), LOG_WARNING);
                    }catch (\Throwable $e){
                        SystemMessages::sysLogMsg(self::LOG_TAG, "Fail read phonebook from $address", LOG_WARNING);
                    }
                }else{
                    SystemMessages::sysLogMsg(self::LOG_TAG, "Fail get phonebook from $address, code: $code", LOG_WARNING);
                }
            }
            unset($otherPbx, $client);
        }

        // Книга группы строится по ID, название остаётся данными XML (атрибут
        // Menu/@Name) — числовые и кириллические названия больше не ломают теги.
        $localBooks = [];
        foreach ($groupEntries as $gid => $entries) {
            $localBooks[] = [
                'id'      => "local:group:{$gid}",
                'title'   => $groupTitles[$gid] ?? $gid,
                'entries' => $entries,
            ];
        }
        if ($commonEntries !== []) {
            $localBooks[] = ['id' => 'local:common', 'title' => $title, 'entries' => $commonEntries];
        }

        $phoneBook = YealinkPhonebook::render(
            array_merge($localBooks, $remoteBooks),
            $title,
            !$isChildRequest
        );
        $this->response->setHeader('Content-Description', "config file");
        $this->response->setHeader('Content-Disposition', "attachment; filename=".basename($uri).'.xml');
        // Yealink rejects text/plain and chunked transfer (ret=23), see #19.
        $this->response->setHeader('Content-type', "text/xml; charset=UTF-8");
        $this->response->setHeader('Content-Length', (string)strlen($phoneBook));
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
     * Serves the PnP-issued config URL. The route is NoAuth by design (phones
     * cannot log in), so the deterministic per-MAC HMAC signature (solt) is the
     * only credential — verify it before any generation work. Answers 404 (not
     * 403) to avoid revealing whether a MAC exists.
     */
    public function getConfig(): void
    {
        $mode = AutoprovisionConf::getSoltMode();
        if ($mode !== 'off') {
            $mac    = strtolower(trim((string)($this->request->getQuery('mac') ?? '')));
            $solt   = (string)($this->request->getQuery('solt') ?? '');
            $secret = AutoprovisionConf::getProvisioningSecret();
            $expected = $secret !== ''
                ? ProvisionUrl::calculateSolt($mac, $secret)
                : '';
            if ($mac === '' || $solt === '' || $expected === '' || !hash_equals($expected, $solt)) {
                // getcfg-no-secret: the check itself cannot run — most often the
                // settings row is not readable yet (fresh install, mid-upgrade);
                // everything else is a genuine signature mismatch.
                $kind = $expected === '' ? 'getcfg-no-secret' : 'getcfg-bad-signature';
                $this->logHttpRequest(404, ['kind' => $kind, 'mac' => $mac, 'mode' => $mode], LOG_WARNING);
                if ($mode === 'enforce') {
                    $this->response->setStatusCode(404, 'Not found');
                    $this->response->sendRaw();
                    $this->terminateStreamedResponse();
                }
                // 'log' mode: serve the config anyway — observation-only rollout.
            }
        }
        $this->streamModuleAction('getProvisionConfig');
    }

    /**
     */
    public function getImg(): void
    {
        $this->streamModuleAction('getImgFile');
    }

    /**
     * Runs a module action that answers with a file and lets BaseController stream it.
     *
     * callActionForModule() is not used: BaseController streams the core-format descriptor
     * (data.fpassthru = [filename, content_type, need_delete]) and leaves the content empty, and
     * cores without mikopbx/Core#1167 then pass json_decode('') = null to
     * ModulesControllerBase::handleResponse(array) — a TypeError after the body (Sentry MIKOPBX-N6K).
     * The payload is the one callActionForModule() builds.
     */
    private function streamModuleAction(string $action): void
    {
        $payload = $this->request->getData();
        $payload['ip_srv'] = $_SERVER['SERVER_ADDR'];
        // The backend worker only receives this payload, not the HTTP headers —
        // yet getProvisionConfig needs the phone's User-Agent to resolve
        // {FIRMWARE_URL} the same way the per-MAC HTTP endpoint does.
        $payload['user_agent'] = (string)$this->request->getHeader('User-Agent');
        $this->sendRequestToBackendWorker(
            PbxExtensionsProcessor::class,
            $action,
            $payload,
            'ModuleAutoprovision',
            max(10, $this->request->getRequestTimeout())
        );
        // A file gone before streaming is answered with send() (404); a second send() throws.
        if (!$this->response->isSent()) {
            $this->response->sendRaw();
        }
    }
}