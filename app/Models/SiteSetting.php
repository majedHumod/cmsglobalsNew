<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
        'is_public',
        'is_tenant_specific'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_tenant_specific' => 'boolean',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $cacheKey = TenantCache::key('setting_' . $key);
        
        return Cache::remember($cacheKey, 7200, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Handle different types of settings
            switch ($setting->type) {
                case 'boolean':
                    return (bool) $setting->value;
                case 'integer':
                    return (int) $setting->value;
                case 'float':
                    return (float) $setting->value;
                case 'array':
                case 'json':
                    return json_decode($setting->value, true);
                default:
                    return $setting->value;
            }
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $type
     * @param string $description
     * @param bool $isPublic
     * @param bool $isTenantSpecific
     * @return SiteSetting
     */
    public static function set($key, $value, $group = 'general', $type = 'string', $description = '', $isPublic = true, $isTenantSpecific = true)
    {
        // Format the value based on type
        if ($type === 'array' || $type === 'json') {
            $value = is_array($value) ? json_encode($value) : $value;
        }
        
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
                'is_tenant_specific' => $isTenantSpecific
            ]
        );
        
        // Clear the cache for this setting
        Cache::forget(TenantCache::key('setting_' . $key));
        
        return $setting;
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return \Illuminate\Support\Collection
     */
    public static function getGroup($group)
    {
        $cacheKey = TenantCache::key('settings_group_' . $group);

        try {
            return Cache::remember($cacheKey, 7200, function () use ($group) {
                return self::where('group', $group)
                    ->select(['key', 'value', 'type'])
                    ->get()
                    ->mapWithKeys(function ($setting) {
                    // Handle different types of settings
                    $value = $setting->value;
                    switch ($setting->type) {
                        case 'boolean':
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                            break;
                        case 'integer':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'array':
                        case 'json':
                            $value = json_decode($value, true);
                            break;
                    }
                    
                    return [$setting->key => $value];
                });
            });
        } catch (\Exception $e) {
            \Log::error('Error getting settings group: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Clear the cache for a specific group
     *
     * @param string $group
     * @return void
     */
    public static function clearGroupCache($group)
    {
        Cache::forget(TenantCache::key('settings_group_' . $group));
        
        // Also clear individual setting caches in this group
        $settings = self::where('group', $group)->get();
        foreach ($settings as $setting) {
            Cache::forget(TenantCache::key('setting_' . $setting->key));
        }
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearAllCache()
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget(TenantCache::key('setting_' . $setting->key));
        }
        
        $groups = self::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget(TenantCache::key('settings_group_' . $group));
        }
    }
}