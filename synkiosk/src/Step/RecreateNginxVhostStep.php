<?php

namespace App\Step;

/**
 * Recreate Nginx Vhost.
 */
class RecreateNginxVhostStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->io->writeln("Recreating Nginx Vhost");

    $nginx = "/opt/docker-proxy/vhosts/000-default";
    $this->rm($nginx);
    if (is_dir($nginx)) {
      $this->command->io->warning("Recreating Nginx Vhost error");

      return FALSE;
    }
    $this->mkdir($nginx);
    $this->cmd([
      "cp -r {$_ENV['ASSETS']}/nginx/example.conf $nginx/http.conf",
      "docker restart docker-proxy",
    ]);

    return TRUE;
  }

}
