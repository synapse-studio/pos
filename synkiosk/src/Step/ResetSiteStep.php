<?php

namespace App\Step;

/**
 * Reset site.
 */
class ResetSiteStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->io->writeln("Resetting `000-default` site");

    if (!(new RecreateSiteStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new RecreateNginxVhostStep($this->command))->run()) {
      return FALSE;
    }

    return TRUE;
  }

}
