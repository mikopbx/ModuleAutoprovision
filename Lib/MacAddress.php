<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib;

/**
 * Нормализация и сопоставление MAC-адресов для привязок TemplatesUsers.
 *
 * Каналы провизионирования (PnP SUBSCRIBE, getcfg URL, TFTP-имя файла) всегда
 * несут MAC как 12 строчных hex-символов, а оператор вводит его в веб-интерфейсе
 * с разделителями (0c:38:3e:43:cc:2b). Обе формы должны находить одну привязку,
 * поэтому любое сравнение идёт через normalize(). Класс без зависимостей от
 * DI/моделей — используется и контроллером при сохранении, и генератором конфига.
 */
final class MacAddress
{
    /**
     * Каноническая форма: нижний регистр, без разделителей. LIKE-маски (% и _)
     * сохраняются, чтобы операторские маски вида `0c383e%` не теряли смысла.
     */
    public static function normalize(string $mac): string
    {
        return str_replace([':', '-', '.', ' '], '', strtolower(trim($mac)));
    }

    /**
     * LIKE-сопоставление сохранённой маски MAC с запрошенным MAC, обе стороны
     * нормализуются. % — любая последовательность символов, _ — ровно один.
     */
    public static function matches(string $pattern, string $mac): bool
    {
        $pattern = self::normalize($pattern);
        $mac     = self::normalize($mac);
        if ($pattern === '') {
            return false;
        }
        $regex = '/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($pattern, '/')) . '$/';
        return preg_match($regex, $mac) === 1;
    }

    /**
     * Выбирает строку TemplatesUsers, которая должна обслужить $mac:
     * строки, отсеянные $isFilled, не побеждают никогда; точное совпадение MAC
     * важнее любой маски; иначе побеждает самая длинная (специфичная) маска.
     * При равной длине выигрывает первая строка.
     *
     * @param array<int, array<string, mixed>> $rows строки с ключом 'mac'
     * @param callable(array<string, mixed>): bool $isFilled критерий заполненной привязки
     * @return array<string, mixed>|null
     */
    public static function selectBestRow(array $rows, string $mac, callable $isFilled): ?array
    {
        $mac = self::normalize($mac);
        if ($mac === '') {
            return null;
        }
        $best    = null;
        $bestLen = -1;
        foreach ($rows as $row) {
            $pattern = self::normalize((string)($row['mac'] ?? ''));
            if ($pattern === '' || !self::matches($pattern, $mac) || !$isFilled($row)) {
                continue;
            }
            if ($pattern === $mac) {
                return $row;
            }
            $len = strlen($pattern);
            if ($len > $bestLen) {
                $bestLen = $len;
                $best    = $row;
            }
        }
        return $best;
    }
}