<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configs extends Model
{
    protected $table = "configs";
    protected $fillable = [
        'key',
        'value'
    ];

    public static function get(string $key, bool $decrypt = false, $default = null)
    {
        $config = self::where('key', $key)->first();
        if (!$config) return $default;

        $value = $config->value;

        if ($decrypt) {
            $value = decrypt($value);
        }

        return $value;
    }

    public static function set(string $key, $value, bool $encrypt = false)
    {
        if ($encrypt) {
            $value = encrypt($value);
        }

        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
