# hyperf-trigger

[![Latest Stable Version](https://poser.pugx.org/huangdijia/hyperf-trigger/version.png)](https://packagist.org/packages/huangdijia/hyperf-trigger)
[![Total Downloads](https://poser.pugx.org/huangdijia/hyperf-trigger/d/total.png)](https://packagist.org/packages/huangdijia/hyperf-trigger)
[![GitHub license](https://img.shields.io/github/license/huangdijia/hyperf-trigger)](https://github.com/huangdijia/hyperf-trigger)

MySQL trigger component for hyperf

⚠️ Move to [friendsofhyperf/trigger](https://github.com/friendsofhyperf/trigger)

## Installation

- Request

```bash
composer require huangdijia/hyperf-trigger
```

- Publish

```bash
php bin/hyperf.php vendor:publish huangdijia/hyperf-trigger
```

## Costom Trigger

```php
namespace App\Trigger;

use Huangdijia\Trigger\Annotation\Trigger;
use Huangdijia\Trigger\Trigger\AbstractTrigger;
use MySQLReplication\Event\DTO\EventDTO;

/**
 * single
 * @Trigger(table="table" on="write", replication="default")
 * or multi events by array
 * @Trigger(table="table" on={"write", "update", "delete"}, replication="default")
 * or multi events by string
 * @Trigger(table="table" on="write,update,delete", replication="default")
 * or all events
 * @Trigger(table="table" on="*", replication="default")
 */
class SomeTableTrigger extends AbstractTrigger
{
    public function onWrite(array $new)
    {
        var_dump($new);
    }

    public function onUpdate(array $old, array $new)
    {
        var_dump($old, $new);
    }

    public function onDelete(array $old)
    {
        var_dump($old);
    }
}
```

## Costom Subscriber

```php
namespace App\Subscriber;

use Huangdijia\Trigger\Annotation\Subscriber;
use Huangdijia\Trigger\Subscriber\AbstractEventSubscriber;
use MySQLReplication\Event\DTO\EventDTO;

/**
 * @Subscriber(replication="default")
 */
class DemoSubscriber extends AbstractEventSubscriber
{
    protected function allEvents(EventDTO $event): void
    {
        // some code
    }
}
```

## Setup Process

- Default

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

- Custom replication

```php
namespace App\Process;

use Huangdijia\Trigger\Process\ConsumeProcess;
use Hyperf\Process\Annotation\Process;

/**
 * @Process
 */
class CustomProcess extends ConsumeProcess
{
    protected $replication = 'custom_replication';
}
