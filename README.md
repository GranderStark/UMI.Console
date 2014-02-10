# UMI.Console

Консольная утилита UMI.CMS версия 0.1.alfa 

[демо](http://shelr.tv/records/502a05089660807bfb000066)

## Установка
    $ curl -sL umiconsole.emom.ru | bash -s stable

## Использование
    $ umi <command> [action] [flags] [options [options [...]] [<args> [<args> [...]]

### Показать информацию о текущей UMI.CMS
    $ umi info
    
### Показать базовые типы данных
    $ umi type list -nm
* **-n** показать название
* **-m** показать модуль-метод

### Показать все типы данных
    $ umi type list -AnG
* **-A** показать все типы данных
* **-n** показать название
* **-G** показать GUID
* **-g** Узнать, помечен ли тип данных как справочник
* **-l** Узнать, заблокирован ли тип данных

### Скопировать структуру типа данных
    $ umi type cp id_sourse id_target [--group=all [--field=all]

### Выборки
    $ umi sel --mode=pages --type_guid=content-page -nc
    $ umi sel --mode=objects --type_guid=content-page --type_guid=users-user -cn
    
### Unix pipe, показать тип данных **Раздел сайта**
    $ umi type -AG | grep root-pages | awk '{print $1}' | umi type view -A

## Ресурсы
* http://umiconsole.emom.ru/
* http://hub.umi-cms.ru/project/126/
* http://umiconsole.emom.ru/issues/
