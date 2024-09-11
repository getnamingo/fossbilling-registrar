<?php
/**
 * Copyright 2022-2023 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Registrar;

use FOSSBilling\InjectionAwareInterface;
use RedBeanPHP\OODBBean;

class Service implements InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }
    
    /**
     * Creates the database structure to store the records in.
     */
    public function install(): bool
    {
        $sql = '
        -- Domain Meta Table
        CREATE TABLE IF NOT EXISTS `domain_meta` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `domain_id` bigint(20) NOT NULL,
            `registry_domain_id` varchar(100) DEFAULT NULL,
            `reseller` varchar(255) DEFAULT NULL,
            `reseller_url` varchar(255) DEFAULT NULL,
            `registrant_contact_id` varchar(100) DEFAULT NULL,
            `admin_contact_id` varchar(100) DEFAULT NULL,
            `tech_contact_id` varchar(100) DEFAULT NULL,
            `billing_contact_id` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE (`domain_id`),
            FOREIGN KEY (`domain_id`) REFERENCES `service_domain`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- Domain Status Table
        CREATE TABLE IF NOT EXISTS `domain_status` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `domain_id` bigint(20) NOT NULL,
            `status` varchar(100) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE (`domain_id`),
            FOREIGN KEY (`domain_id`) REFERENCES `service_domain`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DNSSEC Table
        CREATE TABLE IF NOT EXISTS `domain_dnssec` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `domain_id` bigint(20) NOT NULL,
            `key_tag` int(11) NOT NULL,
            `algorithm` varchar(10) NOT NULL,
            `digest_type` varchar(10) NOT NULL,
            `digest` text NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE (`domain_id`),
            FOREIGN KEY (`domain_id`) REFERENCES `service_domain`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ';
        $this->di['db']->exec($sql);

        return true;
    }

    /**
     * Removes the records from the database.
     */
    public function uninstall(): bool
    {
        $this->di['db']->exec('DROP TABLE IF EXISTS `domain_meta`');
        $this->di['db']->exec('DROP TABLE IF EXISTS `domain_status`');
        $this->di['db']->exec('DROP TABLE IF EXISTS `domain_dnssec`');

        return true;
    }

    /**
     * Method to update module. When you release new version to
     * extensions.fossbilling.org then this method will be called
     * after the new files are placed.
     *
     * @param array $manifest - information about the new module version
     *
     * @return bool
     *
     * @throws InformationException
     */
    public function update(array $manifest): bool
    {
        // throw new InformationException("Throw exception to terminate module update process with a message", array(), 125);
        return true;
    }

    /**
     * Methods is a delegate for one database row.
     *
     * @param array $row - array representing one database row
     * @param string $role - guest|client|admin who is calling this method
     * @param bool $deep - true|false deep or light version of result to return to API
     *
     * @return array
     */
    public function toApiArray(array $row, string $role = 'guest', bool $deep = true): array
    {
        return $row;
    }
}