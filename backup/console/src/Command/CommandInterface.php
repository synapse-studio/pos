<?php

namespace App\Command;

/**
 * Interface Command Interface.
 */
interface CommandInterface {

  /**
   * Run Command.
   */
  public function runProcess(string $cmd, int $timeout = 60000);

  /**
   * Send message.
   */
  public function sendMessage(string $message, string $type = 'OK');

  /**
   * PhpMQTT.
   */
  public function sendMqttMessage(string $message, string $step = NULL);

  /**
   * Log Execute.
   */
  public function logExecute(
    bool $success,
    string $success_message,
    string $error_message
  );

}
