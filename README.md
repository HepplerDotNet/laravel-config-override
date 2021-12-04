# Laravel Config Override
Store Laravel Configuration in Database and use it thru `config()` helper

# Important
This Package uses `Illuminate\Vendor\HepplerDotNet\Providers\ConfigurationProvider`

Before you start yelling "Using Illuminate namespace is bad practice!".

You're right, but let's take a look at `Illuminate\Foundation\Application::registerConfiguredProviders`

```php
/**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->make('config')->get('app.providers'))
                        ->partition(function ($provider) {
                            return strpos($provider, 'Illuminate\\') === 0;
                        });

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                    ->load($providers->collapse()->toArray());
    }
```
1. It creates a collection from your App config providers array
2. Splits this array in 2 chunks [Everything that starts with Illuminate\, rest of it]
3. Adds all the composer packages service providers in between

This package derived from one of my projects which had some requirements:

1. Login against Active Directory (solved easily with https://ldaprecord.com/docs/laravel/v2/)
2. Make the whole App configuration maintainable thru Webgui, including ldap configuration

All "regular" options to register a Service Provider failed at some point, either `Auth` facade failed or LDAP failed because `config()`
had no access to the configuration from database at this point.

So using Illuminate namespace was the only working solution. Period.

# Installation
**Requires at least laravel/framework 8.37 because of anonymous migrations**

Run `composer require hepplerdotnet/laravel-config-override`

# Usage
Let's say you would change the app locale

```php
use HepplerDotNet\LaravelConfigOverride\Models\Group;
Group::create(["name" => "app", "root" => true, "active" => true])
  ->entries()
  ->create(["key" => "locale", "value" => "de", "type" => "String"]);
```
Now you can access it with `config("app.locale")`

Or a more complex example with nested groups

```php
Group::create(["name" => "mail", "root" => true, "active" => true])
    ->groups()->create(["name" => "mailers", "root" => false, "active" => true])
    ->groups()->create(["name" => "smtp", "root" => false, "active" => true])
    ->entries()->create(["key" => "host","value" => "localhost", "type" => "String"]);
```
Now you can access it with `config("mail.mailers.smtp.host")`


# The Models
## Group
| Property | Description | Values |
|----------|-------------|--------|
| name | Name analog to config file e.g. app or mail| String |
| root | Group is root element | true or false |
| active | Configuration is active and should be loaded | true or false|

## Entry
| Property | Description | Values |
|----------|-------------|--------|
| key | Configuration Key | String |
| value | Configuration Value | String, Password, Integer, Boolean, Array |
| type | Value type | String, Password, Integer, Boolean, Array |

**Type Password will be stored encrypted in database!**

## Config
Config is not a real Eloquent Model, it's just a Class which builds the config from database
