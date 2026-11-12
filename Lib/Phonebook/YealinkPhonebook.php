<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\Phonebook;

use SimpleXMLElement;
use UnexpectedValueException;
use XMLWriter;

/**
 * Сериализация телефонной книги Yealink без зависимостей от Phalcon и БД.
 *
 * Формат — официальный Remote Phone Book с группировкой (Yealink Phonebook
 * Generation Tool User Guide): единственный корень YealinkIPPhoneBook,
 * группы Menu/@Name, контакты Unit/@Name + Unit/@Phone1. Названия книг и
 * контактов передаются только текстом и атрибутами, поэтому числовые,
 * кириллические и спецсимвольные названия не влияют на структуру XML.
 *
 * Плоский вариант с корнем *IPPhoneDirectory — легаси; он не отдаётся, но
 * читается методом import() из ответов необновлённых АТС.
 */
final class YealinkPhonebook
{
    /**
     * Нормализованная книга: ['id' => внутренний ключ, 'title' => исходное
     * название, 'entries' => [['name' => ..., 'telephone' => ...], ...]].
     * id используется только для слияния источников, в XML не попадает.
     *
     * @param array<int, array> $books
     */
    public static function render(array $books, string $title, bool $withXmlDeclaration = true): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString("\t");
        if ($withXmlDeclaration) {
            // Корневой ответ для телефона; дочерний ответ (?name=) отдаётся без
            // декларации — старые АТС-агрегаторы проверяют фрагмент обёрткой и
            // отвергают декларацию внутри неё.
            $writer->startDocument('1.0', 'UTF-8');
        }

        $writer->startElement('YealinkIPPhoneBook');
        $writer->writeElement('Title', self::scrub($title));
        foreach ($books as $book) {
            $entries = $book['entries'] ?? [];
            if (!is_array($entries) || $entries === []) {
                continue;
            }
            $writer->startElement('Menu');
            $writer->writeAttribute('Name', self::scrub((string)($book['title'] ?? '')));
            foreach ($entries as $entry) {
                $writer->startElement('Unit');
                $writer->writeAttribute('Name', self::scrub((string)($entry['name'] ?? '')));
                $writer->writeAttribute('Phone1', self::scrub((string)($entry['telephone'] ?? '')));
                // Пустые Phone2/Phone3/default_photo — как в XML официального
                // Phonebook Generation Tool.
                $writer->writeAttribute('Phone2', '');
                $writer->writeAttribute('Phone3', '');
                $writer->writeAttribute('default_photo', '');
                $writer->endElement();
            }
            $writer->endElement();
        }
        $writer->endElement();
        $writer->endDocument();

        return $writer->flush();
    }

    /**
     * Готовит строку к использованию в XML: заменяет недопустимые UTF-8
     * последовательности на U+FFFD, удаляет символы, запрещённые в XML 1.0
     * (управляющие, кроме \t \r \n, и не-символы U+FFFE/U+FFFF). Чистый PHP,
     * без mbstring/iconv — модуль работает на минимальных сборках PHP MikoPBX.
     */
    public static function scrub(string $raw): string
    {
        $result = '';
        $length = strlen($raw);
        for ($i = 0; $i < $length; $i++) {
            $byte = ord($raw[$i]);
            if ($byte < 0x80) {
                // XML 1.0 из управляющих допускает только \t \r \n.
                if ($byte === 0x09 || $byte === 0x0A || $byte === 0x0D || $byte >= 0x20) {
                    $result .= $raw[$i];
                }
                continue;
            }

            $seqLength = match (true) {
                $byte >= 0xC2 && $byte <= 0xDF => 2,
                $byte >= 0xE0 && $byte <= 0xEF => 3,
                $byte >= 0xF0 && $byte <= 0xF4 => 4,
                default => 0, // 0x80-0xBF без ведущего байта, 0xC0/0xC1 (overlong), 0xF5+
            };
            $wellFormed = $seqLength !== 0 && $i + $seqLength <= $length;
            if ($wellFormed) {
                for ($j = 1; $j < $seqLength; $j++) {
                    if ((ord($raw[$i + $j]) & 0xC0) !== 0x80) {
                        $wellFormed = false;
                        break;
                    }
                }
            }
            if (!$wellFormed) {
                // Обрыв последовательности: байт заменяем, остаток пересканируется.
                $result .= "\u{FFFD}";
                continue;
            }

            $cp = $byte & (0x7F >> $seqLength);
            for ($j = 1; $j < $seqLength; $j++) {
                $cp = ($cp << 6) | (ord($raw[$i + $j]) & 0x3F);
            }
            $valid = match ($seqLength) {
                2 => $cp >= 0x80,
                // суррогаты U+D800-U+DFFF запрещены и в UTF-8, и в XML
                3 => $cp >= 0x800 && ($cp < 0xD800 || $cp > 0xDFFF),
                4 => $cp >= 0x10000 && $cp <= 0x10FFFF,
                default => false,
            };
            if (!$valid) {
                // Байты корректны, но значение вне Unicode (overlong, за U+10FFFF).
                $result .= "\u{FFFD}";
                $i += $seqLength - 1;
                continue;
            }
            if ($cp === 0xFFFE || $cp === 0xFFFF) {
                // Не-символы U+FFFE/U+FFFF — корректный UTF-8, но запрещены в XML 1.0: удаляем.
                $i += $seqLength - 1;
                continue;
            }
            $result .= substr($raw, $i, $seqLength);
            $i += $seqLength - 1;
        }

        return $result;
    }

    /**
     * Разбирает ответ телефонной книги другой АТС и возвращает нормализованные
     * книги (структура как у render()). Понимает новый формат YealinkIPPhoneBook
     * и легаси-корни *IPPhoneDirectory — с декларацией и без. Повреждённый,
     * пустой или неподдерживаемый ответ вызывает UnexpectedValueException;
     * сообщение содержит только идентификатор источника и причину, без тела
     * ответа и контактов.
     *
     * @return array<int, array>
     */
    public static function import(string $xml, string $sourceId, string $fallbackTitle): array
    {
        $xml = ltrim($xml, "\u{FEFF} \t\n\r");
        if ($xml === '') {
            throw new UnexpectedValueException("{$sourceId}: пустой ответ телефонной книги");
        }
        if (str_contains($xml, "\x00")) {
            // Поддерживается только UTF-8: UTF-16/32 содержат NUL-байты, а в UTF-8
            // они запрещены. Заодно закрывает обход проверки DOCTYPE сменой кодировки.
            throw new UnexpectedValueException("{$sourceId}: ожидается UTF-8 без управляющих байтов");
        }
        if (stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            // Ни одному формату книги DTD не нужен; отвергаем до парсера,
            // исключая внешние сущности (XXE) целиком.
            throw new UnexpectedValueException("{$sourceId}: DTD/ENTITY в ответе запрещены");
        }

        $roots = [];
        $root = self::parseStrict($xml);
        if ($root === false) {
            // Легаси-ответ необновлённой АТС с несколькими группами —
            // последовательность корней. Служебная обёртка допускается только
            // здесь, на входе; в итоговый ответ она не попадает. Числовые имена
            // корней остаются невалидным XML и источник отвергается целиком.
            $wrapped = self::parseStrict("<mikoLegacy>{$xml}</mikoLegacy>");
            if ($wrapped === false || trim((string)$wrapped) !== '') {
                throw new UnexpectedValueException("{$sourceId}: некорректный XML");
            }
            foreach ($wrapped->children() as $child) {
                $roots[] = $child;
            }
        } else {
            $roots[] = $root;
        }

        $books = [];
        foreach ($roots as $index => $rootElement) {
            $rootName = $rootElement->getName();
            if ($rootName === 'YealinkIPPhoneBook') {
                $menuIndex = 0;
                foreach ($rootElement->Menu as $menu) {
                    $entries = [];
                    foreach ($menu->Unit as $unit) {
                        $entries[] = [
                            'name'      => trim((string)$unit['Name']),
                            'telephone' => trim((string)$unit['Phone1']),
                        ];
                    }
                    if ($entries === []) {
                        $menuIndex++;
                        continue;
                    }
                    $books[] = [
                        'id'      => "{$sourceId}:root{$index}:menu:{$menuIndex}",
                        'title'   => trim((string)$menu['Name']),
                        'entries' => $entries,
                    ];
                    $menuIndex++;
                }
            } elseif (str_ends_with($rootName, 'IPPhoneDirectory')) {
                $title = trim((string)($rootElement->Title ?? ''));
                if ($title === '') {
                    $title = $fallbackTitle;
                }
                $entries = [];
                foreach ($rootElement->DirectoryEntry as $entry) {
                    $entries[] = [
                        'name'      => trim((string)$entry->Name),
                        'telephone' => trim((string)$entry->Telephone),
                    ];
                }
                if ($entries !== []) {
                    $books[] = [
                        'id'      => "{$sourceId}:root{$index}:directory",
                        'title'   => $title,
                        'entries' => $entries,
                    ];
                }
            } else {
                throw new UnexpectedValueException("{$sourceId}: неподдерживаемый корень '{$rootName}'");
            }
        }
        if ($books === []) {
            throw new UnexpectedValueException("{$sourceId}: в ответе нет контактов");
        }

        return $books;
    }

    /**
     * Строгий разбор без сети и сущностей; восстанавливает глобальный режим libxml.
     */
    private static function parseStrict(string $xml): SimpleXMLElement|false
    {
        $previous = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();
            // Несколько корней, декларация в середине и обрыв документа
            // завершатся ошибкой разбора — конкатенация ответов недопустима.
            $parsed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return ($parsed === false || $errors !== []) ? false : $parsed;
    }
}
