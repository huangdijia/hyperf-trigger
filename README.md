# hyperf-trigger

[![Latest Stable Version](https://poser.pugx.org/huangdijia/hyperf-trigger/version.png)](https://packagist.org/packages/huangdijia/hyperf-trigger)
[![Total Downloads](https://poser.pugx.org/huangdijia/hyperf-trigger/d/total.png)](https://packagist.org/packages/huangdijia/hyperf-trigger)
[![GitHub license](https://img.shields.io/github/license/huangdijia/hyperf-trigger)](https://github.com/huangdijia/hyperf-trigger)

A service-governance component for hyperf

## Installation

- Request

```bash
composer require huangdijia/hyperf-trigger
```

- Publish

```bash
php bin/hyperf.php vendor:publish huangdijia/hyperf-trigger
```

## Listener

```php
namespace App\Listener;

use Huangdijia\Trigger\Annotation\Trigger;
use Huangdijia\Trigger\Constact\ListenerInterface;
use Hyperf\Di\Annotation\Inject;
use MySQLReplication\Event\DTO\EventDTO;

/**
 * event: write,update,delete
 * @Trigger(listen="some_table.event")
 */
class SomeTableListener implements ListenerInterface
{
    public function process(EventDTO $event)
    {
        var_dump($event->__toString());
    }
}
```

## Setup Process

```php
namespace App\Process;

use Huangdijia\Trigger\Process\ConsumeProcess;
use Hyperf\Process\Annotation\Process;

/**
 * @Process
 */
class TriggerProcess extends ConsumeProcess
{
}
```
