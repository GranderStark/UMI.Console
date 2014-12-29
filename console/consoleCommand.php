<?php
/**
 * Карказ для создания консольных команд
 * @author Ilya Rogov <ilyar.software@gmail.com>
 * @package umi.console.command
 * @since 2.8.3
 */

  interface iConsoleCommand {
    public function run($argv);

    public function getHelp();
  }

  interface iConsoleCommandRunner {
    public function run($argv);

    public function loadCommands($path);

    public function createCommand($name);
  }

  /**
   * TODO Enter description here ...
   * @property CLIOutputBuffer $buffer
   */
  abstract class consoleCommand implements iConsoleCommand {
    public $default_action = 'main';
    protected $name, $arguments, $connection = null, $buffer;

    /**
     * Конструктор
     * @param string $name
     */
    public function __construct() {
      $this->name = '';// TODO add name
      $this->connection = ConnectionPool::getInstance()->getConnection();
      $this->buffer = outputBuffer::current('CLIOutputBuffer');
    }

    public function run($args) {
      list($action, $act_params, $options, $flags) = $this->resolveRequest($args);
      $methodName = 'action' . $action;
      if (!preg_match('/^\w+$/', $action) || !method_exists($this, $methodName)) {
        $this->usageError("Unknown action: " . $action);
      }

      $method = new ReflectionMethod($this, $methodName);
      $params = array();

      foreach ($method->getParameters() as $i => $param) {
        $name = $param->getName();

        if (preg_match('/^param(\d+)?$/', $name, $act_params_name)) {

          if (isset($act_params[$act_params_name[1]])) {
            if ($param->isArray()) {
              $params[] = is_array($act_params[$act_params_name[1]]) ? $act_params[$act_params_name[1]] : array($act_params[$act_params_name[1]]);
            } elseif (!is_array($act_params[$act_params_name[1]])) {
              $params[] = $act_params[$act_params_name[1]];
            }
          } elseif (!$param->isDefaultValueAvailable() and STDIN) {

            $items = array();
            while (($buffer = fgets(STDIN, 4096)) !== false) {
              $items[] = $buffer;
            }
            $params[] = $items;
          } elseif (!$param->isDefaultValueAvailable()) {
            $this->usageError("Missing required Param param$act_params_name[1]");
          } else {
            $params[] = $param->getDefaultValue();
          }
        } elseif (isset($options[$name])) {

          if ($param->isArray()) {
            $params[] = is_array($options[$name]) ? $options[$name] : array($options[$name]);
          } elseif (!is_array($options[$name])) {
            $params[] = $options[$name];
          } else {
            $this->usageError("Missing required Option --$name");
          }
        } elseif ($name === 'args') {
          $params[] = $args;
        } elseif ($name === 'flags') {
          $params[] = $flags;
        } elseif ($param->isDefaultValueAvailable()) {
          $params[] = $param->getDefaultValue();
        } else {
          $this->usageError("Missing required Option --$name");
        }
        unset($options[$name]);
      }

      if (!empty($options)) {
        $class = new ReflectionClass(get_class($this));
        foreach ($options as $name => $value) {
          if ($class->hasProperty($name)) {
            $property = $class->getProperty($name);
            if ($property->isPublic() && !$property->isStatic()) {
              $this->$name = $value;
              unset($options[$name]);
            }
          }
        }
      }

      if (!empty($options)) {
        $this->usageError("Unknown options: " . implode(', ', array_keys($options)));
      }

      $exitCode = 0;

      if ($this->beforeAction($action, $params)) {
        $exitCode = $method->invokeArgs($this, $params);
        $exitCode = $this->afterAction($action, $params, is_int($exitCode) ? $exitCode : 0);
      }

      return $exitCode;
    }

    /**
     * TODO Enter description here ...
     */
    protected function beforeAction($action, $params) {
      // 		if($this->hasEventHandler('onBeforeAction')) {
      // 			$event = new CConsoleCommandEvent($this, $params, $action);
      // 			$this->onBeforeAction($event);
      // 			return !$event->stopCommand;
      // 		} else{
      // 			return true;
      // 		}
      return true;
    }

    /**
     * TODO Enter description here ...
     */
    protected function afterAction($action, $params, $exitCode = 0) {
      // 		$event = new CConsoleCommandEvent($this, $params, $action, $exitCode);
      // 		if($this->hasEventHandler('onAfterAction')) $this->onAfterAction($event);
      // 		return $event->exitCode;
      return true;
    }

    /**
     * TODO Enter description here ...
     */
    public function usageError($message) {
      echo "Error: $message\n\n" . $this->getHelp() . "\n";
      exit(1);
    }

    /**
     * TODO Enter description here ...
     */
    protected function resolveRequest($args) {
      $params = array();
      $options = array();
      $flags = array();

      foreach ($args as $arg) {
        if (preg_match('/^--(\w+)=(.*)?$/', $arg, $matches)) {
          $name = $matches[1];
          $value = isset($matches[2]) ? $matches[2] : true;

          if (isset($options[$name])) {

            if (!is_array($options[$name])) {
              $options[$name] = array($options[$name]);
            }
            $options[$name][] = $value;
          } else {
            $options[$name] = $value;
          }
        } elseif (preg_match('/^--(\w+)?$/', $arg, $matches)) { //FIXME refactoring patern
          $name = $matches[1];
          $value = isset($matches[2]) ? $matches[2] : true;

          if (isset($options[$name])) {

            if (!is_array($options[$name])) {
              $options[$name] = array($options[$name]);
            }
            $options[$name][] = $value;
          } else {
            $options[$name] = $value;
          }
        } elseif (preg_match('/^-(\w+)=(.*)?$/', $arg, $matches)) {
          $name = $matches[1];
          $value = isset($matches[2]) ? $matches[2] : true;

          if (isset($options[$name])) {

            if (!is_array($options[$name])) {
              $options[$name] = array($options[$name]);
            }
            $options[$name][] = $value;
          } else {
            $options[$name] = $value;
          }
        } elseif (preg_match('/^-(\w+)?$/', $arg, $matches)) {
          $chars = str_split(substr($arg, 1));
          foreach ($chars as $char) {
            $key = $char;
            $flags[$key] = isset($flags[$key]) ? $flags[$key] : true;
          }
        } elseif (isset($action)) {
          $params[] = $arg;
        } else {
          $action = $arg;
        }
      }

      if (!isset($action)) {
        $action = $this->default_action;
      }

      return array($action, $params, $options, $flags);
    }

    /**
     * Получить инструкции по использованию команды
     */
    public function getHelp() {
      $help = "";
      $help .= "help for " . $this->name;
      // FIXME add get command help
      return $help;
    }

    public function out($mess) {
      $this->buffer->push($mess.PHP_EOL);
      $this->buffer->send();
    }

    public function log($mess, $dataFormat = 'H:i:s') {
      $this->out($mess);
      file_put_contents('./sys-temp/console.log', date($dataFormat)." ".$mess . "\n", FILE_APPEND);
    }
  }

  /**
   * Реализует запуск консольной команды
   */
  class consoleCommandRunner implements iConsoleCommandRunner {

    public $commands = array();

    private $command_name;

    public function run($argv) {
      $this->command_name = $argv[0];
      array_shift($argv);
      if (isset($argv[0])) {
        $name = $argv[0];
        array_shift($argv);
      } else {
        $name = 'help';
      }

      if (($command = $this->createCommand($name)) === null) {
        return $this->help();
      } else {
        return $command->run($argv);
      }
    }

    /**
     * Получить список доступных команд
     * @param string $path Путь поиска
     */
    public function loadCommands($path) {
      if (($dir = @opendir($path)) !== false) {
        while (($name = readdir($dir)) !== false) {
          $file = $path . DIRECTORY_SEPARATOR . $name;
          if (!strcmp(substr($name, -11), 'Command.php') && is_file($file)) {
            $this->commands[substr($name, 0, -11)] = $file;
          }
        }
        closedir($dir);
      }
    }

    /**
     * Создать команду если это возможно
     * @param string $name Имя команды
     * @return consoleCommand | null
     */
    public function createCommand($name) {
      if (isset($this->commands[$name])) {
        $className = basename($this->commands[$name], '.php');
        if (!class_exists($className, false)) {
          require_once($this->commands[$name]);
          $comman = new $className();
          if ($comman instanceof consoleCommand) {
            return $comman;
          }
        }
      }
      return null;
    }

    public function view($res) {
      $out = null;
      foreach ($res as $key => $value) {
        $out .= $value . "\n";
      }
      return $out;
    }

    public function help() {
      echo "UMI.Console (based on UMI.CMS)\n";
      echo "Usage: umi <command-name> [flags...] [options...] [parameters...]\n";
      echo "\nThe following commands are available:\n";
      $out = null;
      foreach ($this->commands as $name => $path) {
        $out .= "  " . $name . "\n";
      }
      echo $out;
      return true;
    }
  }

?>
