# Media Manager

Craft CMS plugin for synchronize Media entries data from PBS API

## Pre-installation
1. Append following line in `composer.json` :

   ```
   "repositories": [
      {
        "type": "vcs",
        "url": "https://github.com/pbs-digital/pbs-media-manager-craft-plugin.git"
      },
   ]
   ```
2. Append another line :

   ```
   "require": {
     ...
     "pbs-digital/pbs-media-manager-craft-plugin": "^4.1.0"
   }
   ```


## Installation
1. Run `composer clearcache`
1. Run `composer update` or you can install the package only by running `composer require pbs-digital/pbs-media-manager-craft-plugin`
2. Install plugin through admin `Settings > Plugins`.
