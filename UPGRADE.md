## Upgrade to next version (Symfony 8 / XML deprecation)

### [BC BREAK] Bundle routes file extension changed

If you import the bundle routes manually, update the resource path from `routing.xml` to `routing.yaml`:

```yaml
# Before
_monitor:
    resource: "@LiipMonitorBundle/Resources/config/routing.xml"

# After
_monitor:
    resource: "@LiipMonitorBundle/Resources/config/routing.yaml"
```

The bundle's internal service configuration has been migrated from XML to PHP (Symfony 7.4+ deprecation).
This only affects the routing file if you reference it explicitly; the bundle routes themselves are unchanged.

---

## Upgrade from 2.11 to 2.12

### [Possible BC BREAK] Switch to `laminas/laminas-diagnostics`

`zendframework/zenddiagnostics` has been deprecated and replaced with `laminas/laminas-diagnostics`. The API remains the same
but the namespace has changed (`ZendDiagnostics\*` to `Laminas\Diagnostics\*`). If using this bundle without custom reporters
or checks, there is no BC break. If using custom checks/reporters, you will need to update their imports:

```diff
- use ZendDiagnostics\*;
+ use Laminas\Diagnostics\*;
```
