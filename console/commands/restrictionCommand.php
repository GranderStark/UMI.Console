<?php
/**
 * TODO Enter description here ...
 * @package umi.tools.console.command
 */
class restrictionCommand extends consoleCommand {

	public function actionMain () {

		echo "todo help\n";
	}

	public function actionFix () {
		$sql = "DELETE FROM  cms3_object_fields_restrictions WHERE  id = '7'";
		$result = l_mysql_query($sql);
		if($error = l_mysql_error()) {
			$out .= "error 1  \n";
		} else {
			$out .= "ok 1  \n";
		}

		$sql = "DELETE FROM  cms3_object_fields_restrictions WHERE  id = '8'";
		$result = l_mysql_query($sql);
		if($error = l_mysql_error()) {
			$out .= "error 2  \n";
		} else {
			$out .= "ok 2  \n";
		}

		$sql = "UPDATE cms3_object_fields_restrictions SET field_type_id =  '10' WHERE id = '6'";
		$result = l_mysql_query($sql);
		if($error = l_mysql_error()) {
			$out .= "error 3  \n";
		} else {
			$out .= "ok 3  \n";
		}
		echo $out;
	}

	public function actionLists () {
		$restrictionList = baseRestriction::getList();
		$out = '';
		foreach ($restrictionList as $restriction) {
			if ($restriction instanceof baseRestriction) {
				$out .= " ".$restriction->getId();
				$out .= " ".$restriction->getTitle();
				$out .= " ".$restriction->getClassName();
				$out .= " ".$restriction->getFieldTypeId();
				//$out .= " ".$restriction->getErrorMessage();
				$out .= "\n";
			}
		}
		echo $out;
	}

	public function actionAdd ($param0, $title, $prefix) {
		$field_type_id = (int) $param0;
		$oFieldType = umiFieldTypesCollection::getInstance()->getFieldType($field_type_id);
		$out = '';
		if ($oFieldType instanceof umiFieldType) {
			$dataType = $oFieldType->getDataType(); //строковый идентификатор типа поля
			$multiple =  $oFieldType->getIsMultiple(); //множественность типа поля

			$fieldsCollection = umiFieldTypesCollection::getInstance(); //коллекция типов полей
			$fieldType = $fieldsCollection->getFieldTypeByDataType($dataType, $multiple); //тип поля
			$fieldTypeId = $fieldType->getId(); //числовой идентификатор типа поля

			//добавить рестрикшен с именем $title префиксом $prefix для поля типа "число"
			if ($restrictionId = baseRestriction::add($prefix, $title, $fieldTypeId)) {
				$out .= "Создан рестрикшен id: {$restrictionId} с именем {$title} префиксом {$prefix} \n";
			} else {
				$out .= "Ошибка: создания рестрикшена с именем {$title} префиксом {$prefix} \n";
			}
		} else {
			$out .= "Ошибка: Нет поля с id {$field_type_id} показать список полей umi field list \n";
		}
		echo $out;
	}

	public function actionSet ($param0, $param1) {
		$field = umiFieldsCollection::getInstance()->getField($param0);
		if ($field instanceof umiField) {
			$field->setRestrictionId($param1);
			if ($field->commit() !== false) {
				$out .= "Установлен рестрикшен id: {$param1} для поля с id  {$param0}\n";
			} else {
				$out .= "Ошибка: рестрикшен id {$param1} не установлен \n";
			}
		} else {
			$out .= "Ошибка: Нет поля с id {$param0} \n";
		}
		echo $out;
	}

	public function actionList () {
		$fieldTypesList= umiFieldTypesCollection::getInstance()->getFieldTypesList();

		$out = '';
		foreach ($fieldTypesList as $fieldType) {
			if ($fieldType instanceof umiFieldType) {
				$out .= " ".$fieldType->getId();
				$out .= " ".$fieldType->getName();
				$out .= " ".$fieldType->getDataType();
				$out .= "\n";
			}
		}
		echo $out;
	}
}
