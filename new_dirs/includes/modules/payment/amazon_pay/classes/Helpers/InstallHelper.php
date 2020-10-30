<?php

namespace AlkimAmazonPay;


class InstallHelper
{
    protected function addConfiguration(){
        $configHelper = new ConfigHelper();
        $migrationHelper = new MigrationHelper();
        foreach($configHelper->getConfigurationFields() as $field=>$fieldInfo){
            if($fieldInfo['type'] !== ConfigHelper::FIELD_TYPE_READ_ONLY) {
                if ($configHelper->getConfigurationValue($field) === null) {
                    $value = $migrationHelper->getLegacyValue($field);
                    if ($value === null) {
                        $value = '';
                        if($fieldInfo ['type'] === ConfigHelper::FIELD_TYPE_BOOL){
                            $value = 'False';
                        }elseif($fieldInfo ['type'] === ConfigHelper::FIELD_TYPE_SELECT){
                            $value = $fieldInfo['options'][0]['id'];
                        }elseif($fieldInfo ['type'] === ConfigHelper::FIELD_TYPE_STATUS){
                            $value = -1;
                        }
                    }
                    $configHelper->addConfigurationValue($field, $value);
                }
            }
        }
    }

    protected function addTable(){
        xtc_db_query("
        CREATE TABLE IF NOT EXISTS `amazon_pay_transactions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `reference` varchar(255) NOT NULL,
          `merchant_id` varchar(32) DEFAULT NULL,
          `mode` varchar(16) DEFAULT NULL,
          `type` varchar(16) NOT NULL,
          `time` datetime NOT NULL,
          `expiration` datetime NOT NULL,
          `charge_amount` float NOT NULL,
          `captured_amount` float NOT NULL,
          `refunded_amount` float NOT NULL,
          `currency` varchar(16) DEFAULT NULL,
          `status` varchar(32) NOT NULL,
          `last_change` datetime NOT NULL,
          `last_update` datetime NOT NULL,
          `order_id` int(11) NOT NULL,
          `customer_informed` tinyint(1) NOT NULL,
          `admin_informed` tinyint(1) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `reference` (`reference`),
          KEY `type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

    public function process(){
        $this->addTable();;
        $this->addConfiguration();
        $rs = xtc_db_query("show columns from ".TABLE_ADMIN_ACCESS." like 'amazon_pay_configuration'");
        if (xtc_db_num_rows($rs) === 0) {
            xtc_db_query("ALTER TABLE `". TABLE_ADMIN_ACCESS."` ADD `amazon_pay_configuration` TINYINT(1) DEFAULT 1");
        }
        $configHelper = new ConfigHelper();
        $configHelper->resetKey();
    }
}