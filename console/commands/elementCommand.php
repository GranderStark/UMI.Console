<?php
  /**
   * TODO descrp
   * @author Ilya Rogov <ilyar.software@gmail.com>
   * @package umi.console.command
   */
  class elementCommand extends consoleCommand {
    protected $domainId = false, $langId = false;
    public $default_action = 'list';

    public function actionAdd($rel_id = false, $hierarchy_type_id = "co", $name = false, $alt_name = false, $type_id = false, $domain_id = false, $lang_id = false, $tpl_id = false, $argv, $flags) {
      $iHierarchy = umiHierarchy::getInstance();

      $new_element_id = $iHierarchy->addElement($rel_id, $hierarchy_type_id, $name, $alt_name);
      $oElement = $iHierarchy->getElement($new_element_id);

      return true;
    }

    /**
     * Установить источник данных для элемента
     *
     */
    public function actionChobj($param0, $param1, $flags = false) {
      $iHierarchy = umiHierarchy::getInstance();
      $oElement = $iHierarchy->getElement($param0, true);
      $object = umiObjectsCollection::getInstance()->getObject($param1);
      $out = null;
      if (($oElement instanceof umiHierarchyElement) and ($object instanceof umiObject)) {
        $oElement->setObject($object);
        $oElement->commit();
        if (isset($flags['d'])) {
          $out .= " " . $oElement->getId();
        }
      } else {
        $out .= "Елемента с id $param0 несуществует, показать все елементы: umi elemet list --depth = 0";
      }

      echo $out;

      return true;
    }

    /**
     * Управление правами на страниц
     * umi element permission rootId [ownerId] [level | [--reset]]
     *
     * Установить права level на страницу rootId и ее подстраницы для пользователя или группы
     * umi element permission {rootId} {ownerId} {level}
     *
     * Удалить все права на страницу rootId и ее подстраницы для всех пользователей
     * umi element permission {rootId} --reset
     *
     * Удалить все права на страницу rootId и ее подстраницы для пользователя или группы
     * umi element permission {rootId} {ownerId} --reset
     *
     * Удалить все права на страницу rootId и ее подстраницы для всех пользователей и установить права level для пользователя или группы
     * umi element permission {rootId} {ownerId} {level} --reset
     *
     * Дополнительные параметры выборки и значения поумолчанию
     * --domainId = 1 указать индентификатор домена (актуально если ишем от корня: rootId = 0)
     * --langId = 1 указать индентификатор языка (актуально если ишем от корня: rootId = 0)
     * --unActive = true если true, то в результат будут включены неактивные страницы
     * --unVisible = true если true, то в результат будут включены невидимые в меню страницы
     * --depth = 0  глубина поиска
     * --typeId = false включить в результат только страницы с указанным индентификатора базового типа (umiHierarchyType)
     * --addSelf = true включить в результат rootId
     */
    public function actionPermission($param0 = 0, $param1 = false, $param2 = false, $reset = false, $unActive = true, $unVisible = true, $depth = 0,  $typeId = false,
      $domainId = false, $addSelf = true, $langId = false) {

      $elements = $this->getChildIds((int) $param0, $unActive, $unVisible, $depth, $typeId, $domainId, $addSelf, $langId);

      $permissions = permissionsCollection::getInstance();

      $out = "";

      foreach ($elements as $elementId) {
        $out .= " elementId: {$elementId}";
        if ($reset !== false && $param1 !== false && $param2 !== false) {
          $permissions->resetElementPermissions($elementId);
          $out .= " reset all";
        } elseif ($reset !== false){
          $permissions->resetElementPermissions($elementId, $param1);
          $out .= $param1  !== false ?" reset ownerId: {$param1}": " reset all";
        }

        if ($param1 && $param2 !== false) {
          $permissions->setElementPermissions($param1, $elementId, $param2);
          $out .= " set level: {$param2} for ownerId: {$param1}";
        }
        $out .= "\n";
      }

      $out .= "rootId {$param0} domainId {$this->domainId} langId {$this->langId} count element " . count($elements) . "\n";

      echo $out;

      return true;
    }

    /**
     * umi element list {root_id}
     */
    public function actionList($param0 = 0, $allow_unactive = true, $allow_unvisible = true, $depth = 0, $hierarchy_type_id = false, $domainId = 1, $langId = 1, $flags = false) {
      $hierarchy = umiHierarchy::getInstance();
      $childs = $hierarchy->getChilds($param0, $allow_unactive, $allow_unvisible, $depth, $hierarchy_type_id, $domainId, $langId);

      $out = $this->printLevel($childs, $flags);

      if (isset($flags['c'])) {
        $out .= " count " . $hierarchy->getChildsCount($param0, $allow_unactive, $allow_unvisible, $depth, $hierarchy_type_id, $domainId) . "\n";
      }
      if (isset($flags['a'])) {
        $out .= " total " . $hierarchy->getChildsCount($param0, true, true, 0, false, $domainId) . "\n";
      }

      echo $out;

      return true;
    }

    public function printLevel($childs, $flags, $space = "") {
      $out = null;
      foreach ($childs as $element_id => $child) {
        $out .= $space . $this->actionView($element_id, $flags, false) . "\n";
        if (count($child)) {
          $out .= $this->printLevel($child, $flags, $space . " ");
        }
      }
      return $out;
    }

    public function actionView($param0, $flags = false, $ln = true) {
      $out = null;
      $param0 = is_array($param0) ? $param0 : array($param0);

      foreach ($param0 as $element_id) {
        $oElement = umiHierarchy::getInstance()->getElement($element_id, true);
        if ($oElement instanceof umiHierarchyElement) {
          $out .= " " . $oElement->getId();
          $out .= " " . $oElement->getName();
          if (isset($flags['h'])) {
            $out .= " " . $oElement->getTypeId();
          }
          if (isset($flags['t'])) {
            $out .= " " . $oElement->getObjectTypeId();
          }
          if (isset($flags['o'])) {
            $out .= " " . $oElement->getObjectId();
          }
        } else {
          $out .= "Елемента с id $param0 несуществует, показать все елементы: umi elemet list -A";
        }

        if ($ln) {
          $out .= "\n";
        }
      }

      if ($ln) {
        echo $out;
      } else {
        return $out;
      }

      return true;
    }

    /**
     * Получить список индентификаторов потомков по отношению к $rootId на глубину $depth
     *
     * @param $rootId
     * @param bool $unActive=true если true, то в результат будут включены неактивные страницы
     * @param bool $unVisible=true если true, то в результат будут включены невидимые в меню страницы
     * @param int $depth=0 глубина поиска
     * @param bool $typeId=false включить в результат только страницы с указанным индентификатора базового типа (umiHierarchyType)
     * @param int|bool $domainId=false указать индентификатор домена (актуально если ишем от корня: $rootId = 0)
     * @param bool $addSelf=false включить в результат $rootId
     * @param int|bool $langId=false указать индентификатор языка (актуально если ишем от корня: $rootId = 0)
     * @return array массив индентификаторов потомков
     */
    private function getChildIds($rootId, $unActive = true, $unVisible = true, $depth = 0, $typeId = false, $domainId = false, $addSelf = true, $langId = false) {

      $childIds = array();

      if(is_int($rootId)) {

        $hierarchy = umiHierarchy::getInstance();
        $page = $hierarchy->getElement($rootId);
        if($page instanceof umiHierarchyElement) {
          $this->domainId = !$domainId  ? $page->getDomainId(): $domainId;
          $this->langId = !$langId ? $page->getLangId(): $langId;
        }

        if($addSelf) { $childIds[] = $rootId; }

        $childs = $hierarchy->getChilds($rootId, $unActive, $unVisible, $depth, $typeId, $this->domainId, $this->langId);

      } elseif(is_array($rootId)) { $childs = $rootId; }

      if ($childs === false) return $childIds;

      foreach ($childs as $childId => $value) {
        $childIds[] = $childId;
        $childIds = array_merge($childIds, $this->getChildIds($value, $unActive, $unVisible, $typeId, $domainId, false, $langId));
      }
      $childIds = array_unique($childIds);
      return $childIds;
    }

    public function actionTpl($param0 = 0, $param1 = 1, $unActive = true, $unVisible = true, $depth = 0,  $typeId = false, $domainId = false, $addSelf = true, $langId = false) {

      $elements = $this->getChildIds((int) $param0, $unActive, $unVisible, $depth, $typeId, $domainId, $addSelf, $langId);
      $hierarchy = umiHierarchy::getInstance();

      $out = "";
      foreach ($elements as $elementId) {
        $out .= " elementId: {$elementId}";
        $element = $hierarchy->getElement($elementId);
        if($element instanceof umiHierarchyElement) {
          $element->setTplId($param1);
          $element->commit();
          $out .= " set templateId: $param1\n";
        } else {
          $out .= " error load element\n";
        }
      }

      $out .= "rootId {$param0} domainId {$this->domainId} langId {$this->langId} count element " . count($elements) . "\n";
      echo $out;

      return true;
    }
  }
