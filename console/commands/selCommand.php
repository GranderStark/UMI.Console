<?php
  /**
   * Выборки
   * @package umi.tools.console.command
   */
  class selCommand extends consoleCommand {

    public function actionMain($mode = "objects", array $type_id = array(), array $type_guid = array(), array $hierarchy_id = array(), array $hierarchy_guid = array(), $domain = 1, $lang = 1, $flags = NULL) {

      $sel = new selector($mode);

      if ($mode == 'pages') {
        $sel->where('domain')->equals($domain);
        $sel->where('lang')->equals($lang);
      }

      foreach ($type_id as $id) {
        $sel->types('object-type')->id($id);
      }
      foreach ($type_guid as $guid) {
        $sel->types('object-type')->guid($guid);
      }
      foreach ($hierarchy_id as $id) {
        $sel->types('hierarchy-type')->id($id);
      }
      foreach ($hierarchy_guid as $guid) {
        $sel->types('hierarchy-type')->guid($guid);
      }

      $out = null;
      foreach ($sel as $item) {
        $out .= " " . $item->id;
        if (isset($flags['n'])) {
          $out .= " " . $item->name;
        }
        if (isset($flags['h']) && $mode == 'pages') {
          $out .= " " . $item->h1;
        }
        if (isset($flags['o']) && $mode == 'pages') {
          $out .= " " . $item->getObjectId();
        }
        $out .= "\n";
      }

      if (isset($flags['c'])) {
        $out .= "Total " . $sel->length . "\n";
      }
      echo $out;
      return true;
    }
  }
