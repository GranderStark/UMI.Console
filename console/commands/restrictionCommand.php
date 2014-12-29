<?php
/**
 * Управление рестрикшенами [baseRestriction](http://api.docs.umi-cms.ru/spravochnik_po_klassam_yadra_umicms/model_dannyh/baserestriction/)
 * @package umi.tools.console.command
 */
class restrictionCommand extends consoleCommand {

	public function actionMain () {

		echo "todo help\n";
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
