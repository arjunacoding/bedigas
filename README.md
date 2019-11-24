# What is Bedigas?

Bedigas is a fast, simple, extensible framework for PHP. Bedigas enables you to 
quickly and easily build RESTful web applications.

```php
require 'bedigas/Bedigas.php';

Bedigas::route('/', function(){
    echo 'hello world!';
});

Bedigas::start();
```

[Learn more](http://bedigasphp.com/learn)

# Requirements

Bedigas requires `PHP 5.3` or greater.

# License

Bedigas is released under the [MIT](http://bedigasphp.com/license) license.

# Installation

1\. Download the files.

If you're using [Composer](https://getcomposer.org/), you can run the following command:

```
composer require arjunacoding/bedigas
```

OR you can [download](https://github.com/arjunacoding/bedigas/archive/master.zip) them directly 
and extract them to your web directory.

2\. Configure your webserver.

For *Apache*, edit your `.htaccess` file with the following:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Note**: If you need to use bedigas in a subdirectory add the line `RewriteBase /subdir/` just after `RewriteEngine On`.

For *Nginx*, add the following to your server declaration:

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```
3\. Create your `index.php` file.

First include the framework.

```php
require 'bedigas/Bedigas.php';
```

If you're using Composer, run the autoloader instead.

```php
require 'vendor/autoload.php';
```

Then define a route and assign a function to handle the request.

```php
Bedigas::route('/', function(){
    echo 'hello world!';
});
```

Finally, start the framework.

```php
Bedigas::start();
```

# Routing

Routing in Bedigas is done by matching a URL pattern with a callback function.

```php
Bedigas::route('/', function(){
    echo 'hello world!';
});
```

The callback can be any object that is callable. So you can use a regular function:

```php
function hello(){
    echo 'hello world!';
}

Bedigas::route('/', 'hello');
```

Or a class method:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Bedigas::route('/', array('Greeting', 'hello'));
```

Or an object method:

```php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

$greeting = new Greeting();

Bedigas::route('/', array($greeting, 'hello')); 
```

Routes are matched in the order they are defined. The first route to match a
request will be invoked.

## Method Routing

By default, route patterns are matched against all request methods. You can respond
to specific methods by placing an identifier before the URL.

```php
Bedigas::route('GET /', function(){
    echo 'I received a GET request.';
});

Bedigas::route('POST /', function(){
    echo 'I received a POST request.';
});
```

You can also map multiple methods to a single callback by using a `|` delimiter:

```php
Bedigas::route('GET|POST /', function(){
    echo 'I received either a GET or a POST request.';
});
```

## Regular Expressions

You can use regular expressions in your routes:

```php
Bedigas::route('/user/[0-9]+', function(){
    // This will match /user/1234
});
```

## Named Parameters

You can specify named parameters in your routes which will be passed along to
your callback function.

```php
Bedigas::route('/@name/@id', function($name, $id){
    echo "hello, $name ($id)!";
});
```

You can also include regular expressions with your named parameters by using
the `:` delimiter:

```php
Bedigas::route('/@name/@id:[0-9]{3}', function($name, $id){
    // This will match /bob/123
    // But will not match /bob/12345
});
```

## Optional Parameters

You can specify named parameters that are optional for matching by wrapping
segments in parentheses.

```php
Bedigas::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Any optional parameters that are not matched will be passed in as NULL.

## Wildcards

Matching is only done on individual URL segments. If you want to match multiple
segments you can use the `*` wildcard.

```php
Bedigas::route('/blog/*', function(){
    // This will match /blog/2000/02/01
});
```

To route all requests to a single callback, you can do:

```php
Bedigas::route('*', function(){
    // Do something
});
```

## Passing

You can pass execution on to the next matching route by returning `true` from
your callback function.

```php
Bedigas::route('/user/@name', function($name){
    // Check some condition
    if ($name != "Bob") {
        // Continue to next route
        return true;
    }
});

Bedigas::route('/user/*', function(){
    // This will get called
});
```

## Route Info

If you want to inspect the matching route information, you can request for the route
object to be passed to your callback by passing in `true` as the third parameter in
the route method. The route object will always be the last parameter passed to your
callback function.

```php
Bedigas::route('/', function($route){
    // Array of HTTP methods matched against
    $route->methods;

    // Array of named parameters
    $route->params;

    // Matching regular expression
    $route->regex;

    // Contains the contents of any '*' used in the URL pattern
    $route->splat;
}, true);
```

# Extending

Bedigas is designed to be an extensible framework. The framework comes with a set
of default methods and components, but it allows you to map your own methods,
register your own classes, or even override existing classes and methods.

## Mapping Methods

To map your own custom method, you use the `map` function:

```php
// Map your method
Bedigas::map('hello', function($name){
    echo "hello $name!";
});

// Call your custom method
Bedigas::hello('Bob');
```

## Registering Classes

To register your own class, you use the `register` function:

```php
// Register your class
Bedigas::register('user', 'User');

// Get an instance of your class
$user = Bedigas::user();
```

The register method also allows you to pass along parameters to your class
constructor. So when you load your custom class, it will come pre-initialized.
You can define the constructor parameters by passing in an additional array.
Here's an example of loading a database connection:

```php
// Register class with constructor parameters
Bedigas::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Get an instance of your class
// This will create an object with the defined parameters
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Bedigas::db();
```

If you pass in an additional callback parameter, it will be executed immediately
after class construction. This allows you to perform any set up procedures for your
new object. The callback function takes one parameter, an instance of the new object.

```php
// The callback will be passed the object that was constructed
Bedigas::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'), function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});
```

By default, every time you load your class you will get a shared instance.
To get a new instance of a class, simply pass in `false` as a parameter:

```php
// Shared instance of the class
$shared = Bedigas::db();

// New instance of the class
$new = Bedigas::db(false);
```

Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

# Overriding

Bedigas allows you to override its default functionality to suit your own needs,
without having to modify any code.

For example, when Bedigas cannot match a URL to a route, it invokes the `notFound`
method which sends a generic `HTTP 404` response. You can override this behavior
by using the `map` method:

```php
Bedigas::map('notFound', function(){
    // Display custom 404 page
    include 'errors/404.html';
});
```

Bedigas also allows you to replace core components of the framework.
For example you can replace the default Router class with your own custom class:

```php
// Register your custom class
Bedigas::register('router', 'MyRouter');

// When Bedigas loads the Router instance, it will load your class
$myrouter = Bedigas::router();
```

Framework methods like `map` and `register` however cannot be overridden. You will
get an error if you try to do so.

# Filtering

Bedigas allows you to filter methods before and after they are called. There are no
predefined hooks you need to memorize. You can filter any of the default framework
methods as well as any custom methods that you've mapped.

A filter function looks like this:

```php
function(&$params, &$output) {
    // Filter code
}
```

Using the passed in variables you can manipulate the input parameters and/or the output.

You can have a filter run before a method by doing:

```php
Bedigas::before('start', function(&$params, &$output){
    // Do something
});
```

You can have a filter run after a method by doing:

```php
Bedigas::after('start', function(&$params, &$output){
    // Do something
});
```

You can add as many filters as you want to any method. They will be called in the
order that they are declared.

Here's an example of the filtering process:

```php
// Map a custom method
Bedigas::map('hello', function($name){
    return "Hello, $name!";
});

// Add a before filter
Bedigas::before('hello', function(&$params, &$output){
    // Manipulate the parameter
    $params[0] = 'Fred';
});

// Add an after filter
Bedigas::after('hello', function(&$params, &$output){
    // Manipulate the output
    $output .= " Have a nice day!";
});

// Invoke the custom method
echo Bedigas::hello('Bob');
```

This should display:

    Hello Fred! Have a nice day!

If you have defined multiple filters, you can break the chain by returning `false`
in any of your filter functions:

```php
Bedigas::before('start', function(&$params, &$output){
    echo 'one';
});

Bedigas::before('start', function(&$params, &$output){
    echo 'two';

    // This will end the chain
    return false;
});

// This will not get called
Bedigas::before('start', function(&$params, &$output){
    echo 'three';
});
```

Note, core methods such as `map` and `register` cannot be filtered because they
are called directly and not invoked dynamically.

# Variables

Bedigas allows you to save variables so that they can be used anywhere in your application.

```php
// Save your variable
Bedigas::set('id', 123);

// Elsewhere in your application
$id = Bedigas::get('id');
```
To see if a variable has been set you can do:

```php
if (Bedigas::has('id')) {
     // Do something
}
```

You can clear a variable by doing:

```php
// Clears the id variable
Bedigas::clear('id');

// Clears all variables
Bedigas::clear();
```

Bedigas also uses variables for configuration purposes.

```php
Bedigas::set('bedigas.log_errors', true);
```

# Views

Bedigas provides some basic templating functionality by default. To display a view
template call the `render` method with the name of the template file and optional
template data:

```php
Bedigas::render('hello.php', array('name' => 'Bob'));
```

The template data you pass in is automatically injected into the template and can
be reference like a local variable. Template files are simply PHP files. If the
content of the `hello.php` template file is:

```php
Hello, '<?php echo $name; ?>'!
```

The output would be:

    Hello, Bob!

You can also manually set view variables by using the set method:

```php
Bedigas::view()->set('name', 'Bob');
```

The variable `name` is now available across all your views. So you can simply do:

```php
Bedigas::render('hello');
```

Note that when specifying the name of the template in the render method, you can
leave out the `.php` extension.

By default Bedigas will look for a `views` directory for template files. You can
set an alternate path for your templates by setting the following config:

```php
Bedigas::set('bedigas.views.path', '/path/to/views');
```

## Layouts

It is common for websites to have a single layout template file with interchanging
content. To render content to be used in a layout, you can pass in an optional
parameter to the `render` method.

```php
Bedigas::render('header', array('heading' => 'Hello'), 'header_content');
Bedigas::render('body', array('body' => 'World'), 'body_content');
```

Your view will then have saved variables called `header_content` and `body_content`.
You can then render your layout by doing:

```php
Bedigas::render('layout', array('title' => 'Home Page'));
```

If the template files looks like this:

`header.php`:

```php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

```php
<div><?php echo $body; ?></div>
```

`layout.php`:

```php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

The output would be:
```html
<html>
<head>
<title>Home Page</title>
</head>
<body>
<h1>Hello</h1>
<div>World</div>
</body>
</html>
```

## Custom Views

Bedigas allows you to swap out the default view engine simply by registering your
own view class. Here's how you would use the [Smarty](http://www.smarty.net/)
template engine for your views:

```php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Bedigas::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Assign template data
Bedigas::view()->assign('name', 'Bob');

// Display the template
Bedigas::view()->display('hello.tpl');
```

For completeness, you should also override Bedigas's default render method:

```php
Bedigas::map('render', function($template, $data){
    Bedigas::view()->assign($data);
    Bedigas::view()->display($template);
});
```
# Error Handling

## Errors and Exceptions

All errors and exceptions are caught by Bedigas and passed to the `error` method.
The default behavior is to send a generic `HTTP 500 Internal Server Error`
response with some error information.

You can override this behavior for your own needs:

```php
Bedigas::map('error', function(Exception $ex){
    // Handle error
    echo $ex->getTraceAsString();
});
```

By default errors are not logged to the web server. You can enable this by
changing the config:

```php
Bedigas::set('bedigas.log_errors', true);
```

## Not Found

When a URL can't be found, Bedigas calls the `notFound` method. The default
behavior is to send an `HTTP 404 Not Found` response with a simple message.

You can override this behavior for your own needs:

```php
Bedigas::map('notFound', function(){
    // Handle not found
});
```

# Redirects

You can redirect the current request by using the `redirect` method and passing
in a new URL:

```php
Bedigas::redirect('/new/location');
```

By default Bedigas sends a HTTP 303 status code. You can optionally set a
custom code:

```php
Bedigas::redirect('/new/location', 401);
```

# Requests

Bedigas encapsulates the HTTP request into a single object, which can be
accessed by doing:

```php
$request = Bedigas::request();
```

The request object provides the following properties:

```
url - The URL being requested
base - The parent subdirectory of the URL
method - The request method (GET, POST, PUT, DELETE)
referrer - The referrer URL
ip - IP address of the client
ajax - Whether the request is an AJAX request
scheme - The server protocol (http, https)
user_agent - Browser information
type - The content type
length - The content length
query - Query string parameters
data - Post data or JSON data
cookies - Cookie data
files - Uploaded files
secure - Whether the connection is secure
accept - HTTP accept parameters
proxy_ip - Proxy IP address of the client
host - The request host name
```

You can access the `query`, `data`, `cookies`, and `files` properties
as arrays or objects.

So, to get a query string parameter, you can do:

```php
$id = Bedigas::request()->query['id'];
```

Or you can do:

```php
$id = Bedigas::request()->query->id;
```

## RAW Request Body

To get the raw HTTP request body, for example when dealing with PUT requests, you can do:

```php
$body = Bedigas::request()->getBody();
```

## JSON Input

If you send a request with the type `application/json` and the data `{"id": 123}` it will be available
from the `data` property:

```php
$id = Bedigas::request()->data->id;
```

# HTTP Caching

Bedigas provides built-in support for HTTP level caching. If the caching condition
is met, Bedigas will return an HTTP `304 Not Modified` response. The next time the
client requests the same resource, they will be prompted to use their locally
cached version.

## Last-Modified

You can use the `lastModified` method and pass in a UNIX timestamp to set the date
and time a page was last modified. The client will continue to use their cache until
the last modified value is changed.

```php
Bedigas::route('/news', function(){
    Bedigas::lastModified(1234567890);
    echo 'This content will be cached.';
});
```

## ETag

`ETag` caching is similar to `Last-Modified`, except you can specify any id you
want for the resource:

```php
Bedigas::route('/news', function(){
    Bedigas::etag('my-unique-id');
    echo 'This content will be cached.';
});
```

Keep in mind that calling either `lastModified` or `etag` will both set and check the
cache value. If the cache value is the same between requests, Bedigas will immediately
send an `HTTP 304` response and stop processing.

# Stopping

You can stop the framework at any point by calling the `halt` method:

```php
Bedigas::halt();
```

You can also specify an optional `HTTP` status code and message:

```php
Bedigas::halt(200, 'Be right back...');
```

Calling `halt` will discard any response content up to that point. If you want to stop
the framework and output the current response, use the `stop` method:

```php
Bedigas::stop();
```

# JSON

Bedigas provides support for sending JSON and JSONP responses. To send a JSON response you
pass some data to be JSON encoded:

```php
Bedigas::json(array('id' => 123));
```

For JSONP requests you, can optionally pass in the query parameter name you are
using to define your callback function:

```php
Bedigas::jsonp(array('id' => 123), 'q');
```

So, when making a GET request using `?q=my_func`, you should receive the output:

```
my_func({"id":123});
```

If you don't pass in a query parameter name it will default to `jsonp`.


# Configuration

You can customize certain behaviors of Bedigas by setting configuration values
through the `set` method.

```php
Bedigas::set('bedigas.log_errors', true);
```

The following is a list of all the available configuration settings:

    bedigas.base_url - Override the base url of the request. (default: null)
    bedigas.case_sensitive - Case sensitive matching for URLs. (default: false)
    bedigas.handle_errors - Allow Bedigas to handle all errors internally. (default: true)
    bedigas.log_errors - Log errors to the web server's error log file. (default: false)
    bedigas.views.path - Directory containing view template files. (default: ./views)
    bedigas.views.extension - View template file extension. (default: .php)

# Framework Methods

Bedigas is designed to be easy to use and understand. The following is the complete
set of methods for the framework. It consists of core methods, which are regular
static methods, and extensible methods, which are mapped methods that can be filtered
or overridden.

## Core Methods

```php
Bedigas::map($name, $callback) // Creates a custom framework method.
Bedigas::register($name, $class, [$params], [$callback]) // Registers a class to a framework method.
Bedigas::before($name, $callback) // Adds a filter before a framework method.
Bedigas::after($name, $callback) // Adds a filter after a framework method.
Bedigas::path($path) // Adds a path for autoloading classes.
Bedigas::get($key) // Gets a variable.
Bedigas::set($key, $value) // Sets a variable.
Bedigas::has($key) // Checks if a variable is set.
Bedigas::clear([$key]) // Clears a variable.
Bedigas::init() // Initializes the framework to its default settings.
Bedigas::app() // Gets the application object instance
```

## Extensible Methods

```php
Bedigas::start() // Starts the framework.
Bedigas::stop() // Stops the framework and sends a response.
Bedigas::halt([$code], [$message]) // Stop the framework with an optional status code and message.
Bedigas::route($pattern, $callback) // Maps a URL pattern to a callback.
Bedigas::redirect($url, [$code]) // Redirects to another URL.
Bedigas::render($file, [$data], [$key]) // Renders a template file.
Bedigas::error($exception) // Sends an HTTP 500 response.
Bedigas::notFound() // Sends an HTTP 404 response.
Bedigas::etag($id, [$type]) // Performs ETag HTTP caching.
Bedigas::lastModified($time) // Performs last modified HTTP caching.
Bedigas::json($data, [$code], [$encode], [$charset], [$option]) // Sends a JSON response.
Bedigas::jsonp($data, [$param], [$code], [$encode], [$charset], [$option]) // Sends a JSONP response.
```

Any custom methods added with `map` and `register` can also be filtered.


# Framework Instance

Instead of running Bedigas as a global static class, you can optionally run it
as an object instance.

```php
require 'bedigas/autoload.php';

use bedigas\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'hello world!';
});

$app->start();
```

So instead of calling the static method, you would call the instance method with
the same name on the Engine object.
