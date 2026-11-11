<?php

declare(strict_types=1);
/*
 * CLI regression runner для Lib/ProvisionUrl (инцидент 2026-11-11: PnP-воркер
 * кэшировал provisioning_secret в конструкторе и подписывал ссылки пустым
 * ключом — телефоны получали URL с заведомо невалидным solt и получали 404
 * под solt_mode=enforce). Без внешних зависимостей. Запуск из корня модуля:
 *
 *   php tests/ProvisionUrlTest.php
 */

use Modules\ModuleAutoprovision\Lib\ProvisionUrl;

const CLASS_FILE = __DIR__ . '/../Lib/ProvisionUrl.php';

const MAC = '0c383e43cc2b';
const SECRET = '0123456789abcdef0123456789abcdef';

/** @var array<int, array{0: string, 1: callable}> $tests */
$tests = [];

function test(string $name, callable $fn): void
{
    global $tests;
    $tests[] = [$name, $fn];
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function loadClass(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    check(is_file(CLASS_FILE), 'Отсутствует ' . CLASS_FILE);
    require CLASS_FILE;
    $loaded = true;
}

// ---------------------------------------------------------------------------
// A. calculateSolt: известные ответы (KAT)
// ---------------------------------------------------------------------------

test(
    'A1: известная пара mac+secret даёт ожидаемый solt',
    static function (): void {
        loadClass();
        check(
            ProvisionUrl::calculateSolt(MAC, SECRET) === '52599fa85dceb0acae28ccd4158d5f05',
            'KAT по HMAC-SHA256(mac, secret) со срезом 32 символа'
        );
    }
);

test(
    'A2: пустой секрет даёт HMAC с пустым ключом (регрессия инцидента 2026-11-11)',
    static function (): void {
        loadClass();
        // Ровно этот solt телефон получил от PnP-воркера 172.16.32.77, когда
        // воркер подписал ссылку пустым ключом. Значение зафиксировано, чтобы
        // алгоритм (в т.ч. поведение при пустом секрете) не «улучшили» незаметно.
        check(
            ProvisionUrl::calculateSolt(MAC, '') === '2b52e90cadf0f93a6bb85ede70b301b3',
            'HMAC-SHA256 с пустым ключом должен совпадать с подписью из инцидента'
        );
    }
);

test(
    'A3: calculateSolt совместим с substr(hash_hmac(...), 0, 32)',
    static function (): void {
        loadClass();
        foreach ([MAC, 'aabbccddeeff', 'ffffffffffff'] as $mac) {
            foreach ([SECRET, '', 'short'] as $secret) {
                $expected = substr(hash_hmac('sha256', $mac, $secret), 0, 32);
                check(
                    ProvisionUrl::calculateSolt($mac, $secret) === $expected,
                    "Формула подписи изменилась для mac=$mac — проверка getcfg разъедется с PnP-воркером"
                );
            }
        }
    }
);

// ---------------------------------------------------------------------------
// B. buildProvisionUrl: подписанная ссылка
// ---------------------------------------------------------------------------

test(
    'B1: solt идёт сразу после mac (контракт обрезки Fanvil)',
    static function (): void {
        loadClass();
        $url = ProvisionUrl::buildProvisionUrl(
            'http://pbx.local:8480/pbxcore/api/autoprovision',
            MAC,
            ['vendor' => 'fanvil', 'model' => 'x5u', 'version' => 't0.7.2'],
            SECRET
        );
        $expected = 'http://pbx.local:8480/pbxcore/api/autoprovision/getcfg'
            . '?mac=' . MAC
            . '&solt=' . ProvisionUrl::calculateSolt(MAC, SECRET)
            . '&vendor=fanvil&model=x5u&version=t0.7.2';
        check($url === $expected, "URL отличается от эталона: $url");
    }
);

test(
    'B2: подпись в ссылке совпадает с calculateSolt того же секрета',
    static function (): void {
        loadClass();
        $url = ProvisionUrl::buildProvisionUrl('http://pbx:8480/pbxcore/api/autoprovision', MAC, [], SECRET);
        check(
            str_contains($url, 'solt=' . ProvisionUrl::calculateSolt(MAC, SECRET)),
            'solt в URL должен быть вычислен тем же calculateSolt'
        );
    }
);

// ---------------------------------------------------------------------------
// C. buildProvisionUrl: пустой секрет — ссылка без solt
// ---------------------------------------------------------------------------

test(
    'C1: пустой секрет — параметр solt не выдаётся вовсе',
    static function (): void {
        loadClass();
        $url = ProvisionUrl::buildProvisionUrl(
            'http://pbx:8480/pbxcore/api/autoprovision',
            MAC,
            ['vendor' => 'fanvil'],
            ''
        );
        check(!str_contains($url, 'solt'), 'Пустой секрет не должен попадать в URL как solt=');
        check(str_contains($url, 'mac=' . MAC), 'MAC сохранён');
        check(str_contains($url, '&vendor=fanvil'), 'Параметры события сохранены');
    }
);

test(
    'C2: пустой секрет и пустые event-параметры — без хвостового "&"',
    static function (): void {
        loadClass();
        $url = ProvisionUrl::buildProvisionUrl('http://pbx:8480/pbxcore/api/autoprovision', MAC, [], '');
        check(!str_ends_with($url, '&'), 'Хвостовой & при пустых event-параметрах не нужен');
        check(str_ends_with($url, 'mac=' . MAC), 'URL заканчивается на mac');
    }
);

test(
    'C3: непустой секрет и пустые event-параметры — solt в конце без "&"',
    static function (): void {
        loadClass();
        $url = ProvisionUrl::buildProvisionUrl('http://pbx:8480/pbxcore/api/autoprovision', MAC, [], SECRET);
        check(str_ends_with($url, 'solt=' . ProvisionUrl::calculateSolt(MAC, SECRET)), 'solt замыкает URL');
    }
);

// ---------------------------------------------------------------------------
// Runner
// ---------------------------------------------------------------------------

$passed = 0;
$failed = 0;
foreach ($tests as [$name, $fn]) {
    try {
        $fn();
        $passed++;
        echo "ok   - $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "FAIL - $name\n      {$e->getMessage()}\n";
    }
}

echo "\nПроверок: " . count($tests) . ", прошло: $passed, упало: $failed\n";
exit($failed === 0 ? 0 : 1);
