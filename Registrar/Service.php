<?php
/**
 * Namingo Registrar for FOSSBilling (https://fossbilling.org/)
 *
 * Module for Implementing ICANN Accreditation Database Structure
 * Written in 2025-2026 by Taras Kondratyuk (https://namingo.org)
 *
 * @license Apache-2.0
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
            UNIQUE (`domain_id`, `status`),
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
            UNIQUE (`key_tag`),
            FOREIGN KEY (`domain_id`) REFERENCES `service_domain`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ';
        $this->di['db']->exec($sql);
        $this->installV120Tables();

        return true;
    }

    /**
     * Uninstalls the module.
     *
     * Module data is intentionally preserved to avoid accidental loss of
     * domain, DNSSEC, reseller, and contact validation records.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        // Preserve all module data on uninstall.
        // Use a separate manual purge if destructive cleanup is required.
        return true;
    }

    /**
     * Updates the module after new files are placed.
     *
     * Ensures that the latest database tables are present. Existing data is
     * preserved.
     *
     * @return bool
     */
    public function update(array $manifest): bool
    {
        $this->installV120Tables();

        return true;
    }

    private function installV120Tables(): void
    {
        $sql = '
        -- Domain Resellers Table
        CREATE TABLE IF NOT EXISTS `domain_reseller` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(100) NOT NULL,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) DEFAULT NULL,
            `url` varchar(255) DEFAULT NULL,
            `country` char(2) DEFAULT NULL,
            `status` enum(\'active\',\'suspended\',\'terminated\') NOT NULL DEFAULT \'active\',
            `notes` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `identifier` (`identifier`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- Domain Reseller Mapping Table
        CREATE TABLE IF NOT EXISTS `domain_reseller_domain` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `reseller_id` bigint(20) NOT NULL,
            `domain` varchar(255) NOT NULL,
            `service_domain_id` bigint(20) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `domain` (`domain`),
            KEY `reseller_id` (`reseller_id`),
            KEY `service_domain_id` (`service_domain_id`),
            CONSTRAINT `domain_reseller_domain_reseller_fk`
                FOREIGN KEY (`reseller_id`) REFERENCES `domain_reseller`(`id`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- ICANN / NIS2 Contact Validation Table
        CREATE TABLE IF NOT EXISTS `domain_contact_validation` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `client_id` bigint(20) NOT NULL,
            `is_validated` tinyint(1) NOT NULL DEFAULT 0,
            `validation_checked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `validation_method` varchar(100) DEFAULT NULL,
            `validation_token` varchar(255) DEFAULT NULL,
            `validation_log` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `client_id` (`client_id`),
            KEY `is_validated` (`is_validated`),
            KEY `validation_checked_at` (`validation_checked_at`),
            KEY `validation_token` (`validation_token`),
            CONSTRAINT `domain_contact_validation_client_fk`
                FOREIGN KEY (`client_id`) REFERENCES `client`(`id`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ';

        $this->di['db']->exec($sql);
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