# 📦 BagIt PHP — *Wizdam Edition*

**A modern, PHP 8.x‑ready library for creating, manipulating and validating [BagIt](https://datatracker.ietf.org/doc/rfc8493/) bags – the standard packaging format for digital preservation in the [LOCKSS Personal Lockers Network (PLN)](https://www.lockss.org/).**

---

<p align="center">
  <a href="https://github.com/mokesano/bagit-php">
    <img src="https://img.shields.io/badge/PHP-^8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  </a>
  <a href="https://github.com/mokesano/bagit-php/blob/main/LICENSE">
    <img src="https://img.shields.io/badge/license-GPL%203.0--only-blue?style=for-the-badge" alt="License">
  </a>
  <a href="https://packagist.org/packages/wizdam/bagit-php">
    <img src="https://img.shields.io/badge/packagist-wizdam%2Fbagit--php-F28D1A?style=for-the-badge&logo=packagist&logoColor=white" alt="Packagist">
  </a>
  <a href="https://github.com/mokesano/bagit-php/actions">
    <img src="https://img.shields.io/badge/build-passing-brightgreen?style=for-the-badge&logo=github-actions&logoColor=white" alt="Build">
  </a>
  <a href="https://github.com/mokesano/bagit-php/security/advisories">
    <img src="https://img.shields.io/badge/security-policy-important?style=for-the-badge&logo=github" alt="Security Policy">
  </a>
</p>

<br>

<p align="center">
  <em>🔐 Ensure integrity · 📋 Generate manifests · 🌐 Fetch remote content · ✅ Validate bags</em>
</p>

---

## 📖 About the Project

BagIt PHP is a **PHP implementation of the [BagIt 0.96 specification](https://wiki.ucop.edu/display/Curation/BagIt)** (now standardized as [RFC 8493](https://datatracker.ietf.org/doc/rfc8493/)). A "bag" is a self‑describing, integrity‑checked container that bundles digital payload files together with their metadata – making it ideal for archival storage, network transfer, and long‑term digital preservation.

This **Wizdam Edition** is a complete modernization of the original [scholarslab/BagItPHP](https://github.com/scholarslab/BagItPHP) library, fully refactored for **PHP 8.4+** compatibility, PSR‑4 autoloading, and seamless integration with Composer. It is purpose‑built to support the **LOCKSS PLN (Private LOCKSS Network)** ecosystem, where integrity‑verified content packages are critical for distributed preservation workflows.

---

## ✨ Key Features

| 🔧 Feature | 📝 Description |
| :--- | :--- |
| **Bag Compilation** | Create new bags from scratch with a single constructor call |
| **Manifest Generation** | Automatically generate `manifest-{sha1,md5}.txt` and `tagmanifest-{sha1,md5}.txt` |
| **Tag File Handling** | Read and write `bagit.txt`, `bag-info.txt` and custom tag files |
| **Remote Fetching** | Download files listed in `fetch.txt` via HTTP (optional) |
| **Validation** | Full integrity checking – checksum verification, required file/directory presence |
| **Compression** | Export bags as `.tgz` (gzip) or `.zip` archives |
| **Dual Hash Support** | Choose between **SHA‑1** and **MD5** hashing algorithms on the fly |
| **Extended Bag Mode** | Optional creation of `bag-info.txt`, `fetch.txt`, and tag manifests |
| **PHP 8.4+ Native** | Fully compatible with modern PHP – type‑safe, deprecation‑free |
| **PSR‑4 Autoloading** | PSR‑4 namespaced under `Wizdam\BagIt` for immediate Composer autoloading |

---

## 🚀 Installation

### Via Composer (Recommended)

```bash
composer require wizdam/bagit-php
```

### Manual Installation

Clone the repository and include the autoloader:

```bash
git clone https://github.com/mokesano/bagit-php.git
cd bagit-php
composer install
```

### Requirements

- **PHP** ≥ 8.4
- **PEAR `Archive_Tar`** ≥ 1.4 (automatically pulled by Composer)

---

## ⚡ Quick Start

### 🆕 Create a New Bag

```php
<?php

require_once 'vendor/autoload.php';

use Wizdam\BagIt\BagIt;

// Create a fresh bag in the "my-bag" directory
$bag = new BagIt('my-bag');

// Add a file to the payload
$bag->addFile('/path/to/source/document.pdf', 'document.pdf');

// Update checksums and manifests
$bag->update();

// Export as a gzipped tarball
$bag->package('my-bag');  // creates my-bag.tgz

echo "✅ Bag created successfully!\n";
```

### ✨ Create an Extended Bag with Metadata

```php
<?php

require_once 'vendor/autoload.php';

use Wizdam\BagIt\BagIt;

// Metadata to store in bag-info.txt
$bagInfo = [
    'Source-Organization'  => 'University of Virginia Library',
    'Bagging-Date'         => date('Y-m-d'),
    'External-Description' => 'Preservation copy of a scholarly article'
];

// Create an extended bag with metadata and remote fetching enabled
$bag = new BagIt(
    bag:          'extended-bag',
    validate:     true,    // run checksum validation after creation
    extended:     true,    // generate optional tag files
    fetch:        true,    // download files listed in fetch.txt
    bagInfoData:  $bagInfo
);

// Add files and register remote URLs
$bag->addFile('localfile.xml', 'data/localfile.xml');
$bag->fetch->add('https://example.org/remote-file.pdf', 'data/remote-file.pdf');

// Add more metadata
$bag->setBagInfoData('Internal-Sender-Identifier', 'archive-2025-001');

// Finalize the bag
$bag->update();
$bag->package('extended-bag', 'zip');  // create extended-bag.zip
```

### 🔍 Validate an Existing Bag

```php
<?php

use Wizdam\BagIt\BagIt;

// Open and validate an existing bag
$bag = new BagIt('path/to/existing-bag.tgz');

// Check validity
if ($bag->isValid()) {
    echo "✅ Bag is valid!\n";

    // List all payload files
    foreach ($bag->getBagContents() as $file) {
        echo "  📄 $file\n";
    }

    // Retrieve remote files if needed
    $bag->fetch->download();
} else {
    echo "❌ Validation errors found:\n";
    foreach ($bag->getBagErrors() as $error) {
        echo "  ⚠️  {$error['file']}: {$error['message']}\n";
    }
}
```

---

## 📚 API Overview

The library is organized into four source files, each providing a distinct capability:

| 📁 File | 🏷️ Class / Functions | 📋 Responsibility |
| :--- | :--- | :--- |
| `src/bagit.php` | `BagIt`, `BagItException` | Core bag logic – creation, file management, validation, compression |
| `src/bagit_manifest.php` | `BagItManifest` | Reading, writing, and validating manifest checksum files |
| `src/bagit_fetch.php` | `BagItFetch` | Managing `fetch.txt` entries and downloading remote payload files |
| `src/bagit_utils.php` | `rls()`, `rrmdir()`, `tmpdir()`, `endsWith()`, `filterArrayMatches()` | Filesystem and string utility functions |

### Core Methods (Class `BagIt`)

| Method | Description |
| :--- | :--- |
| `__construct($bag, $validate, $extended, $fetch, $bagInfoData)` | Initialize a bag – create new or open existing |
| `addFile($src, $dest)` | Copy a file into the bag's `data/` directory |
| `update()` | Regenerate all manifests and sanitize file names |
| `package($destination, $method)` | Compress to `.tgz` or `.zip` archive |
| `validate()` | Run full integrity checks (returns error list) |
| `isValid()` | Returns `true` if no validation errors exist |
| `getBagContents()` | List all files in the data directory |
| `getBagErrors()` | Return array of validation errors |
| `getBagInfo()` | Retrieve bag version, encoding, and hash algorithm |
| `setHashEncoding($algo)` | Switch between `'sha1'` and `'md5'` |
| `setBagInfoData($key, $value)` | Add or update metadata key–value pairs |

---

## 📦 BagIt – A Quick Primer

A BagIt "bag" is a hierarchical file layout:

```
my-bag/
├── bagit.txt                  # BagIt version and encoding
├── bag-info.txt               # Metadata (extended bags)
├── manifest-sha1.txt          # Checksums of payload files
├── tagmanifest-sha1.txt       # Checksums of tag files
├── fetch.txt                  # Remote file URLs (optional)
└── data/                      # Payload – the actual content
    ├── document.pdf
    ├── image.jpg
    └── subdir/
        └── another-file.xml
```

The specification ensures that each bag carries its own integrity verification data, making it self‑contained and suitable for archiving, network exchange, and long‑term preservation.

> 📘 Learn more: [RFC 8493 – The BagIt File Packaging Format](https://datatracker.ietf.org/doc/rfc8493/)

---

## 🤝 Contributing

We welcome contributions! Please review our [Contributing Guidelines](https://github.com/mokesano/bagit-php/blob/main/CONTRIBUTING.md) before submitting a pull request.

**Coding Standards:**
- PHP code must follow **PSR‑1** guidelines
- JavaScript follows [Crockford's conventions](http://javascript.crockford.com/code.html)
- All new features require updated unit tests and documentation

This project adheres to the [Contributor Covenant Code of Conduct](https://github.com/mokesano/bagit-php/blob/main/CODE_OF_CONDUCT.md). By participating, you agree to uphold these standards.

---

## 🔒 Security

Security is taken seriously. **Please do not publicly disclose any vulnerabilities.**

- **Reporting:** Send vulnerability reports to [security@sangia.org](mailto:security@sangia.org)
- **Acknowledgment:** The lead maintainer will respond within 48 hours
- **Advisories:** Published at [GitHub Security Advisories](https://github.com/mokesano/bagit-php/security/advisories)

Full details are in our [Security Policy](https://github.com/mokesano/bagit-php/blob/main/SECURITY.md).

---

## 📄 License

This project is distributed under the **GNU General Public License v3.0 (GPL‑3.0‑only)**. See [LICENSE](https://github.com/mokesano/bagit-php/blob/main/LICENSE) for the full text.

> **Note:** The original BagItPHP library by the University of Virginia / Scholars' Lab was released under Apache License 2.0. This fork retains attribution headers in source files and is relicensed under GPL‑3.0‑only for the Wizdam Edition.

---

## 🙏 Acknowledgments

| 🏷️ Attribution | 🔗 Reference |
| :--- | :--- |
| **Original Author** | Wayne Graham, Eric Rochester – University of Virginia Scholars' Lab |
| **Original Repository** | [scholarslab/BagItPHP](https://github.com/scholarslab/BagItPHP) *(no longer maintained)* |
| **Wizdam Edition Maintainer** | [Rochmady (mokesano)](https://github.com/mokesano) |
| **BagIt Specification** | [RFC 8493](https://datatracker.ietf.org/doc/rfc8493/) – J. Kunze, J. Littman, E. Madden, J. Scancella |
| **LOCKSS Program** | [lockss.org](https://www.lockss.org/) – Stanford University |
| **Dependency** | [PEAR `Archive_Tar`](https://pear.php.net/package/Archive_Tar) |

---

<p align="center">
  <br>
  <sub>Made with ❤️ for the digital preservation community</sub>
  <br><br>
  <a href="https://github.com/mokesano/bagit-php/stargazers">
    <img src="https://img.shields.io/github/stars/mokesano/bagit-php?style=social" alt="GitHub Stars">
  </a>
  <a href="https://github.com/mokesano/bagit-php/network/members">
    <img src="https://img.shields.io/github/forks/mokesano/bagit-php?style=social" alt="GitHub Forks">
  </a>
  <br><br>
  <sub>© 2026 Rochmady. Licensed under GPL‑3.0‑only.</sub>
</p>