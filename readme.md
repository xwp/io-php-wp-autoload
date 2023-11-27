## WP Autoload

PHP autoloader for projects with [file naming conventions from WordPress coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/#naming-conventions).

Attempts to resolve classes, interfaces and traits.

## Usage

Add this library as a dependency for your project:

	composer require xwp/wp-autoload

Register the namespace mapping to folders containing your code:

```php
$autoload = new XWP\IO\WP_Autoload\Autoload();

$autoload->add(
	__DIR__ . '/php',
	'YourVendor\Project'
);

$autoload->add(
	__DIR__ . '/lib/rest-api',
	'Another_Vendor\Rest_Api'
);

// Now instantiate the class without any includes.
$api = new YourVendor\Project\Module_One\Api();
```

For example, a request for `YourVendor\Project\Module_One\Api` will attempt to include the following files:

- `.../php/module-one/class-api.php`
- `.../php/module-one/interface-api.php`
- `.../php/module-one/trait-api.php`
