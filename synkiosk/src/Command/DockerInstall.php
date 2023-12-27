<?php

namespace App\Command;

use App\Utility\ExecCmd;
use App\Utility\Crypt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Echo.
 */
class DockerInstall extends Command {

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('kiosk:install');
  }

  /**
   * Exec.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->io = new SymfonyStyle($input, $output);
    $this->exec = new ExecCmd($this->io, $output);
    $this->io->title('Docker Install');
    $this->dockerCheckInstall();
    $this->exec->cmd([
      'git clone https://github.com/politsin/docker-proxy /opt/docker-proxy',
      'cd /opt/docker-proxy && ./start.sh',
      'rm /home/synapse/kiosk.sh',
      'rm /home/synapse/reboot.sh',
      'rm /home/synapse/.config/autostart/kiosk.sh.desktop',
      'rm /home/synapse/Рабочий\ стол/reboot.sh.desktop',
      'cp /opt/kiosk/assets/home/kiosk.sh /home/synapse',
      'chmod +x /home/synapse/kiosk.sh',
      'cp /opt/kiosk/assets/home/reboot.sh /home/synapse',
      'chmod +x /home/synapse/reboot.sh',
      'mkdir -p /home/synapse/.config/autostart',
      'cp /opt/kiosk/assets/home/kiosk.sh.desktop /home/synapse/.config/autostart',
      'cp /opt/kiosk/assets/home/reboot.sh.desktop /home/synapse/Рабочий\ стол/',
      'chmod +x /home/synapse/Рабочий\ стол/reboot.sh.desktop',
    ]);
    $this->exec->addSite();
    $this->io->writeln('Done');
    return 0;
  }

  /**
   * Exec.
   */
  protected function dockerCheckInstall() {
    $docker_check = shell_exec('which docker');
    if (!$docker_check) {
      $this->io->warning("Install Docker");
      $this->exec->install([
        'docker',
        'docker-compose',
      ]);
      $this->exec->cmd([
        'systemctl enable docker',
      ]);
      $this->io->writeln("Docker installed");
    }
    $docker = shell_exec("docker --version");
    $this->io->success("Docker version: $docker");
  }

}
