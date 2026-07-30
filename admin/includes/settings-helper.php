<?php

/** Upserts one site_settings field. $table/$columns args elsewhere in this
 * file are always literal strings the calling admin page hardcodes -- never
 * user input -- so building their SQL by concatenation is safe here. */
function upsert_setting(string $page, string $section, string $key, string $value): void
{
    $stmt = get_db()->prepare(
        'INSERT INTO site_settings (page, section, field_key, field_value) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)'
    );
    $stmt->execute([$page, $section, $key, $value]);
}

/** Upserts every field in $fields (field_key => value) for one page/section. */
function save_settings_section(string $page, string $section, array $fields): void
{
    foreach ($fields as $key => $value) {
        upsert_setting($page, $section, $key, (string) $value);
    }
}

/**
 * Replaces every row in a small repeater table ($table is always a literal
 * name the caller hardcodes, e.g. 'stats') with $rows, each an ordered list
 * of values matching $columns. This is the whole-table DELETE+INSERT
 * strategy: safe because these tables are small (a handful of rows) and
 * nothing else references them by id, so there's no update-in-place
 * bookkeeping to get right.
 */
function replace_repeater_rows(string $table, array $columns, array $rows): void
{
    $pdo = get_db();
    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM ' . $table);

    if ($rows !== []) {
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $stmt = $pdo->prepare(
            'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES ' . $placeholders
        );

        foreach ($rows as $row) {
            $stmt->execute($row);
        }
    }

    $pdo->commit();
}
