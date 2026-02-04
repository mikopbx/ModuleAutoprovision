# ModuleAutoprovision for MikoPBX

<a href="README.ru.md">Документация на русском</a>

<img src="public/assets/img/logo.png" alt="ModuleAutoprovision" width="160">

Automatic IP phone provisioning module for [MikoPBX](https://github.com/mikopbx/Core). Discovers phones on the local network via Plug-and-Play (PnP) multicast, generates vendor-specific configuration files, and delivers them over HTTP.

## Supported Phones

- **Yealink** — T18P, T19D, T21D, T28P, W52P (DECT)
- **Snom**
- **Fanvil**
- **Grandstream** (phonebook only)

## How It Works

1. The module enables a special SIP account **apv-miko-pbx** on the PBX.
2. A PnP server listens on multicast address `224.0.1.75:5060` and discovers phones on the local network.
3. When a phone is reset to factory settings and connects for the first time, it registers to the **apv-miko-pbx** account.
4. To assign an extension, dial the provision pattern (e.g. `*2*XXX`) from the phone, where `XXX` is the desired internal number.
5. The module generates a vendor-specific configuration and delivers it via the REST API at `/pbxcore/api/autoprovision-http`.

## Features

- Automatic phone discovery and registration via PnP
- MAC address whitelisting and blacklisting
- Template-based configuration with variables (`{SIP_USER_NAME}`, `{SIP_NUM}`, `{SIP_PASS}`)
- URI pattern matching for flexible config delivery
- Shared phonebook across multiple PBX instances
- BLF (Busy Lamp Field) button configuration
- Vendor-specific INI parameters

## Requirements

- MikoPBX **2024.1.42** or later

## Installation

Install from the MikoPBX marketplace via the admin panel:
**Modules** > **Marketplace** > **Autoprovision module**

## Configuration

After installation, the module settings are available at:
**Modules** > **Autoprovision module**

The settings page has five tabs:

| Tab | Description |
|-----|-------------|
| **Phone Settings** | Assign employees to MAC addresses with templates |
| **Settings Templates** | Define configuration templates with variable substitution |
| **URI Settings** | Map URI patterns to templates (supports `%` wildcard) |
| **Phone Book** | Add external PBX addresses for shared phonebook |
| **PnP Settings** | Extension pattern, PBX host, MAC lists, additional params |

## REST API

Base path: `/pbxcore/api/autoprovision-http`

| Endpoint | Description |
|----------|-------------|
| `/*` | Phone configuration delivery (auto-detects vendor by MAC) |
| `/phonebook` | Phonebook in vendor-specific format (XML for Yealink, text for Grandstream) |

## Documentation

- [English documentation](https://docs.mikopbx.com/mikopbx/english/modules/miko/module-autoprovision)
- [Документация на русском](https://docs.mikopbx.ru/mikopbx/modules/miko/module-autoprovision)

## License

GPL-3.0-or-later. See [LICENSE](LICENSE).
