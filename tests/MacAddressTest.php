<?php

declare(strict_types=1);
/*
 * CLI regression runner для Lib/MacAddress (инцидент 2026-11-11: привязка
 * телефона, сохранённая с MAC через двоеточия, не находилась запросом getcfg
 * без двоеточий — телефон получал дефолтный аккаунт apv-miko-pbx вместо
 * назначенного добавочного). Без внешних зависимостей. Запуск из корня модуля:
 *
 *   php tests/MacAddressTest.php
 *
 * Раздел A воспроизводит дефект прежнего SQL LIKE. Разделы B–D проверяют
 * публичный контракт MacAddress.
 */

use Modules\ModuleAutoprovision\Lib\MacAddress;

const CLASS_FILE = __DIR__ . '/../Lib/MacAddress.php';

// MAC из инцидента: в UI введён с двоеточиями, телефон запрашивает без них.
const MAC_STORED = '0c:38:3e:43:cc:2b';
const MAC_CLEAN  = '0c383e43cc2b';

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

/**
 * Строка привязки в форме, которую отдаёт modelsManager->toArray().
 *
 * @param array<string, mixed> $extra
 * @return array<string, mixed>
 */
function binding(string $mac, string $userId, array $extra = []): array
{
    return array_merge(['mac' => $mac, 'userId' => $userId], $extra);
}

function filledByUserId(array $row): bool
{
    return trim((string)($row['userId'] ?? '')) !== '';
}

// ---------------------------------------------------------------------------
// A. Воспроизведение дефекта: прежний поиск строил SQL LIKE без нормализации
// ---------------------------------------------------------------------------

test(
    'A1: SQL LIKE не сопоставляет MAC с двоеточиями и без (корень инцидента)',
    static function (): void {
        if (!extension_loaded('pdo_sqlite')) {
            return; // раздел-иллюстрация, не обязательный контракт
        }
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare('SELECT :mac LIKE :pattern');
        $stmt->execute(['mac' => MAC_CLEAN, 'pattern' => MAC_STORED]);
        check(
            (int)$stmt->fetchColumn() === 0,
            'LIKE без нормализации обязан НЕ находить привязку — иначе тест не воспроизводит инцидент'
        );
        $stmt->execute(['mac' => MAC_CLEAN, 'pattern' => MAC_CLEAN]);
        check((int)$stmt->fetchColumn() === 1, 'Совпадающие формы LIKE находит');
    }
);

// ---------------------------------------------------------------------------
// B. normalize: каноническая форма
// ---------------------------------------------------------------------------

test(
    'B1: двоеточия убираются (форма из инцидента)',
    static function (): void {
        loadClass();
        check(MacAddress::normalize(MAC_STORED) === MAC_CLEAN, 'MAC с двоеточиями должен стать 12-hex');
    }
);

test(
    'B2: верхний регистр приводится к нижнему',
    static function (): void {
        loadClass();
        check(MacAddress::normalize('0C:38:3E:43:CC:2B') === MAC_CLEAN, 'Регистр не должен влиять на поиск');
    }
);

test(
    'B3: дефисы и точки (Cisco-формат) убираются',
    static function (): void {
        loadClass();
        check(MacAddress::normalize('0c-38-3e-43-cc-2b') === MAC_CLEAN, 'Дефисы — разделители');
        check(MacAddress::normalize('0c38.3e43.cc2b') === MAC_CLEAN, 'Точки — разделители');
    }
);

test(
    'B4: пробелы по краям и внутри убираются',
    static function (): void {
        loadClass();
        check(MacAddress::normalize(' 0c 38 3e 43 cc 2b ') === MAC_CLEAN, 'Пробелы не должны ломать привязку');
    }
);

test(
    'B5: каноническая форма не меняется, пустая строка остаётся пустой',
    static function (): void {
        loadClass();
        check(MacAddress::normalize(MAC_CLEAN) === MAC_CLEAN, 'Чистый MAC неизменен');
        check(MacAddress::normalize('') === '', 'Пустая строка');
    }
);

test(
    'B6: LIKE-маски сохраняют wildcard после нормализации',
    static function (): void {
        loadClass();
        check(MacAddress::normalize('0C:38:3E%') === '0c383e%', '% должен пережить нормализацию');
        check(MacAddress::normalize('0c383e43cc__') === '0c383e43cc__', '_ должен пережить нормализацию');
    }
);

// ---------------------------------------------------------------------------
// C. matches: LIKE-семантика поверх нормализации
// ---------------------------------------------------------------------------

test(
    'C1: пара инцидента — привязка с двоеточиями находится чистым MAC',
    static function (): void {
        loadClass();
        check(
            MacAddress::matches(MAC_STORED, MAC_CLEAN) === true,
            'Именно этот запрос 2026-11-11 ушёл в fallback apv-miko-pbx'
        );
    }
);

test(
    'C2: обратное направление — чистая привязка находится MAC с двоеточиями',
    static function (): void {
        loadClass();
        check(MacAddress::matches(MAC_CLEAN, MAC_STORED) === true, 'Нормализуются обе стороны');
    }
);

test(
    'C3: регистр обеих сторон не важен',
    static function (): void {
        loadClass();
        check(MacAddress::matches('0C383E43CC2B', '0c383e43cc2b') === true, 'LIKE регистронезависим для ASCII');
    }
);

test(
    'C4: маска с % работает как LIKE',
    static function (): void {
        loadClass();
        check(MacAddress::matches('0c383e%', MAC_CLEAN) === true, 'Префиксная маска совпадает');
        check(MacAddress::matches('0c383e%', '0c383f43cc2b') === false, 'Чужой префикс не совпадает');
        check(MacAddress::matches('0c:38:3e%', MAC_CLEAN) === true, 'Маска с двоеточиями тоже нормализуется');
    }
);

test(
    'C5: _ — ровно один символ',
    static function (): void {
        loadClass();
        check(MacAddress::matches('0c383e43cc2_', MAC_CLEAN) === true, '_ заменяет один символ');
        check(MacAddress::matches('0c383e43cc2_', '0c383e43cc2') === false, '_ не заменяет пустоту');
        check(MacAddress::matches('0c383e43cc2_', '0c383e43cc2bb') === false, '_ не заменяет два символа');
    }
);

test(
    'C6: другой MAC не совпадает',
    static function (): void {
        loadClass();
        check(MacAddress::matches(MAC_STORED, '0c383e43cc2c') === false, 'Последний символ отличается');
    }
);

test(
    'C7: пустая маска ничего не находит',
    static function (): void {
        loadClass();
        check(MacAddress::matches('', MAC_CLEAN) === false, 'Пустая строка в БД — не привязка');
    }
);

test(
    'C8: спецсимволы regex в маске трактуются буквально',
    static function (): void {
        loadClass();
        // Без preg_quote '+' стал бы квантификатором и маска «совпала» бы.
        check(MacAddress::matches('0c383e43cc2b+', MAC_CLEAN) === false, '+ не квантификатор');
        check(MacAddress::matches('%', 'ffffffffffff') === true, '% совпадает с любым MAC');
    }
);

// ---------------------------------------------------------------------------
// D. selectBestRow: правила выбора привязки
// ---------------------------------------------------------------------------

test(
    'D1: точное совпадение важнее маски при любом порядке строк',
    static function (): void {
        loadClass();
        $exact   = binding(MAC_CLEAN, 'u-exact');
        $pattern = binding('0c383e%', 'u-pattern');
        foreach ([[$pattern, $exact], [$exact, $pattern]] as $rows) {
            $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
            check($row !== null && $row['userId'] === 'u-exact', 'Точная привязка должна побеждать маску');
        }
    }
);

test(
    'D2: точное совпадение важнее маски той же длины',
    static function (): void {
        loadClass();
        $rows = [
            binding('0c383e43cc2_', 'u-wildcard'),
            binding(MAC_CLEAN, 'u-exact'),
        ];
        $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
        check($row !== null && $row['userId'] === 'u-exact', 'Wildcard той же длины не должен затмевать точный MAC');
    }
);

test(
    'D3: из масок побеждает самая длинная (специфичная)',
    static function (): void {
        loadClass();
        $short = binding('0c%', 'u-short');
        $long  = binding('0c383e%', 'u-long');
        foreach ([[$short, $long], [$long, $short]] as $rows) {
            $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
            check($row !== null && $row['userId'] === 'u-long', 'Длинная маска специфичнее короткой');
        }
    }
);

test(
    'D4: пустая привязка (PnP-плейсхолдер) не затмевает заполненную',
    static function (): void {
        loadClass();
        $rows = [
            binding(MAC_CLEAN, ''),          // авто-строка PnP без сотрудника
            binding('0c383e%', 'u-filled'),
        ];
        $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
        check($row !== null && $row['userId'] === 'u-filled', 'Плейсхолдер не должен побеждать');
    }
);

test(
    'D5: все привязки пустые — ничего не выбрано',
    static function (): void {
        loadClass();
        $row = MacAddress::selectBestRow([binding(MAC_CLEAN, ''), binding('0c383e%', '  ')], MAC_CLEAN, 'filledByUserId');
        check($row === null, 'Пустые userId не дают привязку');
    }
);

test(
    'D6: маски с разделителями и в верхнем регистре участвуют в отборе',
    static function (): void {
        loadClass();
        $rows = [
            binding('0C%', 'u-short'),
            binding('0C:38:3E%', 'u-long'),
        ];
        $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
        check($row !== null && $row['userId'] === 'u-long', 'Длина считается после нормализации');
    }
);

test(
    'D7: инцидент end-to-end на уровне отбора — одна строка с двоеточиями',
    static function (): void {
        loadClass();
        $rows = [binding(MAC_STORED, 'u-bound', ['template' => ''])];
        $row  = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
        check($row !== null && $row['userId'] === 'u-bound', 'Привязка из инцидента должна находиться');
    }
);

test(
    'D8: нет совпадений / пустой MAC — null',
    static function (): void {
        loadClass();
        check(MacAddress::selectBestRow([binding('aabbcc%', 'u')], MAC_CLEAN, 'filledByUserId') === null, 'Чужая маска');
        check(MacAddress::selectBestRow([binding(MAC_CLEAN, 'u')], '', 'filledByUserId') === null, 'Пустой MAC');
    }
);

test(
    'D9: при равной длине масок побеждает первая строка',
    static function (): void {
        loadClass();
        $rows = [
            binding('0c383e43cc%', 'u-first'),
            binding('0c383e43c_', 'u-second'),
        ];
        $row = MacAddress::selectBestRow($rows, MAC_CLEAN, 'filledByUserId');
        check($row !== null && $row['userId'] === 'u-first', 'Стабильный выбор при равной специфичности');
    }
);

test(
    'D10: критерий заполненности задаётся вызывающей стороной (template)',
    static function (): void {
        loadClass();
        $rows = [
            binding(MAC_CLEAN, 'u1', ['template' => '']),
            binding('0c383e%', 'u2', ['template' => 'body']),
        ];
        $row = MacAddress::selectBestRow(
            $rows,
            MAC_CLEAN,
            static fn(array $r): bool => (string)($r['template'] ?? '') !== ''
        );
        check($row !== null && $row['userId'] === 'u2', 'Для шаблонов пустое тело — не привязка');
    }
);

test(
    'D11: критерий по длине шаблона — NULL/0 не побеждают (двухэтапная выборка)',
    static function (): void {
        loadClass();
        // renderTemplateConfig() не тянет тела шаблонов в запросе: признак
        // заполненности — LENGTH(Templates.template), NULL при битом join.
        $isFilled = static fn(array $r): bool => (int)($r['tplLen'] ?? 0) > 0;
        $rows = [
            binding(MAC_CLEAN, 'u-empty', ['tplLen' => 0]),
            binding('0c383e43cc2_', 'u-dangling', ['tplLen' => null]),
            binding('0c383e%', 'u-filled', ['tplLen' => 64]),
        ];
        $row = MacAddress::selectBestRow($rows, MAC_CLEAN, $isFilled);
        check($row !== null && $row['userId'] === 'u-filled', 'Только строка с непустым шаблоном может победить');
        check(MacAddress::selectBestRow([$rows[0], $rows[1]], MAC_CLEAN, $isFilled) === null, 'Пустые/битые шаблоны не дают привязку');
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