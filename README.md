# Combining URL rewrites with dynamic JS templating for your Laravel Nova powered application

This packages is build on top of two other packages:
* [Laravel Nova templating](https://github.com/rjvandoesburg/laravel-nova-templating): Add dynamic JS template loading to your Laravel Nova powered application
* [Laravel Nova Url Rewrite](https://github.com/rjvandoesburg/laravel-nova-url-rewrite): Add Url Rewrites to your Laravel Nova powered application

I was building a Laravel Nova application but was missing the front-end ease-of-use in combination with Nova.
[Ruthger Idema](https://github.com/ruthgeridema) had built a url rewrite package which he was using with Nova, but was using Laravel for the routing.
I was also using Nova but was using Vue.js for my front-end so I wanted pretty urls and to just build my front-end via an API.

Because people might want just one or the other I made 3 packages (templating, url rewrite and one that combines the other two)


What this package allows you to do is use Nova for your resources, add url rewrites so urls are nice and pretty and call an endpoint from your JS application and you will receive a list of potential templates to load. 

## Requirements

This package requires Laravel 5.8 or higher, PHP 7.2 or higher.

## Installation

You can install the package via composer:

``` bash
composer require rjvandoesburg/laravel-nova-url-rewrite-templating
```

The package will automatically register itself.

### Routing

Instead of using the route provided in [Laravel Nova templating](https://github.com/rjvandoesburg/laravel-nova-templating) you need to add the following to your routes file
```php
Route::NovaUrlRewriteTemplates();
```

Which will add the following routes:
* `api/template-api/{resource}/{resourceId}`
* `api/template-api/{templateUrl?}`

If the resource route returns a 404 it will try the other url as well.

[Laravel Nova Url Rewrite](https://github.com/rjvandoesburg/laravel-nova-url-rewrite#publish) comes with a migration, config and translations, please have a look if you wish to publish any of these files.

## Usage

Much like the usage of [Laravel Nova templating](https://github.com/rjvandoesburg/laravel-nova-templating#usage) new endpoints will be available and return a list of names you can use for templating.
However a 'cach-all' route is added with this package which will look for a url rewrite to decide what to return.

A typical response could be:
```json
{
  "templates": [
    "user-1",
    "user",
    "model",
    "index"
  ]
}
```

However if it is just a route without a resource and or model, the response is quite small:

```json
{
  "templates": [
    "home",
    "index"
  ]
}
```
(This needs work, e.g. a name could be generated based on the Request path and Target path)

In this case I would advise using a `Page` resource which will allow you to define the look and feel for a page.


### Redirects

When a url rewrite is a redirect the response is a little bit different as no templates are returned:
```json
{
    "redirect": "/users/1",
    "status": 302,
    "isExternal": false
}
```

The redirect path is returned as with the status (allthough chances are you cannot return a server response) and if the redirect is to an external url.

### VueJs

Say you want to use this within Vue, here is an example of how you could implement this:
```js
const files = require.context('./templates/', true, /\.vue$/i);
files.keys().map(key => Vue.component('template-'+key.split('/').pop().split('.')[0], files(key).default));
```
From `app.js` I am loading all files within the `templates` folder and prefixing `template` as the name when registring them with Vue.
* `templates/index.vue` will be registered as `template-index`
* `templates/user.vue` will be registered as `template-user`
* `templates/user-1.vue` will be registered as `template-user-1`

Create a Vue file that will be rendered on specific routes.
In the example I am using `vue-router` and `beforeRouteEnter` to retrieve the correct template based on the current url.

```vue
<template>
    <component :is="`template-${template}`"></component>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            return axios.get(`/template-api${path}`)
                .then(({data: response}) => {
                    if (response.redirect !== undefined) {
                        if (response.isExternal) {
                            window.location = response.redirect
                            return
                        }
                        this.$router.push(response.redirect)
                        return;
                    }

                    this.route = response
                })
                .then(next)
                .catch(error => {
                    // Show an error or redirect to an error page, dealer's choice
                })
        },

        data: () => ({
            template: null,
            route: null,
        }),

        created() {
            _.forEach(this.route.templates, template => {
                if (Vue.options.components[`template-${template}`] !== undefined) {
                    this.template = template
                    return false
                }
            })
        },
    }
</script>
```

## TODO

* Better templating for non resource/model routes

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Robert-John van Doesburg](https://github.com/rjvandoesburg)
- [All Contributors](../../contributors)

Special thanks for Spatie for their guidelines and their packages as an inspiration
- [Spatie](https://spatie.be)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
