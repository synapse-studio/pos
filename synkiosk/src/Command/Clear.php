<?php

namespace App\Command;

use App\Utility\ExecCmd;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Echo.
 */
class Clear extends Command {

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('kiosk:clear');
  }

  /**
   * Exec.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->io = new SymfonyStyle($input, $output);
    $this->exec = new ExecCmd($this->io, $output);
    $this->io->title('Clear System');
    $this->exec->cmd([
      'docker container stop $(docker container ls -aq)',
      'docker system prune',
      'apt-get purge docker docker.io docker-compose -y',
      'apt autoremove -y',
    ]);
    $this->exec->rm('/opt/sites', TRUE);
    $this->exec->rm('/opt/docker-proxy', TRUE);

    $this->io->writeln('Done');
    return 0;
  }

}
