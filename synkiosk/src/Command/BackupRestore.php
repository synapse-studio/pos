<?php

namespace App\Command;

use App\Utility\ExecCmd;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use App\Step\ResetSiteStep;
use App\Step\RestoreStep;

/**
 * Echo.
 */
class BackupRestore extends Command {

  const RESET_SITE_ERROR = 201;
  const RESTORE_ERROR = 202;

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('kiosk:restore')
      ->setDescription('Reset kiosk site and load new from provided url')
      ->addArgument('backup', InputArgument::OPTIONAL, 'Input backup URL');
  }

  /**
   * Exec.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->io = new SymfonyStyle($input, $output);

    $backup_url = $input->getArgument('backup');

    if (!(new ResetSiteStep($this))->run()) {
      return self::RESET_SITE_ERROR;
    }
    elseif (!(new RestoreStep($this))->run($backup_url)) {
      return self::RESTORE_ERROR;
    }
    $this->io->success('BackupRestore -> done');

    return 0;
  }

}
