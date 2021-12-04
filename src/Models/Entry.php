<?php

namespace HepplerDotNet\LaravelConfigOverride\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Entry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'config_entries';

    private static $types = ['String', 'Password', 'Integer', 'Boolean', 'Array'];

    public static function create(array $attributes = []): Entry
    {
        ksort($attributes);

        $model = static::query()->create($attributes);

        return $model;
    }

    public static function getTypes(): array
    {
        return self::$types;
    }

    public function group()
    {
        return $this->belongsTo(Group::class)->withOut('entries');
    }

    public function setValueAttribute(mixed $value): void
    {
        $this->attributes['value'] = $this->setValue($value);
    }

    public function getValueAttribute(): mixed
    {
        return $this->getValue();
    }

    public function getValue(): mixed
    {
        return match ($this->getType()) {
            'String' => $this->attributes['value'],
            'Integer' => (int) $this->attributes['value'],
            'Boolean' => (bool) $this->attributes['value'],
            'Array' => explode(',', $this->attributes['value']),
            'Password' => Crypt::decrypt($this->attributes['value']),
            default => $this->attributes['value']
        };
    }

    protected static function booted(): void
    {
        // On Create, Update and Delete flush Cache
        static::created(function (): void {
            Cache::forget('laravel-configuration');
            Artisan::call('config:clear');
        });
        static::saved(function (): void {
            Cache::forget('laravel-configuration');
            Artisan::call('config:clear');
        });
        static::deleted(function (): void {
            Cache::forget('laravel-configuration');
            Artisan::call('config:clear');
        });
        static::updated(function (): void {
            Cache::forget('laravel-configuration');
            Artisan::call('config:clear');
        });
    }

    private function getType(): string
    {
        return $this->type;
    }

    private function setValue(mixed $value): mixed
    {
        return match ($this->getType()) {
            'String' => is_array($value) ? $value[0] : $value,
            'Integer' => is_array($value) ? (int) $value[0] : (int) $value,
            'Boolean' => is_array($value) ? (bool) $value[0] : (bool) $value,
            'Array' => is_array($value) ? implode(',', $value) : $value,
            'Password' => is_array($value) ? Crypt::encrypt($value[0]) : Crypt::encrypt($value),
            default => $value,
        };
    }
}
