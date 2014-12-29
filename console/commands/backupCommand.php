<?php
  /**
   * Выполнение бекапа
   * @package umi.tools.console.command
   */
  class backupCommand extends consoleCommand {

    public function run($argv) {
      $mcfg = new baseXmlConfig(SYS_KERNEL_PATH . "subsystems/manifest/manifests/MakeSystemBackup.xml");
      $manifest = new manifest($mcfg);
      $manifest->hibernationsCountLeft = -1;
      $manifest->setCallback(new sampleManifestCallback());
      $manifest->execute();
      unset($manifest);
      return true;
    }
  }
