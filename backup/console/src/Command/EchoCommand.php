<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * S3 Restore.
 */
class EchoCommand extends CommandBase implements CommandInterface {

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('echo')
      ->setDescription('echo');
  }

  /**
   * Exec.
   */
  protected function execute(InputInterface $input, OutputInterface $output) : int {
    $this->io = new SymfonyStyle($input, $output);
    $this->io->title("Console echo");
    return 0;
  }

}
