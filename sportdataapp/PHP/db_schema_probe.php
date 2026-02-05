<?php
declare(strict_types=1);

/**
 * information_schema を参照せずに、テーブル/カラムの有無を "実クエリの成功可否" で判定する。
 * （一部の MariaDB/Windows 環境で information_schema がクラッシュトリガになっているための回避策）
 */

function sportdata_mysqli_table_exists(mysqli $link, string $tableName): bool
{
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    if ($tableName === '') {
        return false;
    }

    $sql = "SELECT 1 FROM `{$tableName}` LIMIT 0";
    $res = @mysqli_query($link, $sql);
    if ($res === false) {
        return false;
    }

    mysqli_free_result($res);
    return true;
}

function sportdata_mysqli_column_exists(mysqli $link, string $tableName, string $columnName): bool
{
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    $columnName = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
    if ($tableName === '' || $columnName === '') {
        return false;
    }

    $sql = "SELECT `{$columnName}` FROM `{$tableName}` LIMIT 0";
    $stmt = @mysqli_prepare($link, $sql);
    if ($stmt === false) {
        return false;
    }

    mysqli_stmt_close($stmt);
    return true;
}
