
# PHP Simple Constructor Injection

Very very simple container example that injects dependency on class constructor


## Install


```bash
  composer require php-simple/constructor-injection
```


## Usage/Examples

### bind() method

```php
require_once __DIR__ . '/vendor/autoload.php';

class User
{
    public $foo;

    public function __construct(Foo $foo) 
    {
        $this->foo = $foo
    }
    
    public function foo()
    {
        return $this->foo->sayFoo();
    }
}

class Foo
{
    public function sayFoo()
    {
        return 'Foooo'
    }
}

$container = new \ConstructorInjection\Container();

$container->bind(User::class);

$userInstance = $container->getBinding(User::class)

// Foooo
$userInstance->foo();
```

### bind() method with closure
```php
$container->bind(User::class,function (){
    return new User();
});

$userInstance = $container->getBinding(User::class)

// Foooo
$userInstance->foo();
```

### get() method
```php
$userInstance = $container->get(User::class);

// Foooo
$userInstance->foo();
```

### getBindings method()
- returns all bind classes
```php
$bindings = $container->getBindings();

print_r($bindings);

//Array
//(
//    [User] => Closure Object
//        (
//        )
//
//)
```
