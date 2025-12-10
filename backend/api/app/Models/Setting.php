<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        try {
            if (!Schema::hasTable('settings')) {
                return $default;
            }
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function set($key, $value)
    {
        try {
            if (!Schema::hasTable('settings')) {
                return false;
            }
            self::updateOrCreate(['key' => $key], ['value' => $value]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
