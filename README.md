# Namingo Registrar for FOSSBilling
Module for Implementing ICANN Accreditation Database Structure

## Installation

```bash
git clone https://github.com/getnamingo/fossbilling-registrar
mv fossbilling-registrar/Registrar /var/www/modules/
```

- Go to Extensions > Overview in the admin panel and activate "ICANN Registrar Accreditation".

## Upgrade

Before upgrading, make a database backup.

To upgrade, replace the existing module files with the latest version and open the FOSSBilling admin panel. The module update routine will ensure that the required database tables exist.

Recent versions add database support for reseller management and better ICANN/NIS2 contact validation.

Existing data is preserved. Uninstalling the module no longer drops database tables.

## Usage Instructions

The detailed usage instructions for this module are currently being written and will be available soon. This module is specifically designed to work with the Namingo Registrar project. Please check back later for full documentation and guidance on using this module with your Namingo Registrar setup.

## License

Apache License 2.0