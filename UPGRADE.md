## Upgrade from 2.11 to 2.12

### [Possible BC BREAK] Switch to `laminas/laminas-diagnostics`

`zendframework/zenddiagnostics` has been deprecated and replaced with `laminas/laminas-diagnostics`. The API remains the same
but the namespace has changed (`ZendDiagnostics\*` to `Laminas\Diagnostics\*`). If using this bundle without custom reporters
or checks, there is no BC break. If using custom checks/reporters, you will need to update their imports:

```diff
- use ZendDiagnostics\*;
+ use Laminas\Diagnostics\*;
```
