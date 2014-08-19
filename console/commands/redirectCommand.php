<?php
  /**
   * TODO decr
   *
   * @author Ilia Rogov <ilyar.software@gmail.com>
   * @package umi.tools.console.command
   */

  class redirectCommand extends consoleCommand {
    public $default_action = 'list';

    /**
     * Добавить новое перенаправление
     * @param string $param0 адрес страницы, с которой осуществляется перенаправление
     * @param string $param1 адрес целевой страницы
     * @param int $param2 = 301 статус перенаправления
     */
    public function actionAdd($param0, $param1, $param2 = 301) {
      redirects::getInstance()->add($param0, $param1, (int) $param2);
      echo "adding redirect ";
      $this->actionGet($param0);
    }

    /**
     * Удалить перенаправление
     * @param int $param0 id перенаправления
     */
    public function actionDel($param0) {
      redirects::getInstance()->del((int) $param0);
    }

    /**
     * Получить список перенаправлений со страницы $source
     * @param string $param0 адрес страницы, с которой осуществляется перенаправление
     */
    public function actionGet($param0) {
      $redirects = redirects::getInstance()->getRedirectsIdBySource($param0);
      $this->printResult($redirects);
    }

    /**
     * Получить список перенаправлений
     */
    public function actionList($status = false) //$status = false)
    {
      $sql = "SELECT `id`, `source`, `target`, `status` FROM `cms3_redirects`";
      if ($status) {
        $sql .= " WHERE `status` = '{$status}'";
      }
      $result = l_mysql_query($sql);

      $redirects = array();
      while (list($id, $source, $target, $status) = mysql_fetch_row($result)) {
        $redirects[$id] = Array($source, $target, (int) $status);
      }
      $this->printResult($redirects);
    }

    /**
     * @param $redirects
     */
    public function printResult($result_array) {
      foreach ($result_array as $id => $result) {
        echo "{$id} ";
        foreach ($result as $value) {
          echo "{$value} ";
        }
        echo PHP_EOL;
      }
    }

    public function parseUri($uri) {
      return trim($uri, '/');
    }
  }
