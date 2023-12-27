<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * StepBase.
 */
class StepBase {

  //phpcs:ignore
  protected CommandInterface $command;

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    $this->command = $command;
  }

}
