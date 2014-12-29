<?php
  /**
   * Получить основную информацию о текущей системе
   * Get basic information about the current system
   *
   * @author Ilia Rogov <ilyar.software@gmail.com>
   * @package umi.tools.console.command
   */
  class infoCommand extends consoleCommand {
    public function run($argv) {

      $regedit = regedit::getInstance();
      echo "Редакция системы: " . $regedit->getVal("//modules/autoupdate/system_edition") . "\n";
      echo "Дата последнего обновления: " . date("d/m/Y H:i", $regedit->getVal("//modules/autoupdate/last_updated")) . "\n";
      echo "Версия системы: " . $regedit->getVal("//modules/autoupdate/system_version") . "\n";
      echo "Ревизия: " . $regedit->getVal("//modules/autoupdate/system_build") . "\n";
      echo "Драйвер БД: " . DB_DRIVER . "\n";
      echo "Домены: \n";

      $domains = domainsCollection::getInstance()->getList();
      foreach ($domains as $domain) {
        if ($domain instanceof domain) {
          echo " " . $domain->getHost() . " \n";
        }
      }

      echo "\n";

      return true;
    }
  }
