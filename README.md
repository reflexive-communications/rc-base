# rc-base

[![CI](https://github.com/reflexive-communications/rc-base/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/rc-base/actions/workflows/main.yml)

This extension does nothing, it's only required by some other extensions. It contains shared components, libraries.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Features

### Extra permissions

New CMS permissions:

- **access custom API**: this can be used for an unprivileged CMS user to access custom API endpoints

### New API

#### New API actions are provided.

Below is a summary, for more details check the API Explorer.

- `Setting::getSmtpConfig`, APIv4
    - Returns current SMTP config nicely formatted. If SMTP password is encrypted, decrypted text is provided.
    - You can select which config to return (or all)
- `Setting::setSmtpConfig`, APIv4
    - Allows for more convenient setting of SMTP configs mainly on the CLI (terminal)
    - If encryption is enabled, it will encrypt plain-text passwords
    - Idempotent: checks first and changes config only if it's needed, and report back if no change was done
- `Extension::haspendingupgrade`, APIv3
    - Thin wrapper for `CRM_Extension_Upgrades::hasPending()`
    - Returns `1` if there are pending DB upgrades for extensions, `0` otherwise

#### API wrappers

Generic PHP classes that wrap standard Civi APIv4.

### Processor

Generic PHP IO processors for JSON, URL-encoded, XML or INI files or streams.

### Civi::Settings wrapper

A Base class (`CRM_RcBase_Config`) that wraps `Civi::Settings()` for easier use. For details check
the [Developer Notes](DEVELOPER.md).

## Requirements

* PHP v7.4+
* CiviCRM (5.43 might work below - not tested)

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install it
with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/rc-base.git
cv en rc_base
```
