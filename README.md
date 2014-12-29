# UMI.Console
[![Build Status](https://scrutinizer-ci.com/g/ilyar/UMI.Console/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ilyar/UMI.Console/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ilyar/UMI.Console/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ilyar/UMI.Console/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ilyar/UMI.Console/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ilyar/UMI.Console/?branch=master)
[![Code Climate](https://codeclimate.com/github/ilyar/UMI.Console/badges/gpa.svg)](https://codeclimate.com/github/ilyar/UMI.Console)

Консольная утилита UMI.CMS

## Установка

    curl -O  http://ilyar.github.io/UMI.Console/umi-cli.phar
    chmod +x umi-cli.phar
    mv umi-cli.phar /usr/local/bin/umi

## Использование

Все команды необходимо выполнять в директории системы

    $ umi <command> [action] [flags] [options [options [...]] [<args> [<args> [...]]

Отобразить список доступных команд

    $ umi

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

    $ umi type cp id_source id_target [--group=all [--field=all]

### перемещение или переименование групп, полей Типов данных

    $ umi type mv --group=name|id type_id new_name|new_title -n|-t

### Показать информацию

    $ umi type view {typeId}

### Редактировать тип данных

    $ umi type edit {typeId} [--guid=null [--locked=false [--delete=false]
* **--guid** или **-g** установить GUID
* **--locked** или **-l** заблокировать
* **--delete** или **-g** удвлить

### Выборки

    $ umi sel --mode=pages --type_guid=content-page -nc
    $ umi sel --mode=objects --type_guid=content-page --type_guid=users-user -cn

### Манипуляции над страницами (элементы) ситемы

Установить источник данных для элемента

    $ umi element chobj {elementId} {objectId}

Установить права `level` на страницу `rootId` и ее подстраницы для пользователя или группы

    $ umi element permission {rootId} {ownerId} {level}

Удалить все права на страницу `rootId` и ее подстраницы для всех пользователей

    $ umi element permission {rootId} --reset

Удалить все права на страницу `rootId` и ее подстраницы для пользователя или группы

    $ umi element permission {rootId} {ownerId} --reset

Удалить все права на страницу `rootId` и ее подстраницы для всех пользователей и установить права level для пользователя или группы

    $ umi element permission {rootId} {ownerId} {level} --reset

Дополнительные параметры выборки и значения поумолчанию

* --domainId = 1 указать индентификатор домена (актуально если ишем от корня: rootId = 0)
* --langId = 1 указать индентификатор языка (актуально если ишем от корня: rootId = 0)
* --unActive = true если true, то в результат будут включены неактивные страницы
* --unVisible = true если true, то в результат будут включены невидимые в меню страницы
* --depth = 0  глубина поиска
* --typeId = false включить в результат только страницы с указанным идентификатора базового типа (umiHierarchyType)
* --addSelf = true включить в результат rootId

### Управление рестрикшенами [baseRestriction](http://api.docs.umi-cms.ru/spravochnik_po_klassam_yadra_umicms/model_dannyh/baserestriction/)

Список рестрикшенов

    $ umi restriction list

Добавить новый рестрикшен

    $ umi restriction add {fieldTypeId} {title} {prefix}

Назначить полую рестрикшен

    $ umi restriction set {fieldId} {restrictionId}

### Проверка контролной суммы файлов системы

    $ umi file

### Выполнение бекапа

    $ umi backup

### Управление системными перенаправлениями (редиректами)

Получить список перенаправлений

    $ umi redirect list

Добавить новое перенаправление

    $ umi redirect add {oldURL} {newURL} [status=301]

Удалить перенаправление

    $ umi redirect del {redirectId}

Получить список перенаправлений со страницы `sourceURL`

    $ umi redirect get {sourceURL}

### Миграция типов данных

Формирование и применение [UMIDump 2.0](http://api.docs.umi-cms.ru/razrabotka_nestandartnogo_funkcionala/format_umidump_20/opisanie_formata/) на основе JSON-конфига.
По умолчанию файл настройки миграции называется `migrate.json` и должен быть расположен директории шаблона `~/templates/{имя_шаблона}/db/migrate.json`, пример:

```json
{
  "registry": {
    "paths": ["//"],
    "exclude": ["settings/keycode", "umiMessages/lastConnectTime"]
  },
  "domains": ["all"],
  "templates": [1, 2],
  "types": [
    "users-user",
    "banners-banner"
  ],
  "relations": ["domains", "langs", "templates", "fields_relations", "restrictions", "permissions"]
}
```

Формирование `UMIDump` (экспорт)

    $ umi migrate [save [--name=migrate] # будет создан файл `~/templates/{имя_шаблона}/db/migrate.xml`

Применение `UMIDump` (импорт)

    $ umi migrate apply [--name=migrate] # будет применен файл `~/templates/{имя_шаблона}/db/migrate.xml`

### Управление правами пользователей и групп

    $ umi perm


### Unix pipe, показать тип данных **Раздел сайта**

    $ umi type -AG | grep root-pages | awk '{print $1}' | umi type view -A

## Планы

- Покрытие тестами
- Рефакторинг PHP >= 5.4
- PSR-2, PSR-4
- PSR-3

## Лицензия

Файлы в этом репозитории находятся под действием лицензии BSD.
Копия данной лицензии доступна в [LICENSE](LICENSE.md).
