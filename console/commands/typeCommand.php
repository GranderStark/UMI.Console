<?php
  /**
   * Типы данных
   * @package umi.tools.console.command
   */
  class typeCommand extends consoleCommand {
    public $default_action = 'list';

    public function actionList($flags) {

      $out = null;

      // umi type -h # вывести список иерархических типов
      if (isset($flags['h'])) {
        $hierarchy_types = umiHierarchyTypesCollection::getInstance()->getTypesList();
        foreach ($hierarchy_types as $type_id => $type) {
          if ($type instanceof umiHierarchyType) {
            $out .= " " . $type_id;
            $out .= " " . $type->getTitle();
            $out .= " " . $type->getModule() . " " . $type->getMethod();
            $out .= "\n";
          }
        }

        if (isset($flags['c'])) {
          $out .= "count " . count($hierarchy_types) . "\n";
        }
      } else {
        $all_types = umiObjectTypesCollection::getInstance()->getAllTypes();

        foreach ($all_types as $type_id => $type) {

          $objectType = umiObjectTypesCollection::getInstance()->getType($type_id);

          if ($objectType instanceof umiObjectType) {
            $out .= " " . $objectType->getId();
            $out .= " " . $objectType->getName();
            $out .= " " . $objectType->getGUID();
            if (isset($flags['g'])) {
              $out .= $objectType->getIsGuidable() ? " Guidable" : " ";
            }
            if (isset($flags['l'])) {
              $out .= $objectType->getIsLocked() ? " Locked" : " ";
            }
            $out .= "\n";
          }
        }

        if (isset($flags['c'])) {
          $out .= "count " . count($all_types) . "\n";
        }
      }

      echo $out;

      return true;
    }

    /**
     * Copy group, field, etc for types
     * umi type cp --group=name sourse_type target_type
     * @param string $param0
     * @param string $param1
     * @param string $group
     * @param string $field
     * @param array $flags
     */
    public function actionCp($param0, $param1, $group = 'all', $field = 'all', $flags = NULL) {
      $objectType = umiObjectTypesCollection::getInstance()->getType($param0);

      $out = null;
      if ($objectType instanceof umiObjectType) {

        if ($group) {
          $oFieldsGroup = $objectType->getFieldsGroup($group, true);
          if ($oFieldsGroup === false) {
            $oFieldsGroup = $objectType->getFieldsGroupByName($group, true);
          }
          if ($oFieldsGroup instanceof umiFieldsGroup) {
            //TODO
          } else {
            $out .= "У типа данных с id $param0 несуществует группы $group\n";
            $out .= "Показать тип данных: umi type view $param0\n";
          }
        }

        $fields_groups = $objectType->getFieldsGroupsList(true);
        foreach ($fields_groups as $oFieldGroup) {
          if ($oFieldGroup instanceof umiFieldsGroup) {
            // group
            if ($group == 'all' | $group == $oFieldGroup->getName()) {
              $oTargetType = umiObjectTypesCollection::getInstance()->getType($param1);

              $oNewFieldsGroup = $oTargetType->getFieldsGroupByName($oFieldGroup->getName());
              if ($oNewFieldsGroup === false) {
                $new_group_id = $oTargetType->addFieldsGroup($oFieldGroup->getName(), $oFieldGroup->getTitle(), $oFieldGroup->getIsActive(), $oFieldGroup->getIsVisible());
                $oNewFieldsGroup = $oTargetType->getFieldsGroup($new_group_id, true);
              }

              $oNewFieldsGroup->setIsLocked($oFieldGroup->getIsLocked());

              // field
              $fields_group = $oFieldGroup->getFields();
              foreach ($fields_group as $oField) {
                if ($field == 'all' | $field == $oField->getName()) {
                  $oNewFieldsGroup->attachField($oField->getId());
                }
              }
            }
          }
        }
      } else {
        $out .= "Типа данных с id $param0 несуществует\n";
        $out .= "Показать все типы данных: umi type -A\n";
      }

      echo $out;

      return true;
    }

    /**
     * Move or rename gruop, field for types
     * umi type mv --group=name|id type_id new_name|new_title -n|-t
     * @param string $param0
     * @param string $param1
     * @param string|bool $group
     * @param string|bool $field
     * @param array|null $flags
     * @return bool
     */
    public function actionMv($param0, $param1, $group = false, $field = false, $flags = null) {
      $objectType = umiObjectTypesCollection::getInstance()->getType($param0);

      $out = null;
      if ($objectType instanceof umiObjectType) {

        // Rename name or title gruop
        if ($group) {
          $oFieldsGroup = $objectType->getFieldsGroup($group, true);
          if ($oFieldsGroup === false) {
            $oFieldsGroup = $objectType->getFieldsGroupByName($group, true);
          }
          if ($oFieldsGroup instanceof umiFieldsGroup) {
            if (isset($flags['n'])) {
              $oFieldsGroup->setName($param1);
            }
            if (isset($flags['t'])) {
              $oFieldsGroup->setTitle($param1);
            }
            $this->actionView($objectType->getId());
          } else {
            $out .= "У типа данных с id $param0 несуществует группы $group\n";
            $out .= "Показать тип данных: umi type view $param0\n";
          }
        }
      } else {
        $out .= "Типа данных с id $param0 несуществует\n";
        $out .= "Показать все типы данных: umi type --all\n";
      }

      echo $out;

      return true;
    }

    public function actionView(array $param0, $flags = NULL) {
      $param0 = array_unique($param0);
      $out = null;

      foreach ($param0 as $type_id) {
        $objectType = umiObjectTypesCollection::getInstance()->getType((int) $type_id);
        if ($objectType instanceof umiObjectType) {

          $out .= " " . $objectType->getId();
          $out .= " " . $objectType->getGUID();
          $out .= " " . $objectType->getName();
          $out .= "\n";

          if (isset($flags['A'])) {
            foreach ($objectType->getFieldsGroupsList() as $fieldsGroup) {

              $out .= " fieldsgroup " . $fieldsGroup->getName() . " (" . $fieldsGroup->getTitle() . ")\n";

              foreach ($fieldsGroup->getFields() as $field) {
                $out .= "  " . $field->getName() . " (" . $field->getTitle() . ")\n";
              }
            }
          }
        } else {
          $out .= "Типа данных с id $type_id несуществует\n";
          $out .= "Показать все типы данных: umi type -A\n";
        }
      }

      echo $out;
      return true;
    }

    public function actionEdit($param0, $guid = null, $locked = false, $delete = false,  $flags = null) {
      $objectTypes = umiObjectTypesCollection::getInstance();
      $objectType = $objectTypes->getType($param0);

      $out = null;
      if ($objectType instanceof umiObjectType) {
        if ($guid) { $objectType->setGUID($guid); }
        if ($delete !== false || isset($flags['d'])) { $objectTypes->delType($objectType->getId()); }
        if ($locked !== false || isset($flags['l'])) { $objectType->setIsLocked(true); }
        elseif ($locked == 'no' || (isset($flags['u']) && isset($flags['l']))) { $objectType->setIsLocked(false); }
      } else {
        $out .= "Типа данных с id $param0 несуществует\n";
        $out .= "Показать все типы данных: umi type -A\n";
      };
      echo $out;
      return true;
    }
  }
