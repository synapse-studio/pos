<?php

namespace App\Step;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use App\Utility\Crypt;

/**
 * StepBase.
 */
class StepBase {

  /**
   * Construct.
   */
  public function __construct(Command $command) {
    $this->command = $command;
  }

  /**
   * Commands.
   */
  public function cmd(array $commands, array $ssh = []) : array {
    $exec = "";
    if ($ssh) {
      $exec = "ssh {$ssh['user']}@{$ssh['ip']} -p{$ssh['port']} -i {$ssh['key']}";
    }
    $run = [];
    foreach ($commands as $comand) {
      $run[] = $this->runProcess("$exec $comand");
    }
    return $run;
  }

  /**
   * Run Command.
   *
   * @param string $cmd
   *   Command for exec.
   * @param int $timeout
   *   Timeout.
   */
  public function runProcess(string $cmd, int $timeout = 60000) {
    $process = Process::fromShellCommandline($cmd, NULL, $_ENV);
    $process->setTimeout($timeout);
    $process->start();
    $process->wait();

    return [
      'success' => $process->isSuccessful(),
      'output' => $process->getOutput(),
      'error' => $process->getErrorOutput(),
      'code' => $process->getExitCode(),
    ];
  }

  /**
   * Remove directory.
   */
  public function rm(string $path) {
    $this->mkdir("~/old");
    $path_array = explode("/", $path);
    $name = end($path_array);
    $cmd = "mv $path ~/old/rm--$name--" . date('Y-m-dTH-i-s');
    $this->runProcess($cmd);
  }

  /**
   * Create directory.
   */
  public function mkdir(string $path) {
    $this->runProcess("mkdir -p $path");
  }

  /**
   * Remove directory.
   */
  public function getRandom(int $length) : string {
    return Crypt::rnd($length);
  }

}
