<?php
  /**
   * TODO descrp
   *
   * @author Ilya Rogov <ilyar.software@gmail.com>
   * @package umi.console.command
   * @version 0.1.0
   */
  class objectCommand extends consoleCommand {
    public $default_action = 'help';

    public function actionHelp() {
      echo "no help" . PHP_EOL;
    }

    public function actionClone($param0) {
      $out = null;
      $objects = umiObjectsCollection::getInstance();
      $newObjectId = $objects->cloneObject($param0);
      if ($newObjectId) {

        $out = "Создан объекта с id $newObjectId";
      } else {
        $out = "Объекта с id $param0 несуществует";
      }
      echo $out;
    }

    public function actionValue($param0, $param1, $param2 = null) {
      $out = null;
      $objetc = umiObjectsCollection::getInstance()->getObject($param0);
      if ($objetc instanceof umiObject) {

        if ($param2 !== null) {
          $objetc->setValue($param1, $param2);
          $objetc->commit();
        }

        $out = $param1 . "=" . $objetc->getValue($param1);
      } else {
        $out = "Объекта с id $param0 несуществует";
      }
      echo $out;
    }

    public function actionEdit ($param0, $guid = null, $name = null, $flags = null) {
      $object = selector::get('object')->id($param0);

      $out = null;
      if ($object instanceof umiObject) {
        if($guid) { $object->setGUID($guid); }
        if($name) { $object->setName($name); }
        return true;
      } else {
        $out .= "Объекта данных с id {$param0} несуществует\n";
        $out .= "Показать все типы данных: umi type -A\n";
        echo $out;
        return false;
      }
    }
  }
