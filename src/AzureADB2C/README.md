# Azure AD B2C

```bash
composer require socialiteproviders/azureadb2c
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'azureadb2c' => [
    'client_id' => env('AADB2C_ClientId'),
    'client_secret' => env('AADB2C_ClientSecret'),
    'redirect' => env('AADB2C_RedirectUri'),
    'domain' => env('AADB2C_Domain'),  // {your_domain}.b2clogin.com
    'policy' => env('AADB2C_Policy'),  // such as 'b2c_1_user_susi'
    'default_algorithm' => env('AADB2C_DefaultAlgorithm', 'RS256'), // decoding algorithm JWK key such as 'RS256'
    'custom_domain' => env('AADB2C_CUSTOM_DOMAIN'), // optional - set to use your custom domain e.g. login.contoso.com
    'tenant' => env('AADB2C_TENANT'), // optional - set to use your tenant ID or custom domain in place of the default onmicrosoft.com one
],
```

To set up your Azure AD B2C custom domain, follow [these instructions](https://learn.microsoft.com/en-us/azure/active-directory-b2c/custom-domain?pivots=b2c-user-flow).

### Add provider event listener

#### Laravel 11+

In Laravel 11, the default `EventServiceProvider` provider was removed. Instead, add the listener using the `listen` method on the `Event` facade, in your `AppServiceProvider` `boot` method.

* Note: You do not need to add anything for the built-in socialite providers unless you override them with your own providers.

```php
Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
    $event->extendSocialite('azureadb2c', \SocialiteProviders\AzureADB2C\Provider::class);
});
```
<details>
<summary>
Laravel 10 or below
</summary>
Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\AzureADB2C\AzureADB2CExtendSocialite::class.'@handle',
    ],
];
```
</details>

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

Redirect to Azure AD B2C
```php
return Socialite::driver('azureadb2c')->redirect();
```

Callback
```php
$provided_user = Socialite::driver('azureadb2c')->user();
```

Logout
```php
return redirect(Socialite::driver('azureadb2c')->logout('http://localhost'));
```

### Returned User fields

- ``sub``
- ``name``

Note) If you want to add claim mappings, change `User::setRaw()` function. The claims mappings must be match with claims in id_token which Azure AD B2C returns.
```php
    public function setRaw($user)
    {
        $user['name'] = $user['name'] ?: $user['given_name'].' '.$user['family_name'];
        $user['nickname'] = $user['name'] ?: '';
        $user['email'] = $user['emails'][0];

        return parent::setRaw($user);
    }
```
