# rc-base

[![CI](https://github.com/reflexive-communications/rc-base/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/rc-base/actions/workflows/main.yml)

This extension does nothing, it's only required by some other extensions. It contains shared components, libraries.

**New API actions**

-   `Setting::getSmtpConfig`, APIv4
    -   Returns current SMTP config nicely formatted. If SMTP password is encrypted, decrypted text is provided.
    -   You can select which config to return (or all)
-   `Setting::setSmtpConfig`, APIv4
    -   Allows for more convenient setting of SMTP configs mainly on the CLI (terminal)
    -   If encryption is enabled, it will encrypt plain-text passwords
    -   Idempotent: checks first and changes config only if it's needed, and report back if no change was done
-   `Contact::anonymize`, APIv4 (**DEPRECATED**)
    -   Anonymize contact: delete all contact fields and correspondence (email, phone, address etc.)
-   `Extension::haspendingupgrade`, APIv3
    -   Thin wrapper for `CRM_Extension_Upgrades::hasPending()`
    -   Returns `1` if there are pending DB upgrades for extensions, `0` otherwise

**Wrappers**

-   `\Civi\RcBase\ApiWrapper`: classes that wrap standard Civi APIv4
-   `\Civi\RcBase\Settings`: for managing settings and configs, wrapper for `Civi::Settings`

**Services**

-   `\Civi\RcBase\IOProcessor`: IO processors for JSON, URL-encoded, XML or INI files or streams
-   `\Civi\RcBase\Logger`: file logger, use as `Civi::log('rc-base')->info('message')`

**Traits**

-   `\Civi\RcBase\Api4\ActionUtilsTrait`: methods for APIv4 abstract (non-CRUD) actions
-   `\Civi\RcBase\Api4\EntityPagingTrait`: methods for APIv4 actions that use paging through MySQL results

**Other**

-   `\Civi\RcBase\Exception`: new specific exceptions and handlers
-   `\Civi\RcBase\Utils`: utility classes and various helper methods
-   `CRM_RcBase_Config`: wraps `Civi::Settings` wrapper. For details check the [Developer Notes](DEVELOPER.md). **DEPRECATED**
-   `CRM_RcBase_Setting`: same as above with a few extra methods. **DEPRECATED** in favor of `\Civi\RcBase\Settings`!
-   stylesheets: some `.css` file with general classes.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

-   PHP v7.4+
-   CiviCRM v5.48+

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/rc-base.git
cv en rc-base
```
