<?php
namespace backend\utils;

class DateConvert
{
    public static function convertToSQL($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}