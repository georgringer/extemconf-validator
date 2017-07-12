# Validation for `ext_emconf.php` files of TYPO3 extensions

TYPO3 uses a file named `ext_emconf.php` for declarations of extensions. You can find detailed information about it at https://docs.typo3.org/typo3cms/CoreApiReference/ExtensionArchitecture/DeclarationFile/Index.html

**Installation**

```
composer require georgringer/extemconf-validator:dev-master
```

### Usage

#### CLI

Multiple usages are possible:

```
// Check all extensions in given directory
./bin/emconf-validate emconf:validate --file=typo3conf/ext/

// Check single file
./bin/emconf-validate emconf:validate --file=typo3conf/ext/myextension/ext_emconf.php

```

### Direct call

Use the following code to check a file

```
$fileValidation = new \GeorgRinger\ExtemconfValidator\Validator();
try {
    $fileValidation->validate($file);
} catch (\Exception $e) {
    echo $e->getMessage());
}
```

## Syntax

The following syntax is currently checked:

### title

- required
- string
- minimum length: 10

### description

- required
- string
- minimum length: 50

### category

- required
- string
- one of the following: `be`, `module`, `fe`, `plugin`, `misc`, `services`, `templates`, `example`, `doc`, `distribution`

### author

- required
- string
- minimum length: 10

### state

- required
- string
- one of the following: `alpha`, `beta`, `stable`, `experimental`, `test`, `obsolute`, `excludeFromUpdates`

### constraints

- required
- array with the following keys: `depends`, `conflicts`, `suggests`

### uploadfolder

- optional
- boolean

### shy

- optional
- boolean

### clearCacheOnLoad

- optional
- boolean

### Deprecated configuration

The following keys are deprecated and must not be used anymore

- dependencies
- conflicts
- suggests 
- docPath 
- CGLcompliance
- CGLcompliance_note
- private
- download_password
- shy
- loadOrder
- priority
- internal
- modify_tables
- module
- lockType
- TYPO3_version
- PHP_version

## Todos:

- Check author_email, author_company
- check author as arrays as proposed by extension_builder
- validate constraints
