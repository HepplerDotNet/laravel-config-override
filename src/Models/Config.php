<?php

namespace HepplerDotNet\LaravelConfigOverride\Models;

use Illuminate\Database\Eloquent\Collection;

class Config
{
    public function __construct(public Collection $groups, public array $config = [])
    {
    }

    public function get(): array
    {
        $this->build($this->groups);

        return $this->config;
    }

    private function build(Collection $groups, string $parent = null): void
    {
        foreach ($groups as $group) {
            if ($group->root && $group->active) {
                $this->config[$group->name] = [];
                if ($group->entries->isNotEmpty()) {
                    foreach ($group->entries as $entry) {
                        $this->config[$group->name][$entry->name] = $entry->value;
                    }
                }
                if ($group->groups->isNotEmpty()) {
                    $this->build($group->groups, $group->name);
                }
            }
            if (!$group->root) {
                data_set($this->config, $parent.'.'.$group->name, []);
                if ($group->entries->isNotEmpty()) {
                    foreach ($group->entries as $entry) {
                        data_set($this->config, $parent.'.'.$group->name.'.'.$entry->name, $entry->value);
                    }
                }
                if ($group->groups->isNotEmpty()) {
                    $this->build($group->groups, $parent.'.'.$group->name);
                }
            }
        }
    }
}
