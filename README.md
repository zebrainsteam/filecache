# Filecache

Библиотека для файлового кеша, поддерживающая работу с тегами и масками. Совместима с PSR-16.

## Установка

`composer require zebrains/file-cache`

## Пример

Импортируем нужные классы и создаем экземпляр сервиса (менеджера)
```
use Zebrains\Filecache\Manager;
use Zebrains\Filecache\ImmediateInvalidator;
use Zebrains\Filecache\\Converter;
use Zebrains\Filecache\Formatters\RawFormatter;

$manager = new Manager($path, new Converter(), new RawFormatter(), new ImmediateInvalidator());
```

Менеджер реализует интерфейс `Psr\SimpleCache\CacheInterface`:
```
$key = '1:/catalog/product1';
$data = '<html><head><title>Hello world</title></head><body><p>Hello world!</p></body></html>';
$ttl = strtotime('now') + 60*60*24;

$manager->set($key, $data, $ttl);

var_dump($manager->get($key));
```

Есть поддержка тегов:
```
$tags = ['catalog', 'product1'];

$manager->setWithTags($key, $data, $tags, $ttl);

var_dump($manager->getByTag('catalog'));
```

Есть поддержка поиска по маске. В данный момент поддерживается только символ `*`.

Пример:

`$manager->getByMask('1:', '*'));` - будет произведен поиск на соответствие маске '1:*'

`$manager->getByMask('1:', '*', '/product1'));` - будет произведен поиск на соответствие маске '1:*/product1'
