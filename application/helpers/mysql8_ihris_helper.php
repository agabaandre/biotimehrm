<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MySQL 8 helpers (ONLY_FULL_GROUP_BY, TRIM in GROUP BY/ORDER BY).
 */

if (!function_exists('mysql8_quote_ident')) {
    /**
     * @param string $name
     * @return string
     */
    function mysql8_quote_ident($name)
    {
        return '`' . str_replace('`', '``', trim((string) $name)) . '`';
    }
}

if (!function_exists('mysql8_trim_expr')) {
    /**
     * @param string $column
     * @return string
     */
    function mysql8_trim_expr($column)
    {
        return 'TRIM(' . mysql8_quote_ident($column) . ')';
    }
}

if (!function_exists('mysql8_nonempty_sql')) {
    /**
     * @param string $column
     * @return string
     */
    function mysql8_nonempty_sql($column)
    {
        $t = mysql8_trim_expr($column);
        return $t . ' IS NOT NULL AND ' . $t . " <> ''";
    }
}
