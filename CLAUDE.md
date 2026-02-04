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
