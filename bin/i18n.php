#!/usr/bin/env php
<?php

/**
 * phpwcms content management system
 *
 * Backend language tooling: keeps the language files in sync with the
 * strings the backend code actually uses.
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("bin/i18n.php is a command line tool.\n");
}

const BASE_LANG = 'en';
const LANG_ROOT = 'include/inc_lang/backend';
const CODE_LANG = 'include/inc_lang/code.lang.inc.php';
const MODULE_ROOT = 'include/inc_module';

/** Directories that never hold backend strings or backend code. */
const SKIP_DIRS = [
    'include/inc_lang',
    'include/vendor',
    'include/inc_ext',
    'include/inc_js',
    'include/inc_tmp',
    'node_modules',
    'skills',
    '.git',
    '.phpstan',
    'public',
    'bin',
];

/** Backend code that renders language strings. */
const CODE_ROOTS = [
    'include/inc_tmpl',
    'include/inc_lib',
    'include/inc_act',
    'include/inc_help',
    'include/inc_module',
];

const MAX_LISTED = 25;

function root(): string
{
    return dirname(__DIR__);
}

function path(string $relative): string
{
    return root() . '/' . $relative;
}

/**
 * All installed backend languages, sorted by code.
 *
 * @return list<string>
 */
function backend_languages(): array
{
    $languages = [];
    foreach (glob(path(LANG_ROOT) . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
        if ($directory !== false) {
            $languages[] = basename($directory);
        }
    }
    sort($languages);

    return $languages;
}

function language_file(string $language, string $part = 'main'): string
{
    return path(LANG_ROOT . '/' . $language . '/lang.' . ($part === 'ext' ? 'ext.inc' : 'inc') . '.php');
}

/**
 * Definition lines of a language file.
 *
 * @return list<array{key: string, line: int, array: bool}>
 */
function definition_entries(string $file): array
{
    if (!is_file($file)) {
        return [];
    }

    $entries = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $index => $line) {
        if (!preg_match('/^\s*\$BL\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*=/', $line, $match)) {
            continue;
        }
        $entries[] = [
            'key' => $match[1],
            'line' => $index + 1,
            'array' => (bool) preg_match('/=\s*(array\s*\(|\[)/', $line),
        ];
    }

    return $entries;
}

/**
 * Strings of one backend language.
 *
 * @return array{keys: array<string, int>, duplicates: array<string, list<int>>, arrays: array<string, bool>}
 */
function language_definitions(string $language): array
{
    $keys = [];
    $positions = [];
    $arrays = [];

    foreach (['main', 'ext'] as $part) {
        foreach (definition_entries(language_file($language, $part)) as $entry) {
            $keys[$entry['key']] = $entry['line'];
            $positions[$entry['key']][] = $entry['line'];
            if ($entry['array']) {
                $arrays[$entry['key']] = true;
            }
        }
    }

    $duplicates = [];
    foreach ($positions as $key => $lines) {
        if (count($lines) > 1) {
            $duplicates[$key] = $lines;
        }
    }
    ksort($duplicates);

    return ['keys' => $keys, 'duplicates' => $duplicates, 'arrays' => $arrays];
}

/**
 * Base language strings, including the language-name list.
 *
 * @return array{keys: array<string, int>, duplicates: array<string, list<int>>, arrays: array<string, bool>}
 */
function base_definitions(): array
{
    $definitions = language_definitions(BASE_LANG);

    foreach (definition_entries(path(CODE_LANG)) as $entry) {
        $definitions['keys'][$entry['key']] = $entry['line'];
    }
    ksort($definitions['keys']);

    return $definitions;
}

/**
 * Every backend PHP/HTML file that can reference a language string.
 *
 * @return list<string> absolute paths
 */
function code_files(): array
{
    $files = [];
    foreach (glob(path('*.php')) ?: [] as $file) {
        $files[] = $file;
    }

    foreach (CODE_ROOTS as $directory) {
        $base = path($directory);
        if (!is_dir($base)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (!$item instanceof SplFileInfo || !$item->isFile()) {
                continue;
            }
            $file = $item->getPathname();
            if (!preg_match('/\.(php|html?|inc)$/i', $file)) {
                continue;
            }

            $relative = ltrim(substr($file, strlen(root())), '/');
            foreach (SKIP_DIRS as $skip) {
                if (str_starts_with($relative, $skip . '/')) {
                    continue 2;
                }
            }
            // language definitions, not code that uses strings
            if (str_contains($relative, '/lang/') || preg_match('/\.lang(\.inc)?\.php$/', $relative)) {
                continue;
            }

            $files[] = $file;
        }
    }

    sort($files);

    return $files;
}

/**
 * Strings referenced by the code.
 *
 * @param list<string> $files
 * @param string $variable 'BL' or 'BLM'
 * @return array{used: array<string, string>, arrays: array<string, bool>, assigned: array<string, bool>, dynamic: int, quoted: array<string, bool>}
 */
function scan_usage(array $files, string $variable): array
{
    $used = [];
    $arrays = [];
    $assigned = [];
    $quoted = [];
    $dynamic = 0;

    $prefix = '/\$' . $variable . '\[\s*[\'"]([^\'"]+)[\'"]\s*\]';

    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content === false) {
            continue;
        }
        $relative = ltrim(substr($file, strlen(root())), '/');

        if (preg_match_all($prefix . '/', $content, $matches)) {
            foreach ($matches[1] as $key) {
                if (!isset($used[$key])) {
                    $used[$key] = $relative;
                }
            }
        }

        // $BL['key'][...] and $BL['key'] = array(...) are array values, not strings
        foreach ([$prefix . '\s*\[/', $prefix . '\s*=\s*(?:array\s*\(|\[)/'] as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    $arrays[$key] = true;
                }
            }
        }

        // values the code creates at runtime, so the language file need not define them
        if (preg_match_all($prefix . '\s*=(?!=)/', $content, $matches)) {
            foreach ($matches[1] as $key) {
                $assigned[$key] = true;
            }
        }

        // strings that appear as plain literals, e.g. in an array of key names
        if (preg_match_all('/[\'"]([A-Za-z0-9_]{2,})[\'"]/', $content, $literals)) {
            foreach ($literals[1] as $literal) {
                $quoted[$literal] = true;
            }
        }

        $dynamic += (int) preg_match_all('/\$' . $variable . '\[\s*[^\'"\s\]]/', $content);
    }

    return [
        'used' => $used,
        'arrays' => $arrays,
        'assigned' => $assigned,
        'dynamic' => $dynamic,
        'quoted' => $quoted,
    ];
}

/**
 * Module name of a file inside include/inc_module, null otherwise.
 */
function module_of(string $file): ?string
{
    $relative = ltrim(substr($file, strlen(root())), '/');
    if (!str_starts_with($relative, MODULE_ROOT . '/')) {
        return null;
    }
    $rest = substr($relative, strlen(MODULE_ROOT) + 1);
    $slash = strpos($rest, '/');

    return $slash === false ? null : substr($rest, 0, $slash);
}

/**
 * @return array{keys: array<string, int>, duplicates: array<string, list<int>>}
 */
function module_definitions(string $module): array
{
    $file = path(MODULE_ROOT . '/' . $module . '/lang/' . BASE_LANG . '.lang.php');
    if (!is_file($file)) {
        return ['keys' => [], 'duplicates' => []];
    }

    $keys = [];
    $positions = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $index => $line) {
        if (!preg_match('/^\s*\$BLM\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*=/', $line, $match)) {
            continue;
        }
        $keys[$match[1]] = $index + 1;
        $positions[$match[1]][] = $index + 1;
    }
    ksort($keys);

    $duplicates = [];
    foreach ($positions as $key => $lines) {
        if (count($lines) > 1) {
            $duplicates[$key] = $lines;
        }
    }

    return ['keys' => $keys, 'duplicates' => $duplicates];
}

/**
 * Values of a base language file, read by executing it.
 *
 * @return array<string, string>
 */
function base_values(string $part): array
{
    $file = language_file(BASE_LANG, $part);
    if (!is_file($file)) {
        return [];
    }

    $BL = [];
    require $file;

    $values = [];
    foreach ($BL as $key => $value) {
        if (is_string($value)) {
            $values[(string) $key] = $value;
        }
    }

    return $values;
}

/**
 * Quoting and alignment used by an existing language file.
 *
 * @return array{quote: string, align: int}
 */
function file_style(string $file): array
{
    $single = 0;
    $double = 0;
    $columns = [];

    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (!preg_match('/^\s*\$BL\[\s*[\'"][^\'"]+[\'"]\s*\]\s*=/', $line)) {
            continue;
        }
        $double += (int) preg_match('/=\s*"/', $line);
        $single += (int) preg_match('/=\s*\'/', $line);
        if (preg_match('/^(\$BL\[[^\]]+\])(\s{2,})=/', $line, $match)) {
            $columns[] = strlen($match[1]);
        }
    }

    $align = 0;
    if ($columns !== []) {
        $counts = array_count_values($columns);
        arsort($counts);
        $align = (int) array_key_first($counts);
    }

    return ['quote' => $double > $single ? '"' : "'", 'align' => $align];
}

function php_string(string $value, string $quote): string
{
    if ($quote === "'") {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    return '"' . str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value) . '"';
}

/**
 * @param list<array{key: string, value: string}> $entries
 */
function append_entries(string $file, array $entries, string $comment): void
{
    if ($entries === []) {
        return;
    }

    $style = file_style($file);
    $lines = [];
    $lines[] = '';
    $lines[] = $comment;
    foreach ($entries as $entry) {
        $prefix = "\$BL['" . $entry['key'] . "']";
        $value = php_string($entry['value'], $style['quote']);
        if ($style['align'] > strlen($prefix)) {
            $lines[] = str_pad($prefix, $style['align']) . '= ' . $value . ';';

            continue;
        }
        $lines[] = $prefix . ' = ' . $value . ';';
    }

    $content = file_get_contents($file);
    if ($content === false) {
        return;
    }
    file_put_contents($file, rtrim($content, "\n") . "\n" . implode("\n", $lines) . "\n");
}

/**
 * @param list<string> $items
 */
function print_list(array $items, string $indent = '    '): void
{
    foreach (array_slice($items, 0, MAX_LISTED) as $item) {
        echo $indent . $item . "\n";
    }
    if (count($items) > MAX_LISTED) {
        echo $indent . '... and ' . (count($items) - MAX_LISTED) . " more\n";
    }
}

/**
 * @param array<string, mixed> $options
 * @return array<string, string> problems
 */
function collect_state(array $options): array
{
    $languages = backend_languages();
    $base = base_definitions();
    $files = code_files();
    $usage = scan_usage($files, 'BL');

    $modules = [];
    foreach ($files as $file) {
        $module = module_of($file);
        if ($module !== null) {
            $modules[$module][] = $file;
        }
    }
    ksort($modules);

    $moduleUsage = [];
    foreach (array_keys($modules) as $module) {
        $moduleUsage[$module] = scan_usage($modules[$module], 'BLM');
    }

    return [
        'languages' => $languages,
        'base' => $base,
        'files' => $files,
        'usage' => $usage,
        'modules' => $moduleUsage,
    ];
}

/**
 * @param array{0: string, 1: array<string, string>} $arguments
 */
function command_check(array $options): int
{
    $state = collect_state($options);
    $base = $state['base'];
    $usage = $state['usage'];

    $arrayKeys = $usage['arrays'] + $usage['assigned'];
    $usedKeys = array_diff_key($usage['used'], $arrayKeys);
    ksort($usedKeys);

    $missing = array_diff_key($usedKeys, $base['keys']);
    $unused = array_diff_key(language_definitions(BASE_LANG)['keys'], $usage['used']);
    $unused = array_diff_key($unused, $usage['quoted']);

    echo "i18n check\n";
    printf(
        "  base language %s: %d strings defined, %d referenced by backend code\n",
        BASE_LANG,
        count($base['keys']),
        count($usedKeys)
    );

    $problems = 0;

    if ($missing === []) {
        echo "  missing in " . BASE_LANG . ": 0\n";
    } else {
        $problems += count($missing);
        echo "  missing in " . BASE_LANG . ": " . count($missing) . "\n";
        $list = [];
        foreach ($missing as $key => $file) {
            $list[] = $key . '  (' . $file . ')';
        }
        print_list($list);
    }

    $moduleMissing = [];
    foreach ($state['modules'] as $module => $moduleUsage) {
        $definitions = module_definitions((string) $module);
        $used = array_diff_key($moduleUsage['used'], $moduleUsage['arrays'] + $moduleUsage['assigned']);
        foreach (array_diff_key($used, $definitions['keys']) as $key => $file) {
            $moduleMissing[] = $module . ': ' . $key . '  (' . $file . ')';
        }
    }
    $modulesChecked = count($state['modules']);
    if ($moduleMissing === []) {
        echo '  module strings: ' . $modulesChecked . " modules, 0 missing\n";
    } else {
        $problems += count($moduleMissing);
        echo '  module strings: ' . $modulesChecked . ' modules, ' . count($moduleMissing) . " missing\n";
        print_list($moduleMissing);
    }

    $duplicates = [];
    foreach (array_merge([BASE_LANG], $state['languages']) as $language) {
        $duplicates[$language] = count(language_definitions((string) $language)['duplicates']);
    }
    $duplicates = array_filter($duplicates);
    if ($duplicates === []) {
        echo "  duplicate strings: none\n";
    } else {
        $list = [];
        foreach (array_slice($duplicates, 0, 6, true) as $language => $count) {
            $list[] = $language . ': ' . $count;
        }
        if (count($duplicates) > 6) {
            $list[] = '...';
        }
        echo '  duplicate strings: ' . count($duplicates) . ' file(s) - ' . implode(', ', $list) . "\n";
        echo "    (the later definition wins; run 'i18n.php prune --lang=xx' for details)\n";
        if (!empty($options['strict'])) {
            $problems += count($duplicates);
        }
    }

    if ($unused !== []) {
        printf("  unused in %s: %d  (see 'i18n.php prune')\n", BASE_LANG, count($unused));
    }

    if ($usage['dynamic'] > 0) {
        printf("  dynamic lookups: %d (cannot be validated statically)\n", $usage['dynamic']);
    }

    if ($problems === 0) {
        echo "OK\n";

        return 0;
    }

    echo "FAILED: " . $problems . " problem(s)\n";

    return 1;
}

function command_coverage(array $options): int
{
    $state = collect_state($options);
    $usage = $state['usage'];
    $usedKeys = array_diff_key($usage['used'], $usage['arrays'] + $usage['assigned']);
    $total = count($usedKeys);
    $baseKeys = count(language_definitions(BASE_LANG)['keys']);

    echo 'backend language coverage (base: ' . BASE_LANG . ', '
        . $baseKeys . ' strings, ' . $total . " referenced by code)\n\n";
    printf("%-4s %6s %9s %8s %9s\n", 'lang', 'keys', 'of base', 'missing', 'coverage');

    $weak = [];
    foreach ($state['languages'] as $language) {
        $definitions = language_definitions((string) $language);
        $keys = count($definitions['keys']);
        $missing = count(array_diff_key($usedKeys, $definitions['keys']));
        $coverage = $total > 0 ? (($total - $missing) / $total) * 100 : 100.0;
        printf(
            "%-4s %6d %8.1f%% %8d %8.1f%%\n",
            $language,
            $keys,
            $baseKeys > 0 ? ($keys / $baseKeys) * 100 : 0.0,
            $missing,
            $coverage
        );
        if ($coverage < 95.0) {
            $weak[] = $language;
        }
    }

    echo "\n";
    if ($weak === []) {
        echo "every language covers at least 95% of the referenced strings\n";
    } else {
        echo 'below 95% coverage: ' . implode(', ', $weak) . "\n";
    }

    return 0;
}

function command_sync(array $options): int
{
    $language = $options['lang'] ?? null;
    if ($language === null || $language === '') {
        fwrite(STDERR, "sync needs a target language, e.g. --lang=de\n");

        return 2;
    }
    if (!in_array($language, backend_languages(), true)) {
        fwrite(STDERR, 'unknown backend language: ' . $language . "\n");

        return 2;
    }

    $values = ['main' => base_values('main'), 'ext' => base_values('ext')];
    $definitions = language_definitions($language);
    $state = collect_state($options);
    $usedKeys = array_diff_key($state['usage']['used'], $state['usage']['arrays'] + $state['usage']['assigned']);
    $base = $state['base']['keys'];

    $missing = [];
    foreach (array_keys($usedKeys) as $key) {
        if (isset($definitions['keys'][$key])) {
            continue;
        }
        $part = isset($values['main'][$key]) ? 'main' : (isset($values['ext'][$key]) ? 'ext' : null);
        if ($part === null) {
            // referenced but not part of the base language: nothing to copy
            continue;
        }
        $missing[$part][$key] = $values[$part][$key];
    }

    $dryRun = !empty($options['dry-run']);
    $added = 0;
    foreach (['main', 'ext'] as $part) {
        if (empty($missing[$part])) {
            continue;
        }
        $file = language_file($language, $part);
        $entries = [];
        foreach ($missing[$part] as $key => $value) {
            $entries[] = ['key' => $key, 'value' => $value];
        }
        printf(
            "%s %d missing string(s) %s %s\n",
            $dryRun ? 'would add' : 'adding',
            count($entries),
            $dryRun ? 'to' : 'to',
            ltrim(substr($file, strlen(root())), '/')
        );
        if ($dryRun) {
            foreach ($entries as $entry) {
                echo '    ' . $entry['key'] . ' = ' . $entry['value'] . "\n";
            }
        } else {
            append_entries(
                $file,
                $entries,
                '// TODO translate: added by bin/i18n.php sync, values are still English'
            );
        }
        $added += count($entries);
    }

    if ($added === 0) {
        echo $language . " is complete for every string the backend references\n";
    }

    return 0;
}

function command_prune(array $options): int
{
    $language = $options['lang'] ?? BASE_LANG;
    $languages = $language === 'all' ? backend_languages() : [$language];
    foreach ($languages as $candidate) {
        if (!in_array($candidate, backend_languages(), true)) {
            fwrite(STDERR, 'unknown backend language: ' . $candidate . "\n");

            return 2;
        }
    }

    $state = collect_state($options);
    // conservative: a key that is mentioned anywhere in the code is kept
    $usedKeys = $state['usage']['used'];
    $quoted = $state['usage']['quoted'];

    $delete = !empty($options['delete']);
    if ($delete && count($languages) > 1) {
        fwrite(STDERR, "prune --delete works on one language at a time, e.g. --lang=" . BASE_LANG . "\n");

        return 2;
    }

    $totalUnused = 0;
    foreach ($languages as $candidate) {
        $definitions = language_definitions($candidate);
        $unused = array_diff_key($definitions['keys'], $usedKeys);
        $unused = array_diff_key($unused, $quoted);
        $totalUnused += count($unused);
        if ($unused === [] && !$delete) {
            continue;
        }

        echo $candidate . ': ' . count($definitions['keys']) . ' strings, ' . count($unused) . " unused\n";
        if (!$delete) {
            print_list(array_keys($unused));

            continue;
        }

        $removed = 0;
        foreach (['main', 'ext'] as $part) {
            $file = language_file($candidate, $part);
            if (!is_file($file)) {
                continue;
            }
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                continue;
            }
            $kept = [];
            foreach ($lines as $line) {
                if (!preg_match('/^\s*\$BL\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*=/', $line, $match)
                    || !isset($unused[$match[1]])
                ) {
                    $kept[] = $line;

                    continue;
                }
                $removed++;
            }
            file_put_contents($file, implode("\n", $kept) . "\n");
        }
        echo '    removed ' . $removed . " line(s)\n";
    }

    if ($totalUnused === 0) {
        echo "no unused strings found\n";
    }

    return 0;
}

function usage(): void
{
    echo <<<TXT
phpwcms backend language tooling

Usage: php bin/i18n.php <command> [options]

Commands:
  check       Every string the backend code references exists in the base
              language. Fails when a string is missing, reports duplicates
              and unused strings.
  coverage    Per language: defined strings, missing referenced strings and
              the coverage of the strings the code uses.
  sync        Append the strings a language file is missing, with the English
              text as a placeholder marked for translation.
  prune       Report strings no code references. With --delete they are
              removed from the given language file.

Options:
  --lang=xx   target language (sync, prune; prune also accepts "all")
  --dry-run   sync: print what would be added without writing
  --delete    prune: remove the reported definition lines
  --strict    check: treat duplicate definitions as errors too

Examples:
  php bin/i18n.php check
  php bin/i18n.php coverage
  php bin/i18n.php sync --lang=fr --dry-run
  php bin/i18n.php prune --lang=en

TXT;
}

$arguments = array_slice($argv, 1);
$command = array_shift($arguments);

$options = [];
foreach ($arguments as $argument) {
    if (str_starts_with($argument, '--')) {
        $pair = explode('=', substr($argument, 2), 2);
        $options[$pair[0]] = $pair[1] ?? true;
    }
}

switch ($command) {
    case 'check':
        exit(command_check($options));
    case 'coverage':
        exit(command_coverage($options));
    case 'sync':
        exit(command_sync($options));
    case 'prune':
        exit(command_prune($options));
    case null:
    case 'help':
    case '--help':
        usage();
        exit(0);
    default:
        fwrite(STDERR, 'unknown command: ' . $command . "\n\n");
        usage();
        exit(2);
}
