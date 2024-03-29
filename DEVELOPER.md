# Developer Notes

## Config aka. CRM_RcBase_Config

This abstract class provides an interface for the db methods (civi config). The create, load, update, get, remove
methods are implemented in this class. If you want to use this Config, you have to extend this class and implement the
defaultConfiguration method. It is responsible for providing an extension specific default configuration that will be
inserted to the settings database with the create method. The configuration is stored under a config specific key in the
settings db. The prefix of this key has to be passed to the class constructor. The default configuration is inserted to
the config database on extension install.

**Concrete implementation example**

```php
class CRM_MyExtension_MyConfig extends CRM_RcBase_Config {
    public function defaultConfiguration(): array {
        return [
            "important-key" => 1,
        ];
    }
}
```

**Get current configuration**

```php
$config = new CRM_MyExtension_MyConfig("MyExtension_Key_Prefix");
try {
    $config->load();
} catch (Exception $e) {
    // It could happen if the dbhost is not configured well.
}
$configuration = $config->get();
```

**Update configuration**

```php
$config = new CRM_MyExtension_MyConfig("MyExtension_Key_Prefix");
$newConfig = [
    "important-key" => 2,
    "new-key" => "Some new value",
];
if (!$config->update($newConfig)) {
    // It could happen if the dbhost is not configured well.
}
```
