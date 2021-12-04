<?php

namespace HepplerDotNet\LaravelConfigOverride\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class Group extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['entries', 'groups'];

    protected $table = 'config_groups';

    public function entries()
    {
        return $this->hasMany(Entry::class, 'group_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'group_id')->with('groups');
    }

    public function parent()
    {
        return $this->belongsTo(Group::class, 'group_id')->with('groups');
    }

    public function scopeGetRootGroups(Builder $query): Builder
    {
        return $query->where('root', '=', 1);
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
}
