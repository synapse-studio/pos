<?php

namespace App\Step;

/**
 * Download Backup.
 */
class DownloadBackupStep extends StepBase {

  /**
   * Run.
   */
  public function run(string $backup_url) : bool {
    $this->command->io->writeln("Downloading Backup");

    $temp = "/tmp/drupal.tar.gz";
    $this->rm($temp);
    $this->cmd([
      'mkdir /opt/temp',
      "wget $backup_url -q -O $temp",
    ]);

    return TRUE;
  }

}
