<?php
namespace App\Core;

abstract class Model
{
    protected static $table_name;
    protected static $primary_key;

    public static function find($id)
    {
        $table_name = static::$table_name;
        $primary_key = static::$primary_key;

        $modelSQL = Database::getInstance()->prepare("SELECT * FROM {$table_name} WHERE {$primary_key} = :primary_key");

        $modelSQL->execute([
            ":primary_key" => $id
        ]);

        return $modelSQL->fetch(\PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        if (count($data) < 1) {
            return false;
        }

        $table_name = static::$table_name;

        $sqladd = Model::prepareInsertSql($data);

        $modelSQL = Database::getInstance()->prepare("INSERT INTO {$table_name} {$sqladd}");

        $modelSQL->execute($data);

        return ($modelSQL->rowCount() > 0);
    }

    public static function update($data, $where)
    {
        if (count($data) < 1 || count($where) < 1) {
            return false;
        }

        $table_name = static::$table_name;

        $sqladd = Model::prepareUpdateSql($data);
        $whereadd = Model::prepareWhereSql($where);

        $modelSQL = Database::getInstance()->prepare("UPDATE {$table_name} SET {$sqladd} WHERE {$whereadd}");

        $modelSQL->execute(array_merge($data, $where));

        return ($modelSQL->rowCount() > 0);
    }

    public static function delete($id)
    {
        $table_name = static::$table_name;
        $primary_key = static::$primary_key;

        $modelSQL = Database::getInstance()->prepare("DELETE FROM {$table_name} WHERE {$primary_key} = :primary_key");

        $modelSQL->execute([
            ":primary_key" => $id
        ]);

        return ($modelSQL->rowCount() > 0);
    }

    protected static function prepareInsertSql($data)
    {
        $keys = array_keys($data);
        $keys_value = preg_filter("/^/", ":", $keys);

        $keys = implode(",", $keys);
        $keys_value = implode(",", $keys_value);

        return "({$keys}) VALUES ($keys_value)";
    }

    protected static function prepareUpdateSql($data)
    {
        $keys = array_keys($data);

        $sql = "";

        foreach ($keys as $key => $value) {
            if (array_key_first($keys) != $key) {
                $sql .= ", ";
            }

            $sql .= "{$value} = :{$value}";
        }

        return $sql;
    }

    protected static function prepareWhereSql($data)
    {
        $keys = array_keys($data);

        $sql = "";

        foreach ($keys as $key => $value) {
            if (array_key_first($keys) != $key) {
                $sql .= " AND ";
            }

            $sql .= "{$value} = :{$value}";
        }

        return $sql;
    }
}