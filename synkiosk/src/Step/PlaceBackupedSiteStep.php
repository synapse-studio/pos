<?php

namespace App\Step;

/**
 * Place Backuped Site.
 */
class PlaceBackupedSiteStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->io->writeln("Placing Backuped Site");

    $temp = "/tmp/drupal.tar.gz";
    $dir = "/opt/sites/000-default";
    $home = "$dir/www-home";
    $this->rm("$home/html");
    $dbpass = trim(
      file_get_contents("$home/.pass")
    );
    $this->cmd([
      sprintf('tar xzf %s -C %s', $temp, $dir),
      "mv $dir/var/www/html $home/html",
      "cd $dir && docker-compose restart",
      "echo '\$databases[\"default\"][\"default\"][\"password\"] = \"$dbpass\";' >> $home/html/sites/default/settings.php",
      "cat $home/html/.db.sql | docker exec -i php /usr/bin/mysql -u drupal --password=$dbpass drupal",
      'docker exec -i php /usr/local/bin/drush cr --root=/var/www/html',
    ]);

    return TRUE;
  }

}
