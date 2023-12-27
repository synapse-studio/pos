<?php

namespace App\Step;

/**
 * Restore site.
 */
class RestoreStep extends StepBase {

  /**
   * Run.
   */
  public function run(string $backup_url) : bool {
    $this->command->io->writeln("Restoring");

    if (!(new DownloadBackupStep($this->command))->run($backup_url)) {
      return FALSE;
    }
    elseif (!(new PlaceBackupedSiteStep($this->command))->run()) {
      return FALSE;
    }

    return TRUE;
  }

}
