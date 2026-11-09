<?php

declare(strict_types=1);
/*
 * CLI regression runner для Lib/Phonebook/YealinkPhonebook (issue #38).
 * Без внешних зависимостей. Запуск из корня модуля:
 *
 *   php tests/YealinkPhonebookTest.php
 *
 * Раздел A воспроизводит дефекты прежней генерации (числовой тег группы,
 * несколько корней, декларация без корня) и фиксирует, что все эти формы
 * невалидны. Разделы B и C проверяют публичный контракт YealinkPhonebook.
 */

use Modules\ModuleAutoprovision\Lib\Phonebook\YealinkPhonebook;

const CLASS_FILE = __DIR__ . '/../Lib/Phonebook/YealinkPhonebook.php';

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

function strictXml(string $body): SimpleXMLElement
{
    $previous = libxml_use_internal_errors(true);
    try {
        libxml_clear_errors();
        $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET);
        $errors = libxml_get_errors();
        check($xml !== false && $errors === [], 'Ответ должен быть полным корректным XML');
        return $xml;
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
}

function assertInvalidXml(string $body, string $why): void
{
    try {
        strictXml($body);
    } catch (RuntimeException $e) {
        return;
    }
    check(false, "Ожидался невалидный XML ($why), но разбор прошёл");
}

/**
 * Рендер/импорт не должны выдавать PHP-предупреждения (они попали бы в тело ответа).
 */
function assertNoPhpWarnings(callable $fn): void
{
    set_error_handler(
        static function (int $no, string $str): bool {
            throw new RuntimeException("PHP warning: $str");
        }
    );
    try {
        $fn();
    } finally {
        restore_error_handler();
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
 * Книга в нормализованном виде из плана:
 * id — внутренний ключ, title — исходное название, entries — контакты.
 */
function book(string $id, string $title, array $entries): array
{
    return ['id' => $id, 'title' => $title, 'entries' => $entries];
}

// ---------------------------------------------------------------------------
// A. Репродукция дефектов прежней генерации (синтетика текущего шаблона)
// ---------------------------------------------------------------------------

test(
    'A1: числовое название группы даёт недопустимый XML-тег',
    static function (): void {
        $legacy = "<123IPPhoneDirectory>\n\t<Title>123 Group</Title>\n"
            . "\t<DirectoryEntry>\n\t\t<Name>User A</Name>\n\t\t<Telephone>9001</Telephone>\n\t</DirectoryEntry>\n"
            . "</123IPPhoneDirectory>\n";
        assertInvalidXml($legacy, 'числовой тег <123IPPhoneDirectory>');
    }
);

test(
    'A2: две непустые книги дают два корня в одном ответе',
    static function (): void {
        $legacy = "<SalesIPPhoneDirectory>\n\t<Title>Sales</Title>\n</SalesIPPhoneDirectory>\n"
            . "<SupportIPPhoneDirectory>\n\t<Title>Support</Title>\n</SupportIPPhoneDirectory>\n";
        assertInvalidXml($legacy, 'несколько корней');
    }
);

test(
    'A3: группа плюс общая книга тоже дают несколько корней',
    static function (): void {
        $legacy = "<SalesIPPhoneDirectory>\n\t<Title>Sales</Title>\n</SalesIPPhoneDirectory>\n"
            . "<YealinkIPPhoneDirectory>\n\t<Title>Yealink</Title>\n</YealinkIPPhoneDirectory>\n";
        assertInvalidXml($legacy, 'группа + общая книга');
    }
);

test(
    'A4: книга без контактов — одна XML-декларация без корня',
    static function (): void {
        $legacy = "<?xml version='1.0' encoding='UTF-8'?>\n";
        assertInvalidXml($legacy, 'декларация без корня');
    }
);

// ---------------------------------------------------------------------------
// B. Контракт render(): групповой YealinkIPPhoneBook
// ---------------------------------------------------------------------------

test(
    'B1: без групп — один Menu общей книги, корень и атрибуты по эталону',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [book('local:common', 'Yealink', [['name' => 'User A', 'telephone' => '9001']])],
            'Yealink'
        );
        check(str_starts_with($xml, '<?xml '), 'Корневой ответ должен начинаться с XML-декларации');
        $root = strictXml($xml);
        check($root->getName() === 'YealinkIPPhoneBook', 'Корень должен быть YealinkIPPhoneBook');
        check((string)$root->Title === 'Yealink', 'Title должен быть "Yealink"');
        $menus = $root->Menu;
        check(count($menus) === 1, 'Должен быть ровно один Menu');
        check((string)$menus[0]['Name'] === 'Yealink', 'Menu/@Name должен равняться названию книги');
        $units = $menus[0]->Unit;
        check(count($units) === 1, 'Должен быть один Unit');
        check((string)$units[0]['Name'] === 'User A', 'Unit/@Name');
        check((string)$units[0]['Phone1'] === '9001', 'Unit/@Phone1');
        check((string)$units[0]['Phone2'] === '' && (string)$units[0]['Phone3'] === '', 'Unit/@Phone2-3 пустые');
        check((string)$units[0]['default_photo'] === '', 'Unit/@default_photo пустой');
    }
);

test(
    'B2: числовая группа больше не влияет на структуру',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [book('local:group:12', '123 Group', [['name' => 'User A', 'telephone' => '9001']])],
            'Yealink'
        );
        $root = strictXml($xml);
        check($root->getName() === 'YealinkIPPhoneBook', 'Корень фиксирован');
        check((string)$root->Menu[0]['Name'] === '123 Group', 'Название группы — данные атрибута');
        check((string)$root->Menu[0]->Unit[0]['Phone1'] === '9001', 'Контакт сохранён');
    }
);

test(
    'B3: две группы и общая книга — три Menu, все контакты на месте',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [
                book('local:group:1', 'Sales', [
                    ['name' => 'User A', 'telephone' => '9001'],
                    ['name' => 'User B', 'telephone' => '9002'],
                ]),
                book('local:group:2', 'Support', [
                    ['name' => 'User C', 'telephone' => '9003'],
                ]),
                book('local:common', 'Yealink', [
                    ['name' => 'User D', 'telephone' => '9004'],
                ]),
            ],
            'Yealink'
        );
        $root = strictXml($xml);
        check(count($root->Menu) === 3, 'Три Menu');
        $total = 0;
        foreach ($root->Menu as $menu) {
            $total += count($menu->Unit);
        }
        check($total === 4, 'Все 4 контакта сохранены, найдено ' . $total);
        check((string)$root->Menu[0]['Name'] === 'Sales' && (string)$root->Menu[1]['Name'] === 'Support', 'Порядок книг сохранён');
    }
);

test(
    'B4: кириллица в названиях групп и контактов проходит round-trip',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [book('local:group:1', 'Отдел продаж', [['name' => 'Пользователь Ы', 'telephone' => '9001']])],
            'Yealink'
        );
        $root = strictXml($xml);
        check((string)$root->Menu[0]['Name'] === 'Отдел продаж', 'Название группы без транслитерации');
        check((string)$root->Menu[0]->Unit[0]['Name'] === 'Пользователь Ы', 'Имя контакта без транслитерации');
    }
);

test(
    'B5: XML-метасимволы в названиях не ломают документ',
    static function (): void {
        loadClass();
        $tricky = 'Group & <Support> "quoted" \'single\'';
        $xml = YealinkPhonebook::render(
            [
                book('local:group:1', $tricky, [['name' => 'A & B <tag>', 'telephone' => '9001']]),
            ],
            'Yealink & Co'
        );
        $root = strictXml($xml);
        check((string)$root->Title === 'Yealink & Co', 'Title round-trip');
        check((string)$root->Menu[0]['Name'] === $tricky, 'Menu/@Name round-trip');
        check((string)$root->Menu[0]->Unit[0]['Name'] === 'A & B <tag>', 'Unit/@Name round-trip');
        check(!str_contains($xml, '<Support>'), 'Сырой текст не должен попадать как разметка');
    }
);

test(
    'B6: названия, совпадавшие после camelize(), не сливаются',
    static function (): void {
        loadClass();
        // camelize("Отдел А") == camelize("Otdel A") == "OtdelA" — старый код терял книгу
        $xml = YealinkPhonebook::render(
            [
                book('local:group:1', 'Отдел А', [['name' => 'User A', 'telephone' => '9001']]),
                book('local:group:2', 'Otdel A', [['name' => 'User B', 'telephone' => '9002']]),
            ],
            'Yealink'
        );
        $root = strictXml($xml);
        check(count($root->Menu) === 2, 'Обе группы должны остаться отдельными Menu');
        $phones = [];
        foreach ($root->Menu as $menu) {
            foreach ($menu->Unit as $unit) {
                $phones[] = (string)$unit['Phone1'];
            }
        }
        sort($phones);
        check($phones === ['9001', '9002'], 'Контакты обеих групп сохранены');
    }
);

test(
    'B7: дубликаты имён контактов не стирают записи',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [book('local:common', 'Yealink', [
                ['name' => 'User A', 'telephone' => '9001'],
                ['name' => 'User A', 'telephone' => '9002'],
            ])],
            'Yealink'
        );
        $root = strictXml($xml);
        check(count($root->Menu[0]->Unit) === 2, 'Обе записи с одинаковым именем сохранены');
    }
);

test(
    'B8: пустая книга — валидный документ с одним корнем',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render([], 'Yealink');
        $root = strictXml($xml);
        check($root->getName() === 'YealinkIPPhoneBook', 'Корень YealinkIPPhoneBook');
        check((string)$root->Title === 'Yealink', 'Title присутствует');
        check(count($root->Menu) === 0, 'Menu не должно быть');
    }
);

test(
    'B9: дочерний режим — без XML-декларации, но полный документ',
    static function (): void {
        loadClass();
        $xml = YealinkPhonebook::render(
            [book('local:common', 'Office', [['name' => 'User A', 'telephone' => '9001']])],
            'Office',
            false
        );
        check(!str_starts_with($xml, '<?xml'), 'Дочерний ответ без декларации');
        $root = strictXml($xml);
        check($root->getName() === 'YealinkIPPhoneBook', 'Один корень даже без декларации');
    }
);

test(
    'B10: санитизация — запрещённые символы удалены, битый UTF-8 заменён, emoji сохранён',
    static function (): void {
        loadClass();
        assertNoPhpWarnings(
            static function () use (&$xml): void {
                $xml = YealinkPhonebook::render(
                    [book(
                        'local:common',
                        "Bad\x01\x02Ctrl",
                        [
                            ['name' => "A\xC3(B", 'telephone' => "9\x001"],
                            ['name' => "Emoji 😀 Таб\tстрока", 'telephone' => '9002'],
                        ]
                    )],
                    "T\x00itle"
                );
            }
        );
        $root = strictXml($xml);
        check((string)$root->Title === 'Title', 'U+0000 в Title удалён');
        check((string)$root->Menu[0]['Name'] === 'BadCtrl', 'Управляющие символы в названии удалены');
        // "\xC3(" — обрыв двухбайтовой последовательности: невалидный байт → U+FFFD
        check((string)$root->Menu[0]['Name'] === 'BadCtrl', 'Управляющие символы в названии удалены');
        check((string)$root->Menu[0]->Unit[0]['Name'] === "A\u{FFFD}(B", 'Битый UTF-8 заменён U+FFFD');
        check((string)$root->Menu[0]->Unit[0]['Phone1'] === '91', 'U+0000 в номере удалён');
        check((string)$root->Menu[0]->Unit[1]['Name'] === "Emoji 😀 Таб\tстрока", 'Emoji и табуляция сохранены');
        check(YealinkPhonebook::scrub("A\tB\r\nC") === "A\tB\r\nC", 'scrub сохраняет табуляцию и переводы строк');
    }
);

test(
    'B11: рендер не печатает предупреждений PHP в тело ответа',
    static function (): void {
        loadClass();
        assertNoPhpWarnings(
            static function () use (&$xml): void {
                $xml = YealinkPhonebook::render(
                    [book('local:common', "Weird \xB1\x31 name", [['name' => "\xF0\x28\x8C\x28", 'telephone' => '9001']])],
                    'Yealink'
                );
            }
        );
        check(!str_contains($xml, 'Warning') && !str_contains($xml, 'Deprecated'), 'Тело не должно содержать текст предупреждений');
        strictXml($xml);
    }
);

test(
    'B12: scrub — границы Unicode и не-символы XML',
    static function (): void {
        loadClass();
        // Не-символы U+FFFE/U+FFFF: корректный UTF-8, запрещены XML 1.0 — удаляются.
        check(YealinkPhonebook::scrub("\u{FFFE}\u{FFFF}") === '', 'U+FFFE/U+FFFF должны удаляться');
        // Граничные допустимые кодовые точки сохраняются.
        foreach (["\u{7F}", "\u{80}", "\u{7FF}", "\u{800}", "\u{D7FF}", "\u{E000}", "\u{FFFD}", "\u{10000}", "\u{10FFFF}"] as $valid) {
            check(YealinkPhonebook::scrub($valid) === $valid, 'Допустимый символ должен сохраняться: ' . bin2hex($valid));
        }
        // Overlong-последовательности и значения за U+10FFFF — одна замена U+FFFD.
        check(YealinkPhonebook::scrub("\xF0\x80\x80\x80") === "\u{FFFD}", 'Overlong 4-байтовая → U+FFFD');
        check(YealinkPhonebook::scrub("\xF4\x90\x80\x80") === "\u{FFFD}", 'За U+10FFFF → U+FFFD');
        check(YealinkPhonebook::scrub("\xE0\x80\x80") === "\u{FFFD}", 'Overlong 3-байтовая → U+FFFD');
        // Суррогаты в UTF-8 недопустимы.
        check(YealinkPhonebook::scrub("\xED\xA0\x80") === "\u{FFFD}", 'Суррогат D800 → U+FFFD');
        // Все проблемные символы сразу в рендере — документ остаётся валидным.
        $xml = YealinkPhonebook::render(
            [book('local:common', "T\u{FFFE}x", [['name' => "A\xF0\x80\x80\x80B\u{10FFFF}", 'telephone' => '9001']])],
            'Yealink'
        );
        $root = strictXml($xml);
        check((string)$root->Menu[0]['Name'] === 'Tx', 'Не-символ удалён из названия книги');
        check((string)$root->Menu[0]->Unit[0]['Name'] === "A\u{FFFD}B\u{10FFFF}", 'Overlong заменён, граница Unicode сохранена');
    }
);

// ---------------------------------------------------------------------------
// C. Контракт import(): чтение ответов других АТС
// ---------------------------------------------------------------------------

const LEGACY_FLAT = "<YealinkIPPhoneDirectory>\n\t<Title>Remote Office</Title>\n"
    . "\t<DirectoryEntry>\n\t\t<Name>User R</Name>\n\t\t<Telephone>7001</Telephone>\n\t</DirectoryEntry>\n"
    . "\t<DirectoryEntry>\n\t\t<Name>User S</Name>\n\t\t<Telephone>7002</Telephone>\n\t</DirectoryEntry>\n"
    . "</YealinkIPPhoneDirectory>\n";

test(
    'C1: легаси-ответ одним корнем IPPhoneDirectory без декларации',
    static function (): void {
        loadClass();
        $books = YealinkPhonebook::import(LEGACY_FLAT, 'otherpbx:1', 'Remote');
        check(count($books) === 1, 'Одна книга');
        check($books[0]['title'] === 'Remote Office', 'Заголовок из Title');
        check(count($books[0]['entries']) === 2, 'Оба контакта');
        check($books[0]['entries'][0] === ['name' => 'User R', 'telephone' => '7001'], 'Контакт 1');
        check(str_starts_with($books[0]['id'], 'otherpbx:1:'), 'id префиксован источником');
    }
);

test(
    'C2: легаси-корень с произвольным именем и без Title — fallback-заголовок',
    static function (): void {
        loadClass();
        $legacy = "<OfficeIPPhoneDirectory>\n"
            . "\t<DirectoryEntry>\n\t\t<Name>User R</Name>\n\t\t<Telephone>7001</Telephone>\n\t</DirectoryEntry>\n"
            . "</OfficeIPPhoneDirectory>\n";
        $books = YealinkPhonebook::import($legacy, 'otherpbx:2', 'Head Office');
        check(count($books) === 1 && $books[0]['title'] === 'Head Office', 'Заголовок из fallback');
        check(count($books[0]['entries']) === 1, 'Контакт прочитан');
    }
);

test(
    'C3: новый формат с декларацией и группами',
    static function (): void {
        loadClass();
        $doc = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<YealinkIPPhoneBook>\n<Title>Remote</Title>\n"
            . "<Menu Name=\"Отдел А\"><Unit Name=\"User R\" Phone1=\"7001\" Phone2=\"\" Phone3=\"\" default_photo=\"\"/></Menu>\n"
            . "<Menu Name=\"Отдел B\"><Unit Name=\"User S\" Phone1=\"7002\" Phone2=\"\" Phone3=\"\" default_photo=\"\"/></Menu>\n"
            . "</YealinkIPPhoneBook>\n";
        $books = YealinkPhonebook::import($doc, 'otherpbx:3', 'Remote');
        check(count($books) === 2, 'Книга на каждый Menu');
        check($books[0]['title'] === 'Отдел А' && $books[0]['entries'][0]['telephone'] === '7001', 'Группа А');
        check($books[1]['title'] === 'Отдел B' && $books[1]['entries'][0]['name'] === 'User S', 'Группа B');
        check($books[0]['id'] !== $books[1]['id'], 'id групп различаются');
    }
);

test(
    'C4: новый формат без декларации (дочерний ответ новой АТС)',
    static function (): void {
        loadClass();
        $doc = "<YealinkIPPhoneBook>\n<Title>Remote</Title>\n"
            . "<Menu Name=\"Sales\"><Unit Name=\"User R\" Phone1=\"7001\"/></Menu>\n"
            . "</YealinkIPPhoneBook>\n";
        $books = YealinkPhonebook::import($doc, 'otherpbx:4', 'Remote');
        check(count($books) === 1 && count($books[0]['entries']) === 1, 'Дочерний ответ читается');
    }
);

test(
    'C5: несколько легаси-корней (старый формат необновлённой АТС) импортируются',
    static function (): void {
        loadClass();
        $multiRoot = "<SalesIPPhoneDirectory>\n\t<Title>Sales</Title>\n"
            . "\t<DirectoryEntry>\n\t\t<Name>User S</Name>\n\t\t<Telephone>7101</Telephone>\n\t</DirectoryEntry>\n"
            . "</SalesIPPhoneDirectory>\n"
            . "<SupportIPPhoneDirectory>\n\t<Title>Support</Title>\n"
            . "\t<DirectoryEntry>\n\t\t<Name>User P</Name>\n\t\t<Telephone>7102</Telephone>\n\t</DirectoryEntry>\n"
            . "</SupportIPPhoneDirectory>\n";
        $books = YealinkPhonebook::import($multiRoot, 'otherpbx:5', 'Remote');
        check(count($books) === 2, 'Книга на каждый легаси-корень');
        check($books[0]['title'] === 'Sales' && $books[0]['entries'][0]['telephone'] === '7101', 'Книга Sales');
        check($books[1]['title'] === 'Support' && $books[1]['entries'][0]['name'] === 'User P', 'Книга Support');
        check($books[0]['id'] !== $books[1]['id'], 'id книг различаются');
    }
);

test(
    'C5b: числовой корень и мусор между книгами по-прежнему отвергаются',
    static function (): void {
        loadClass();
        // Реальный дефект старого генератора: числовое имя группы → невалидный тег.
        $numeric = "<245AgfdgIPPhoneDirectory>\n\t<Title>245 Agfdg</Title>\n"
            . "\t<DirectoryEntry>\n\t\t<Name>User A</Name>\n\t\t<Telephone>9001</Telephone>\n\t</DirectoryEntry>\n"
            . "</245AgfdgIPPhoneDirectory>\n";
        foreach ([$numeric, "garbage text<YealinkIPPhoneDirectory><Title>A</Title></YealinkIPPhoneDirectory>"] as $i => $broken) {
            try {
                YealinkPhonebook::import($broken, "otherpbx:5b.$i", 'Remote');
                check(false, "Ожидалось исключение для входа #$i");
            } catch (UnexpectedValueException $e) {
                check(str_starts_with($e->getMessage(), 'otherpbx:5b'), 'Диагностика с идентификатором источника');
            }
        }
    }
);

test(
    'C6: DOCTYPE/ENTITY (XXE) отвергается, ничего не печатается в вывод',
    static function (): void {
        loadClass();
        $evil = "<!DOCTYPE YealinkIPPhoneDirectory [<!ENTITY xxe SYSTEM \"http://127.0.0.1/steal\">]>\n"
            . "<YealinkIPPhoneDirectory><DirectoryEntry><Name>&xxe;</Name><Telephone>1</Telephone></DirectoryEntry></YealinkIPPhoneDirectory>";
        ob_start();
        try {
            YealinkPhonebook::import($evil, 'otherpbx:6', 'Remote');
            check(false, 'Ожидалось исключение для DOCTYPE/ENTITY');
        } catch (UnexpectedValueException $e) {
            // ожидаемо
        } finally {
            $leaked = (string)ob_get_clean();
        }
        check($leaked === '', 'Ошибки парсера не должны попадать в вывод');
    }
);

test(
    'C7: пустой, пробельный и мусорный ответы отвергаются',
    static function (): void {
        loadClass();
        foreach (['', "  \n\t", 'not xml at all <', "<html><body>404</body></html>"] as $i => $garbage) {
            try {
                YealinkPhonebook::import($garbage, "otherpbx:7.$i", 'Remote');
                check(false, "Ожидалось исключение для входа #$i");
            } catch (UnexpectedValueException $e) {
                // ожидаемо
            }
        }
    }
);

test(
    'C11: DOCTYPE в UTF-16 отвергается (обход через кодировку закрыт)',
    static function (): void {
        loadClass();
        $ascii = '<?xml version="1.0" encoding="UTF-16"?>'
            . '<!DOCTYPE YealinkIPPhoneDirectory [<!ENTITY xxe SYSTEM "http://127.0.0.1/">]>'
            . '<YealinkIPPhoneDirectory><DirectoryEntry><Name>&xxe;</Name><Telephone>1</Telephone></DirectoryEntry></YealinkIPPhoneDirectory>';
        $utf16le = "\xFF\xFE";
        foreach (str_split($ascii) as $char) {
            $utf16le .= $char . "\x00";
        }
        try {
            YealinkPhonebook::import($utf16le, 'otherpbx:13', 'Remote');
            check(false, 'Ожидалось исключение для UTF-16 документа с DOCTYPE');
        } catch (UnexpectedValueException $e) {
            check(str_contains($e->getMessage(), 'UTF-8'), 'Причина — кодировка');
        }
    }
);

test(
    'C8: неподдерживаемый корень отвергается',
    static function (): void {
        loadClass();
        try {
            YealinkPhonebook::import('<AddressBook><Contact/></AddressBook>', 'otherpbx:8', 'Remote');
            check(false, 'Ожидалось исключение для чужого корня');
        } catch (UnexpectedValueException $e) {
            // ожидаемо
        }
    }
);

test(
    'C9: одинаковые названия книг разных источников не перезаписываются',
    static function (): void {
        loadClass();
        $books = array_merge(
            YealinkPhonebook::import(LEGACY_FLAT, 'otherpbx:10', 'Remote'),
            YealinkPhonebook::import(LEGACY_FLAT, 'otherpbx:11', 'Remote')
        );
        check(count($books) === 2, 'Обе книги сохранены');
        $ids = array_column($books, 'id');
        check($ids[0] !== $ids[1], 'id различаются по источнику');
    }
);

test(
    'C10: round-trip — импорт собственного дочернего рендера',
    static function (): void {
        loadClass();
        $source = [
            book('local:group:1', 'Отдел А', [['name' => 'User A & B', 'telephone' => '9001']]),
            book('local:common', 'Yealink', [['name' => 'User C', 'telephone' => '9003']]),
        ];
        $child = YealinkPhonebook::render($source, 'Office', false);
        $books = YealinkPhonebook::import($child, 'otherpbx:12', 'Office');
        check(count($books) === 2, 'Обе книги импортированы');
        check($books[0]['entries'][0]['name'] === 'User A & B', 'Экранированные данные прошли round-trip');
        check($books[1]['entries'][0]['telephone'] === '9003', 'Контакты общей книги прочитаны');
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
