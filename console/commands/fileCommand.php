<?php
  /**
   * Утилиты для файловой системы
   * @package umi.tools.console.command
   */
  class fileCommand extends consoleCommand {
    public $default_action = 'monitor';

    /**
     * Мониторинг удаленных, измененных и добавленых файлов в системе
     * Флаги:
     * -s отправить уведомление администратору при наличии изменений в файловой системе
     */
    public function actionMonitor() {
      $excludes = array('^~/developerTools', '^~/sys-temp', '^~/images', '^~/files', '^~/filemonitor.php', '\.svn$', '^~/.git');

      $logFile = CURRENT_WORKING_DIR . '/sys-temp/filemonitor.log';

      $initialDir = CURRENT_WORKING_DIR;

      $filesMonitor = new FilesMonitor($initialDir, $logFile, $excludes);
      $filesMonitor->checkFileSystem(isset($flags['s']));
    }
  }

  /**
   * Класс для мониторинга удаленных, измененных и добавленых файлов в системе
   */
  class FilesMonitor {

    protected $initialDir, $logFile, $excludes = array();

    public function __construct($initialDir, $logFile, array $excludes) {
      $this->initialDir = $initialDir;
      $this->logFile = $logFile;

      foreach ($excludes as &$exclude) {
        $exclude = str_replace('~', $this->initialDir, $exclude);
      }
      unset($exclude);

      $this->excludes = $excludes;
    }

    protected function isExcluded($path) {
      foreach ($this->excludes as $exclude) {
        if (preg_match('|' . $exclude . '|', $path)) {
          return true;
        }
      }
      return false;
    }

    protected function flushLog($message) {
      echo "{$message}\n";
    }

    /**
     * Записать в файл md5 сканируемых файлов и получить список новых файлов
     * @param string $dir начальная директория сканирования
     * @param array $filesToCompare массив существовавших файлов до сканирования
     * @return string лог добавленных файлов
     */
    public function getMD5($dir, $filesToCompare = array()) {
      $log = '';
      if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
          if ($entry != "." && $entry != "..") {
            $entryPath = $dir . "/" . $entry;
            if ($this->isExcluded($entryPath)) {
              $this->flushLog("{$entryPath} исключен");
              continue;
            }
            if (is_dir($entryPath)) {
              $log .= $this->getMD5($entryPath, $filesToCompare);
            } else {
              if (!empty($filesToCompare) && !in_array($entryPath, $filesToCompare)) {
                $log .= "{$entryPath} создан\n";
                $this->flushLog("{$entryPath} создан\n");
              }
              file_put_contents($this->logFile, "{$entryPath};" . md5_file($entryPath) . "\n", FILE_APPEND);
            }
          }
        }
        closedir($handle);
      }

      return $log;
    }

    /**
     * Сравнить md5 из файла с реальными md5 файлов и получить лог
     * @return array первый элемент массива - string - лог удаленных и измененных файлов,
     * второй элемент массива - array - массив файлов, которые существуют и в логе, и в системе
     */
    public function checkMD5() {
      $log = "";
      $files = array();
      if ($handle = fopen($this->logFile, "r")) {
        while (($line = fgets($handle)) !== false) {
          $fileInfo = explode(';', $line);
          $filePath = trim($fileInfo[0]);
          $md5 = trim($fileInfo[1]);
          if (file_exists($filePath)) {
            if ($md5 != md5_file($filePath)) {
              $log .= "{$filePath} изменен\n";
              $this->flushLog("{$filePath} изменен");
            }
            $files[] = $filePath;
          } else {
            $log .= "{$filePath} удален\n";
            $this->flushLog("{$filePath} удален");
          }
        }
        fclose($handle);
      }
      return array($log, $files);
    }

    /**
     *
     */
    /**
     * Проверить файловую систему
     * @param bool $send_notify отправить уведомление администратору
     */
    public function checkFileSystem($send_notify = TRUE) {
      $logFile = $this->logFile;
      $initialDir = $this->initialDir;

      if (file_exists($logFile)) {

        $info = FilesMonitor::checkMD5();
        $log = $info[0];
        $filesToCompare = $info[1];
        unlink($logFile);

        $log .= $this->getMD5($initialDir, $filesToCompare);

        if (strlen($log) & $send_notify) {

          $emailTo = regedit::getInstance()->getVal("//settings/admin_email");
          $email = regedit::getInstance()->getVal("//settings/email_from");
          $fio = regedit::getInstance()->getVal("//settings/fio_from");

          $registrationMail = new umiMail();
          $registrationMail->addRecipient($emailTo);
          $registrationMail->setFrom($email, $fio);
          $registrationMail->setSubject('Изменения в файловой системе');
          $registrationMail->setContent($log);
          $registrationMail->commit();
          $registrationMail->send();

          $this->flushLog("Письмо с изменениями было отправлено на e-mail {$emailTo}");
        } elseif (strlen($log)) {
          $this->flushLog("Изменения в файловой системе");
        } else {
          $this->flushLog("Изменений не было");
        }
      } else {
        $this->getMD5($initialDir);
        $this->flushLog("По директоиии {$initialDir} был составлен лог");
      }
    }
  }
