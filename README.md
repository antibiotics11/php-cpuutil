# php-cpuutil
Linux utility to check CPU status.

```php
use CpuUtils\Temperature;

for ($cpu = 0; $cpu < 4; $cpu++) {
  $temp = Temperature::read($cpu);
  printf("Core %d: %d°C\r\n", $cpu, $temp->cpu);
}
printf("Package: %d°C\r\n", $temp->package);
printf("TjMax: %d°C\r\n", $temp->tjMax);
```

```bash
Core 0: 38°C
Core 1: 36°C
Core 2: 37°C
Core 3: 38°C
Package: 39°C
TjMax: 105°C
```

## Requirements

- IA32/IA32e CPU
- PHP >= 8.1
- Direct I/O (standard PHP filesystem function does not support seeking)
