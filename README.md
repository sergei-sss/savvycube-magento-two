The extension can be installed from the repository using Magento Composer.
To accomplish that, log in into your server and run the following commands:

```sh
cd /path/to/your/magento/root
composer config repositories.savvycube vcs https://github.com/savvycube/magento-two
composer require savvycube/module-connector:dev-master
php bin/magento module:enable SavvyCube_Connector
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

In the future, to update the extension to the latest version,
run the following commands:

```sh
cd /path/to/your/magento/root
composer update savvycube/module-connector
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
