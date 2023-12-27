<?php

namespace App\Utility;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exec CMD.
 */
class ExecCmd {

  /**
   * Construct.
   */
  public function __construct(SymfonyStyle $io, OutputInterface $output) {
    $this->io = $io;
    $this->output = $output;
    return $this;
  }

  /**
   * Install.
   */
  public function install(array $apps) {
    $cmd = "apt-get update && apt-get install -y " . implode(" ", $apps);
    $this->run($cmd, FALSE, FALSE);
  }

  /**
   * Install.
   */
  public function addSite(string $name = "000-default") {
    $dir = "/opt/sites/$name";
    $nginx = "/opt/docker-proxy/vhosts/$name";
    $this->io->writeln($name);
    $this->rm($dir, TRUE);
    $this->rm($nginx, TRUE);
    if (!is_dir($dir)) {
      $this->io->warning("create NEW site `$name`");
      $this->mkdir($dir);
      $rootpass = $key = Crypt::rnd(12);
      $dbpass = $key = Crypt::rnd(8);
      $this->cmd([
        "cp -r {$_ENV['ASSETS']}/fs/* $dir/",
        "echo $dbpass > $dir/www-home/.pass",
        "echo $dbpass > $dir/www-home/html/.pass",
        "echo '<?php print 2+3;' > $dir/www-home/html/index.php",
        "sed -i 's/dbPass/$dbpass/g' $dir/docker-compose.yml",
        "sed -i 's/rootPass/$rootpass/g' $dir/docker-compose.yml",
        "cd $dir && docker-compose up -d",
      ]);
    }
    if (!is_dir($nginx)) {
      $this->mkdir($nginx);
      $this->cmd([
        "cp -r {$_ENV['ASSETS']}/nginx/example.conf $nginx/http.conf",
        "docker restart docker-proxy",
      ]);
    }
  }

  /**
   * Run Command.
   *
   * @param bool|string $cmd
   *   Command for exec.
   * @param bool $run
   *   Run with new Process.
   * @param int $timeout
   *   Timeout.
   */
  public function run($cmd, $run = TRUE, $timeout = 60000) {
    if ($run) {
      // https://symfony.com/doc/current/components/process.html
      $process = Process::fromShellCommandline($cmd);
      $process->setTimeout($timeout);
      $process->start();
      $process->wait();

      return [
        'success' => $process->isSuccessful(),
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
      ];
    }
    return shell_exec($cmd);
  }

  /**
   * Remove directory.
   */
  public function rm($path, $dir = FALSE) {
    $this->mkdir("~/old");
    $path_array = explode("/", $path);
    $name = end($path_array);
    $cmd = "mv $path ~/old/rm--$name--" . date('Y-m-dTH-i-s');
    $this->run($cmd);
  }

  /**
   * Create directory.
   */
  public function mkdir($path) {
    $this->run("mkdir -p $path");
  }

  /**
   * Commands.
   */
  public function cmd(array $commands, $ssh = FALSE) {
    $exec = "";
    if ($ssh) {
      $exec = "ssh {$ssh['user']}@{$ssh['ip']} -p{$ssh['port']} -i {$ssh['key']}";
    }
    $run = [];
    foreach ($commands as $comand) {
      $run[] = $this->run("$exec $comand");
    }
    return $run;
  }

  /**
   * Composer.
   */
  public function composer(array $require) {
    $rep = implode(" ", $require);
    $cmd = "composer require $rep -d {$_ENV['HTML']}";
    $run = $this->run($cmd);
    return $run;
  }

}
