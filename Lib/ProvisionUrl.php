<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib;

/**
 * Подпись и сборка provisioning-URL для канала PnP.
 *
 * Класс без зависимостей от DI/моделей: один и тот же calculateSolt()
 * используется и PnP-воркером при выдаче ссылки, и GetController при
 * проверке getcfg — расхождение формул между ними означает вечные
 * 404 getcfg-bad-signature у всех телефонов.
 */
final class ProvisionUrl
{
    /**
     * HMAC-подпись getcfg-запроса (solt): первые 32 символа HMAC-SHA256($mac, $secret).
     *
     * $mac должен быть в том же виде, в каком телефон отправит его в запросе
     * (PnP-воркер приводит MAC к строчным 12-hex до вызова).
     */
    public static function calculateSolt(string $mac, string $secret): string
    {
        return substr(hash_hmac('sha256', $mac, $secret), 0, 32);
    }

    /**
     * Собирает provisioning-URL для NOTIFY-ответа на SUBSCRIBE.
     *
     * solt ставится сразу после mac: Fanvil обрезает provisioning-URL на
     * границе параметра, поэтому «расходные» vendor/model/version в хвосте
     * могут отвалиться, а подпись — нет. При пустом секрете параметр solt
     * не выдаётся вовсе: подпись пустым ключом заведомо невалидна при
     * solt_mode=enforce, а в режимах off/log беззнаковая ссылка рабочая.
     *
     * @param array<string, string> $eventParams vendor/model/version из SUBSCRIBE.
     */
    public static function buildProvisionUrl(string $baseUrl, string $mac, array $eventParams, string $secret): string
    {
        $url = rtrim($baseUrl, '/') . "/getcfg?mac={$mac}";
        if ($secret !== '') {
            $url .= '&solt=' . self::calculateSolt($mac, $secret);
        }
        $query = http_build_query($eventParams);
        if ($query !== '') {
            $url .= '&' . $query;
        }
        return $url;
    }
}
