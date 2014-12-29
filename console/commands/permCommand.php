<?php
  /**
   * Управление правами пользователей и групп
   *
   * @author Ilya Rogov <ilyar.software@gmail.com>
   * @package umi.console.command
   * @version 0.1.0
   */
  class permCommand extends consoleCommand {
    public $default_action = 'help';

    public function actionHelp() {
      echo "no help" . PHP_EOL;
    }

    /**
     * Очистить права на страницы у пользователя или группы
     * umi perm reset {user_id|group_id}
     */
    public function actionReset($param0) {
      $ownerId = (int) $param0;
      if ($ownerId) {
        $sql = "DELETE FROM cms3_permissions WHERE owner_id = '{$ownerId}'";
        l_mysql_query($sql);
        echo "Права пользователя или группы с id $param0 очищены\n";
      }
    }

    /**
     * Проверить наличие прав на страницы у пользователя или группы
     * umi perm chech {user_id|group_id}
     */
    public function actionCheck($param0) {
      $resultPermission = (int) permissionsCollection::getInstance()->hasUserPermissions($param0);
      $out = "Права пользователя или группы с id $param0 на $resultPermission страниц\n";
      echo $out;
    }

    public function actionCopy($param0, $param1) {
      $out = null;
      permissionsCollection::getInstance()->copyHierarchyPermissions($param0, $param1);
      $out = "Скопировать права на все страницы из $param0 в $param1";
      echo $out;
    }
  }
