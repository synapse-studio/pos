<?php

namespace App\Step;

/**
 * Remove site.
 */
class RecreateSiteStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->io->writeln("Recreating `000-default` site");

    $dir = "/opt/sites/000-default";
    $this->rm($dir);
    if (is_dir($dir)) {
      $this->command->io->warning("Recreating `000-default` site error");

      return FALSE;
    }
    $this->mkdir($dir);
    $rootpass = $this->getRandom(12);
    $dbpass = $this->getRandom(8);
    $this->cmd([
      "cp -r {$_ENV['ASSETS']}/fs/* $dir/",
      "echo $dbpass > $dir/www-home/.pass",
      "echo $dbpass > $dir/www-home/html/.pass",
      "echo '<?php print 2+3;' > $dir/www-home/html/index.php",
      "sed -i 's/dbPass/$dbpass/g' $dir/docker-compose.yml",
      "sed -i 's/rootPass/$rootpass/g' $dir/docker-compose.yml",
      "cd $dir && docker-compose up -d",
    ]);

    return TRUE;
  }

}
