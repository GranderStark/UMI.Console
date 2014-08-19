<?php
/**
 * TODO descrp
 *
 * @author Ilya Rogov <ilyar.software@gmail.com>
 * @package umi.console.command
 */
  class migrateCommand extends consoleCommand {
    public $default_action = 'save';
    private $destinationPath = './db/';
    private $config = array(
      'registry' => array('paths' => array(), 'exclude' => array()),
      'directories' => array(), 'files' => array(), 'langs' => array(), 'domains' => array(),'templates' => array(),
      'datatypes' => array(), 'types' => array(), 'objects' => array(), 'pages' => array(),'relations' => array(),
      'options' => array(), 'restrictions' => array(), 'permissions' => array(), 'hierarchy' => array()
    );

    public function __construct() {
      parent::__construct();
      $resourcesDir = cmsController::getInstance()->getResourcesDirectory();
      $destinationPath = $resourcesDir . 'db/';
      if (!is_dir($destinationPath)) mkdir($destinationPath, 0777);
      $this->destinationPath = $destinationPath;
    }

    public function actionApply($name = 'migrate', $updateIgnore = false, $flags = null) {
      $importer = new xmlImporter();
      $importer->loadXmlFile($this->destinationPath . $name.'.xml');
      if($updateIgnore !== false || isset($flags['i'])) {
        $importer->setUpdateIgnoreMode(); // режим НЕ обновления уже существующих записей
      }

      // $importer->setFilesSource($dir); // путь до файлов

      $importer->execute();
      if(isset($flags['l']) && $logMessges = $importer->getImportLog()) {
        echo "apply log: ".PHP_EOL;
        foreach($logMessges as $messge){
          echo $messge.PHP_EOL;
        }
      }
    }

    public function actionSave($name = 'migrate', $flags = null) {
      $this->prepareConfig($name);
      $sourceName = isset($flags['t']) ? $name.time(): $name;
      $exporter = new xmlExporter($sourceName);
      $exporter->addRegistry($this->config['registry']['paths']); // массив путей к записям реестра
      $exporter->addLangs($this->config['langs']); // массив объектов lang
      $exporter->addDomains($this->config['domains']); // массив объектов domain
      $exporter->addTemplates($this->config['templates']);  // массив объектов template
      $exporter->addDataTypes($this->config['datatypes']); // массив объектов umiFieldType
      $exporter->addTypes($this->config['types']); // массив объектов umiObjectType
      $exporter->setIgnoreRelations($this->config['relations']);
      $exporter->setShowAllFields(true); // устанавливаем флаг экспорта всех полей (в т.ч. системных и скрытых)
      $dom = $exporter->execute(); // запускаем экспорт, результат записываем в переменную
      file_put_contents($this->destinationPath . $name.'.xml', $dom->saveXML());
      echo "data migrate {$name} save".PHP_EOL;
      if(isset($flags['s'])) {
        echo "statistics migrate: ".PHP_EOL;
        $this->getStat($dom);
      }
      if(isset($flags['l']) && $logMessges = $exporter->getExportLog()) {
        echo "save log: ".PHP_EOL;
        foreach($logMessges as $messge){
          echo $messge.PHP_EOL;
        }
      }
    }

    private function schemeDiff($migrateScheme = 'scheme.xml', $currenScheme = 'current_scheme.xml') {
      $status = null;
      if(!file_exists($migrateScheme) && !file_exists($currenScheme)) {
        echo "error scheme diff".PHP_EOL;
        $status = 'error';
      } else {
        $this->dbSchemeConverter('save', $currenScheme);
        if(md5_file($migrateScheme) != md5_file($currenScheme)) {
          echo "db schema changes have".PHP_EOL;
          $status = 'migrate';
        } else {
          echo "db schema does not change".PHP_EOL;
          $status = 'stable';
        }
      }
      return $status;
    }

    /**
     * @param string $mode save|restore
     * @param string $fileName
     */
    private function dbSchemeConverter($mode = 'save', $fileName = 'scheme.xml') {
      $dbSchemePath = $this->destinationPath . $fileName;
      $converter = new dbSchemeConverter($this->connection);
      $converter->setMode($mode);
      $converter->setDestinationFile($dbSchemePath);
      $converter->run();
      echo "db scheme {$fileName} {$mode}".PHP_EOL;
    }

    private function getStat(DOMDocument $dom) {
      $umidump = $dom->getElementsByTagName('umidump')->item(0);
      foreach ($umidump->childNodes as $node) {
        echo $node->nodeName . ": " . $node->childNodes->length . PHP_EOL;
      }
    }

    private function getLangs($langs = 'all') {
      $array_langs = array();
      if ($langs == 'all') {
        $array_langs = langsCollection::getInstance()->getList();
      } else {
        $langs = explode(',', $langs);
        foreach ($langs as $lang) {
          $host = trim($lang);
          $lang_id = langsCollection::getInstance()->getLangId($lang);
          $array_langs[] = langsCollection::getInstance()->getLang($lang_id);
        }
      }
      return $array_langs;
    }

    /**
     * Функция для рекурсивного выбора ключей реестра
     * @param bool|string $parentPath
     * @param bool|string $excludePaths
     * @return array
     */
    private function getRegistryPaths($parentPath, $excludePaths = false) {
      $paths = array();
      $childrenPaths = regedit::getInstance()->getList($parentPath);
      if (is_array($childrenPaths)) {
        foreach ($childrenPaths as $childPath) {
          if ($parentPath != '//') {
            $path = $parentPath . '/' . $childPath[0];
          } else {
            $path = $childPath[0];
          }
          if (!in_array($path, $excludePaths)) {
            $paths[] = $path;
            $paths = array_merge($paths, $this->getRegistryPaths($path, $excludePaths));
          }
        }
      }
      return $paths;
    }

    private function prepareRegistry() {
      $paths = array();
      foreach($this->config['registry']['paths'] as $path) {
        $paths = array_merge($paths, $this->getRegistryPaths($path, $this->config['registry']['exclude']));
      }
      $this->config['registry']['paths'] = $paths;
    }

    private function prepareDomains() {
      $domains = $this->config['domains'];
      if (isset($domains[0]) && $domains[0] == 'all') {
        $this->config['domains'] = domainsCollection::getInstance()->getList();
      } else {
        $this->config['domains'] = array();
        foreach ($domains as $domain) {
          $domain_id = domainsCollection::getInstance()->getDomainId(trim($domain), true);
          $this->config['domains'][] = domainsCollection::getInstance()->getDomain($domain_id);
        }
      }
    }

    private function prepareTemplates() {
      $templates = templatesCollection::getInstance();
      foreach ($this->config['templates'] as &$template) {
        $template = $templates->getTemplate($template);
      }
    }

    private function prepareDataTypes() {
      $fieldTypes = umiFieldTypesCollection::getInstance();
      foreach ($this->config['datatypes'] as &$fieldType) {
        $fieldType = $fieldTypes->getFieldType($fieldType);
      }
    }

    private function prepareTypes() {
      $objectTypes = umiObjectTypesCollection::getInstance();
      $types = $this->config['types'];
      $this->config['types'] = array();
      if (isset($types[0]) && $types[0] == 'all') {
        $types = $objectTypes->getAllTypes();
        foreach ($types as $typeId => $type) {
          $this->config['types'][] = $objectTypes->getType($typeId);
        }
      } elseif(is_array($types)) {
        foreach($types as $typeId) {
          $this->config['types'][] = $objectTypes->getType($typeId);
        }
      }
    }

    /**
     * @param $name
     */
    private function prepareConfig($name) {
      $this->getConfig($name);
      $this->prepareRegistry();
      $this->prepareDomains();
      $this->prepareTemplates();
      $this->prepareTypes();
      $this->prepareDataTypes();
    }

    /**
     * @param $name
     */
    private function getConfig($name) {
      $this->config = array_merge($this->config, json_decode(file_get_contents($this->destinationPath . $name . '.json'), true));
    }
  }
