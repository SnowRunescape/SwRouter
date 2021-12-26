<?php

namespace App\Core;

abstract class Model
{
    protected static $table_name;
    protected static $primary_key;

    public static function all()
    {
        $table_name = static::$table_name;

        $modelSQL = Database::getInstance()->prepare("SELECT * FROM {$table_name}");

        $modelSQL->execute();

        return $modelSQL->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        $table_name = static::$table_name;
        $primary_key = static::$primary_key;

        $data = [
            $primary_key => $id
        ];

        $whereadd = Model::prepareWhereSql($data);

        $modelSQL = Database::getInstance()->prepare("SELECT * FROM {$table_name} WHERE {$whereadd}");

        $modelSQL->execute($data);

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

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }

        $modelSQL->execute($data);

        return ($modelSQL->rowCount() > 0);
    }

    public static function update($data, $where)
    {
        if (!is_array($where)) {
            if (strlen($where) < 1) {
                return false;
            }

            $where = [
                static::$primary_key => $where
            ];
        }

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

        $data = [
            $primary_key => $id
        ];

        $whereadd = Model::prepareWhereSql($data);

        $modelSQL = Database::getInstance()->prepare("DELETE FROM {$table_name} WHERE {$whereadd}");

        $modelSQL->execute($data);

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

    protected static function prepareWhereSql(&$data)
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
