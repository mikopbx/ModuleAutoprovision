# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Module Overview

ModuleAutoprovision is a MikoPBX extension module for automatic IP phone provisioning. It discovers phones on the local network via PnP multicast (224.0.1.75:5060), generates vendor-specific configuration files, and delivers them over HTTP. Supported vendors: Yealink, Snom, Fanvil, Grandstream.

## Build Commands

### JavaScript compilation (ES6 to ES5)
```bash
/Users/nb/PhpstormProjects/mikopbx/MikoPBXUtils/node_modules/.bin/babel \
  public/assets/js/src/module-autoprovision-index.js \
  --out-dir public/assets/js/ \
  --source-maps inline \
  --presets airbnb
```

### PHP static analysis
```bash
phpstan analyse
```

### CI/CD
Pushes to `master` or `develop` trigger `.github/workflows/build.yml`, which uses the shared `mikopbx/.github-workflows` extension-publish workflow (initial version: 1.62).

## Architecture

### Namespace
All PHP classes use `Modules\ModuleAutoprovision\` namespace with PSR-4 autoloading rooted at `/`.

### Key Layers

**MVC (Phalcon Framework):**
- `App/Controllers/ModuleAutoprovisionController.php` — Web UI controller (settings tabs: phones, templates, URIs, phonebook, PnP config)
- `App/Forms/ModuleAutoprovisionForm.php` — Form field definitions
- `App/Views/index.volt` — Phalcon Volt template with Semantic UI

**Business Logic (`Lib/`):**
- `AutoprovisionConf.php` — Extends `ConfigClass`. Registers workers, defines REST API routes, constants (`BASE_URI = /pbxcore/api/autoprovision-http`)
- `Autoprovision.php` — Core config generation. Routes to vendor-specific classes, sends SIP NOTIFY for phone reboot, AGI entry point
- `ConfManager.php` — Interface with single method `generateConfig($req_data, $sip_peers): string`
- `AutoprovisionYealink.php`, `AutoprovisionSnom.php`, `AutoprovisionFanvil.php` — Vendor-specific implementations of `ConfManager`
- `WorkerProvisioningServerPnP.php` — Background worker: multicast PnP server, MAC filtering, device discovery
- `RestAPI/Controllers/GetController.php` — REST API: config delivery, phonebook, device/user CRUD

**Models (Phalcon ORM, `Models/`):**
- `ModuleAutoprovision` — Global settings (extension pattern, PBX host, MAC white/blacklists, additional INI params)
- `ModuleAutoprovisionDevice` — Phone devices (MAC, model, IP). Has many `Users` and `BLF`
- `ModuleAutoprovisionUsers` — Maps users to device lines. Belongs to `Users` (core) and `Device`
- `ModuleAutoprovisionBLF` — BLF button configs per device
- `Templates`, `TemplatesUri`, `TemplatesUsers` — Template system for config file generation
- `OtherPBX` — External PBX phonebook entries

All models extend `ModulesModelsBase`. Table names use `m_` prefix (e.g., `m_ModuleAutoprovisionDevice`).

**Setup:**
- `Setup/PbxExtensionSetup.php` — DB table creation, default data, extension registration. Extends `PbxExtensionSetupBase`

**AGI:**
- `agi-bin/ModuleAutoprovisionAGI.php` — Asterisk AGI script triggered by dial pattern (e.g., `*2*XXXX`). Resolves phone IP → MAC → device registration

### Frontend
- Source: `public/assets/js/src/module-autoprovision-index.js` (ES6)
- Compiled: `public/assets/js/module-autoprovision-index.js`
- Uses Semantic UI components, dynamic table management, AJAX form submission

### REST API Flow
Phones request configs via `GET /pbxcore/api/autoprovision-http/*` → `AutoprovisionConf::moduleRestAPICallback()` → `GetController::getConfigStatic()` → vendor-specific `ConfManager::generateConfig()`.

### Adding a New Phone Vendor
1. Create `Lib/AutoprovisionNewVendor.php` implementing `ConfManager` interface
2. Add MAC prefix detection in `GetController::getConfigStatic()`
3. Add config generation routing in `Autoprovision::generateConfigPhone()`

## Translations
30 language files in `Messages/`. Key file: `en.php`. Translation keys prefixed with `module_autoprovision_` or `mod_autoprovision_`.

## Diagnostics on a live MikoPBX

The PBX is busybox/alpine — many "standard" Linux commands are absent or behave differently. Verified against MikoPBX 2026.2.89-dev on 172.16.32.94.

### Data layout — do not edit the wrong SQLite file

Two unrelated DBs are routinely confused:

- `/cf/conf/mikopbx.db` — core MikoPBX (Extensions, Users, Sip, PbxExtensionModules). The `m_ModuleAutoprovision*` tables here are **stale leftovers from older versions** and ignored at runtime.
- `/storage/usbdisk1/mikopbx/custom_modules/ModuleAutoprovision/db/module.db` — the **live** module DB. All `m_ModuleAutoprovision*`, `m_Templates*`, `m_OtherPBX` reads/writes go here.

Phalcon's `ModuleAutoprovision::findFirst()` reads from `module.db`, so a `sqlite3 /cf/conf/mikopbx.db UPDATE ...` will appear to "work" yet have no effect. Always write via the Phalcon ORM, or hit `/storage/.../module.db` directly.

### Logger plumbing

`SystemMessages::sysLogMsg($ident, $message, $level)` does **not** use `$ident` as the syslog tag. It logs through Phalcon's logger with the message body `"$message on $ident"`, and the syslog tag ends up `php.backend[<pid>]` or `php.frontend[<pid>]`. Practical consequence:

- The `LOG_TAG` constants (`autoprovision-pnp` in `WorkerProvisioningServerPnP`, `autoprovision-http` in `GetController`) appear at the end of the line, after `" on "`.
- `grep autoprovision-pnp /storage/usbdisk1/mikopbx/log/system/messages` works (substring match in body) — that's the canonical filter.

### Common diagnostic commands

```bash
# PnP worker lifecycle and per-packet events
grep autoprovision-pnp /storage/usbdisk1/mikopbx/log/system/messages | tail -50

# HTTP delivery events (incoming requests, 200/404, vendor detection)
grep autoprovision-http /storage/usbdisk1/mikopbx/log/system/messages | tail -50

# Worker process
ps -ef | grep WorkerProvisioningServerPnP | grep -v grep

# Listening UDP ports — `ss` does NOT exist on MikoPBX, use netstat
netstat -ulnp | grep -E ':5060|:69 '

# Listening TCP ports (nginx must be on 8480 for provisioning HTTP)
netstat -ltnp | grep -E ':80|:8480|:443'

# Currently active module settings (live DB)
sqlite3 /storage/usbdisk1/mikopbx/custom_modules/ModuleAutoprovision/db/module.db \
  -header 'SELECT * FROM m_ModuleAutoprovision;'
```

### Enabling per-packet verbose logging

`WorkerProvisioningServerPnP::parseVerboseFlag()` reads `additional_params` as an INI snippet:

```ini
[debug]
verbose = 1
```

Update via Phalcon (raw `sqlite3 /cf/conf/mikopbx.db UPDATE ...` won't reach the worker — see "Data layout"):

```bash
php -r '
require_once "/usr/www/src/Core/Config/Globals.php";
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
$d = ModuleAutoprovision::findFirst();
$d->additional_params = "[debug]\nverbose = 1\n";
$d->save();
'
```

The worker reads settings only at `start()`; restart it: `pkill -9 -f WorkerProvisioningServerPnP`, then wait ~30 s for `WorkerSafeScriptsCore` to respawn it (SIGUSR1 alone reloads only some fields and is unreliable for `additional_params`). The next startup line should report `verbose=1`.

### Simulating a phone — PnP SUBSCRIBE

The worker ships its own client mode for end-to-end testing:

```bash
cd /storage/usbdisk1/mikopbx/custom_modules/ModuleAutoprovision/Lib
timeout 3 php -f WorkerProvisioningServerPnP.php \
  socket_client <BIND_IP> <BIND_PORT> <MAC_NO_COLONS>
# example:
timeout 3 php -f WorkerProvisioningServerPnP.php socket_client 172.16.32.94 5063 805ec086888f
```

It binds to `<BIND_IP>:<BIND_PORT>` (must be free), sends a SUBSCRIBE to `224.0.1.75:5060`, and prints the worker's `200 OK` + `NOTIFY` response on stdout. The NOTIFY body contains the URL the real phone would fetch — inspect it to confirm `{PBX_HOST}` substitution and the chosen vendor/model.

Verbose logs land in `/storage/usbdisk1/mikopbx/log/system/messages` as: `packet from <ip>:<port> method=SUBSCRIBE`, then `Request provisiong from ip: ...; mac=<r_mac>`, then `NOTIFY sent to <ip>:<port> mac=<mac> vendor=<v> model=<m>`.

Caveat: `r_mac` is obtained via `busybox arp -D <ip>` and falls back to garbage like `"in"` (from `"No match found in 4 entries"`) when ARP can't resolve the IP — common when testing from the PBX itself (its own IP isn't in its ARP table). The downstream code uses `$headers['mac']` (parsed from the SUBSCRIBE URI), not `r_mac`, so this is cosmetic log noise.

### Simulating a phone — HTTP config fetch

Two paths are wired separately in `GetController::getConfigStatic()`:

```bash
# Per-MAC path — substitutes {SIP_USER_NAME}/{SIP_NUM}/{SIP_PASS}/{PBX_HOST}/{FIRMWARE_URL}.
# Requires a row in m_TemplatesUsers binding the MAC to a templateId that exists in m_Templates.
curl -sv -A 'Yealink SIP-T19P 53.84.0.50' \
  http://127.0.0.1:8480/pbxcore/api/autoprovision-http/<MAC>.cfg

# URI template path — substitutes only {PBX_HOST}/{FIRMWARE_URL}; {SIP_*} are left
# literal by design (operator warning logged as warn=unresolved-sip-placeholder).
curl -sv -A 'Snom 765/8.7.5.49' \
  http://127.0.0.1:8480/pbxcore/api/autoprovision-http/snom-D785.htm
```

**Trap with URI templates**: the route condition is `':uri: LIKE TemplatesUri.uri'` — the column is the pattern, the request URI is the value. Phalcon receives the request URI with a leading slash (`/snom-D785.htm`); a `m_TemplatesUri.uri` value without that slash will never match. Entries should be stored either with leading slash or with a `%` wildcard.

### Common-symptom decoder

- **PnP NOTIFY arrives but URL is `http://:8480/...`** — `m_ModuleAutoprovision.pbx_host` is empty. The renderer has **no fallback** for this; phones drop the malformed URL. Fill the "PBX host" field in the module settings before debugging anywhere else.
- **HTTP returns 200 but `{SIP_USER_NAME}` / `{SIP_NUM}` survive verbatim in the config the phone downloads** — the phone fetched a *URI template* (no MAC in URL), where per-user substitution is intentionally skipped. Either route the phone to its per-MAC URL via PnP, or accept that shared URI templates can't carry SIP credentials.
- **Asterisk logs `Request 'SUBSCRIBE' from '<sip:MAC...@224.0.1.75>' ... No matching endpoint found`** — benign pjsip rejection of the same multicast packet `WorkerProvisioningServerPnP` consumes via `SOCK_RAW` + `MCAST_JOIN_GROUP`. Not evidence the worker missed it. Cross-check `grep autoprovision-pnp .../messages` for the matching `packet from ...` line under verbose.
- **`grep autoprovision-pnp` returns nothing even though the worker is in `ps`** — verbose is off and no SUBSCRIBE packets have arrived since the last startup. Lifecycle lines (`PnP listener starting`, `Joined multicast group`, `PnP listener ready`) are emitted only on startup; trigger a restart to confirm the worker is logging at all.
- **Per-MAC HTTP returns 404** — either no `m_TemplatesUsers` row for that MAC, or `m_TemplatesUsers.templateId` points to a missing row in `m_Templates`. Check both joins manually.
