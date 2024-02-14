<?php
require_once 'src/helpers/cli_helper.php';
require_once 'src/controllers/vending_machine_controller.php';

$configs = parse_ini_file_multi('src/configs/app_configs.ini', true);

$vendingMachine = new VendingMachineController(
    $configs['products'],
    $configs['allowed_coins']
);

$vendingMachine->runInterface();