<?php
  /**
   * TODO Enter description here ...
   * @package umi.tools.console.command
   */
  class testCommand extends consoleCommand {

    public function actionMain() {
      if (STDIN) {
        while (($buffer = fgets(STDIN, 4096)) !== false) {
          $line[] = $buffer;
        }
        if (!feof(STDIN)) {
          echo "Error: unexpected fgets() fail\n";
        }
      }
      print_r($line);
      echo "action main\n";
    }

    public function actionCom($param0, $param1, $opt = "dd") {
      $out = "action com\n";

      return $out;
    }

    public function actionLog() {
      for ($i = 0; $i<10; $i++){
        $this->log("Line to show.");
        sleep(2);
      }
      $this->log("Done.");
      return true;
    }
  }
