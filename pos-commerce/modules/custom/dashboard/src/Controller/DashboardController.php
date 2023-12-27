<?php

namespace Drupal\dashboard\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller routines for dashboard.
 */
class DashboardController extends ControllerBase {

  /**
   * Construct new Page instance.
   */
  public function __construct() {
    $this->start = 'this friday -1 year';
    $this->df = \Drupal::service('date.formatter');
    $this->title = '';
  }

  /**
   * Page Title.
   */
  public function title() {
    return $this->t('Dashboard');
  }

  /**
   * Page Title.
   */
  public function page() {
    $link = 'https://r.synapse-studio.ru/issues/101502';
    $class = 'btn btn-label-success btn-bold btn-sm btn-icon-h kt-margin-l-10';
    $info = ' - начни запрос нового отчёта с этой ссылки';
    return [
      '#cache' => ['max-age' => 0],
      "dinamic" => $this->pageBlockDinamic(),
      "static" => $this->pageBlockStatic(),
    ];
  }

  /**
   * Dinamic reports.
   */
  public function pageBlockDinamic() {
    $result = [
      'title' => ["#markup" => "<h2>Динамик отчеты</h2>"],
    ];
    $no = 0;
    $rows = [];
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $definitions = $manager->getDefinitions();
    ksort($definitions);
    $groups = [];
    $recent = \Drupal::service('dashboard.time')->recent();
    foreach ($definitions as $key => $plugin) {
      $no++;
      $descr = empty($plugin['description']) ? "" : "<small><br>{$plugin['description']}</small>";
      $groups[$plugin['group']][] = [
        'no' => $no,
      // 'group' => $plugin['group'],
        "week" => [
          'data' => [
            $this->getLink("Week", "/dashboard/week/{$key}"),
            $this->getLink($recent['week']['current']['name'], "/dashboard/dinamic/{$key}/{$recent['week']['current']['start']}/{$recent['week']['current']['finish']}"),
            $this->getLink($recent['week']['-1']['name'], "/dashboard/dinamic/{$key}/{$recent['week']['-1']['start']}/{$recent['week']['-1']['finish']}"),
            $this->getLink($recent['week']['-2']['name'], "/dashboard/dinamic/{$key}/{$recent['week']['-2']['start']}/{$recent['week']['-2']['finish']}"),
          ],
        ],
        "month" => [
          'data' => [
            $this->getLink("Month", "/dashboard/month/{$key}"),
            $this->getLink($recent['month']['current']['name'], "/dashboard/dinamic/{$key}/{$recent['month']['current']['start']}/{$recent['month']['current']['finish']}"),
            $this->getLink($recent['month']['-1']['name'], "/dashboard/dinamic/{$key}/{$recent['month']['-1']['start']}/{$recent['month']['-1']['finish']}"),
            $this->getLink($recent['month']['-2']['name'], "/dashboard/dinamic/{$key}/{$recent['month']['-2']['start']}/{$recent['month']['-2']['finish']}"),

          ],
        ],
        "Quart" => [
          'data' => [
            $this->getLink("Quart", "/dashboard/quart/{$key}"),
            $this->getLink($recent['quart']['current']['name'], "/dashboard/dinamic/{$key}/{$recent['quart']['current']['start']}/{$recent['quart']['current']['finish']}"),
            $this->getLink($recent['quart']['-1']['name'], "/dashboard/dinamic/{$key}/{$recent['quart']['-1']['start']}/{$recent['quart']['-1']['finish']}"),
            $this->getLink($recent['quart']['-2']['name'], "/dashboard/dinamic/{$key}/{$recent['quart']['-2']['start']}/{$recent['quart']['-2']['finish']}"),

            $this->getLink($recent['year']['current']['name'], "/dashboard/dinamic/{$key}/{$recent['year']['current']['start']}/{$recent['year']['current']['finish']}"),
            $this->getLink($recent['year']['-1']['name'], "/dashboard/dinamic/{$key}/{$recent['year']['-1']['start']}/{$recent['year']['-1']['finish']}"),
            $this->getLink($recent['year']['-2']['name'], "/dashboard/dinamic/{$key}/{$recent['year']['-2']['start']}/{$recent['year']['-2']['finish']}"),
          ],
        ],
        'title' => [
          'data' => ['#markup' => "{$plugin['title']}$descr"],
        ],
        'name' => "{$plugin['provider']}\\{$plugin['id']}",
        'consumers' => empty($plugin['consumers']) ? "" : implode(', ', $plugin['consumers']),
      ];
    }
    foreach ($groups as $key => $items) {
      $items = $this->clearRows($items);
      $result[$key . '_title'] = ["#markup" => "<h3>$key</h3>"];
      $result[$key . '_table'] = [
        '#theme' => 'table',
        '#header' => [
          '#',
        // 'Group',
          'Week',
          'Month',
          'Quart',
          'Title',
          'Name',
          'Consumers',
        ],
        '#rows' => $items,
      ];
    }
    return $result;
  }

  /**
   * Clear rows number.
   */
  private function clearRows($rows) {
    $i = 0;
    foreach ($rows as $row) {
      $i++;
      $row['no'] = $i;
      $rows_new[] = $row;
    }
    return $rows_new;
  }

  /**
   * Static Reports.
   */
  public function pageBlockStatic() {
    $no = 0;
    $rows = [];
    $manager = \Drupal::service('plugin.manager.dashboard_static');
    $definitions = $manager->getDefinitions();
    ksort($definitions);
    usort($definitions, function ($item1, $item2) {
      return $item1['group'] <=> $item2['group'];
    });
    foreach ($definitions as $plugin) {
      $no++;
      $descr = empty($plugin['description']) ? "" : "<small><br>{$plugin['description']}</small>";
      $rows[] = [
        'no' => $no,
        'group' => $plugin['group'],
        "report" => [
          'data' => $this->getLink("[Report] ", "/dashboard/static/{$plugin['id']}"),
        ],
        'title' => [
          'data' => ['#markup' => "{$plugin['title']}$descr"],
        ],
        'name' => "{$plugin['provider']}\\{$plugin['id']}",
        'consumers' => empty($plugin['consumers']) ? "" : implode(', ', $plugin['consumers']),
      ];
    }
    return [
      'title' => ["#markup" => "<h2>Статик отчеты</h2>"],
      'table' => [
        '#theme' => 'table',
        '#header' => [
          '#',
          'Group',
          'Report',
          'Title',
          'Name',
          'Consumers',
        ],
        '#rows' => $rows,
      ],
    ];
  }

  /**
   * Link string.
   */
  private function getLink($name, $path) {
    return [
      '#prefix' => " ",
      '#markup' => Link::fromTextAndUrl(
      $name, Url::fromUri("internal:{$path}")
      )->toString(),
    ];
  }

  /**
   * Links period.
   */
  private function getLinksPeriod($start, $finish) {
    $renderable = [];
    $plugin_name = $this->currentPluginName;
    $path = "/dashboard/dinamic/{$plugin_name}/$start/$finish";
    $link = Link::fromTextAndUrl(
      $plugin_name, Url::fromUri("internal:$path")
    )->toRenderable();
    $link['#options']['attributes']['title'] = $this->title;
    $link['#options']['attributes']['data-toggle'] = "tooltip";
    $link['#options']['attributes']['data-placement'] = "top";
    $link['#options']['attributes']['class'] = [
      "kt-badge",
      "kt-badge--inline",
      "kt-badge--success",
    ];
    $renderable[] = $link;
    return $renderable;
  }

  /**
   * DashBoard.
   */
  public function dinamicReportTitle(string $plugin_name, int $start, int $finish) {
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $plugin = $manager->createInstance($plugin_name, [
      $start,
      $finish,
    ]);
    $this->title = $plugin->getPluginDefinition()['title'];
    // $this->title = $plugin_name;
    $st = $this->df->format($start, 'custom', 'D d.m.y H:i');
    $fi = $this->df->format($finish, 'custom', 'D d.m.y H:i');
    $title = $this->title;
    return ['#markup' => "<h1>$title $st → $fi</h1>"];
  }

  /**
   * DashBoard.
   */
  public function dinamicReport(Request $request, string $plugin_name, int $start, int $finish) {
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $st = $this->df->format($start, 'custom', 'D d.m.y H:i');
    $fi = $this->df->format($finish, 'custom', 'D d.m.y H:i');
    $options = (object) [
      'start' => $start,
      'finish' => $finish,
      'plugin_name' => $plugin_name,
    ];
    $plugin = $manager->createInstance($plugin_name, [
      $start,
      $finish,
    ]);
    if (!\Drupal::service('dashboard.access')->check()) {
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к сервису «‎%s»</h2>", $plugin_name),
        ],
      ];
    }
    $result = $plugin->report($options);
    return [
      '#cache' => ['max-age' => 0],
    ['#markup' => "<a href='/dashboard'>← Отчеты</a>"],
      'title' => $this->dinamicReportTitle($plugin_name, $start, $finish),
      'result' => $result,
    ];
  }

  /**
   * Month reports.
   */
  public function month(string $plugin_name = NULL) : array {
    if (!$plugin_name) {
      return ['#markup' => 'miss'];
    }
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $plugin = $manager->createInstance($plugin_name);
    $this->setCurrentReport($plugin, $plugin_name);
    if (!\Drupal::service('dashboard.access')->check()) {
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к отчету «‎%s»</h2>", $plugin_name),
        ],
      ];
    }
    $title = $this->title;
    $table = ["#markup" => "<h2>access error</h2>"];
    if (\Drupal::service('dashboard.access')->check()) {
      $month = \Drupal::service('dashboard.time')->months($this->start);
      $table = $this->renderTable($month);
    }
    return [
      '#cache' => ['max-age' => 0],
      "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
      "info" => ["#markup" => "<h2>Ежемесячные отчеты → $title</h2>"],
      "table" => $table,
    ];
  }

  /**
   * Week reports.
   */
  public function week(string $plugin_name = NULL) : array {
    if (!$plugin_name) {
      return ['#markup' => 'miss'];
    }
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $plugin = $manager->createInstance($plugin_name);
    $this->setCurrentReport($plugin, $plugin_name);
    if (!\Drupal::service('dashboard.access')->check()) {
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
      ['#markup' => "<h1>{$this->title}</h1>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к отчету «‎%s»</h2>", $plugin_name),
        ],
      ];
    }
    else {
      $title = $this->title;
      $table = ["#markup" => "<h2>access error</h2>"];
      $weeks = \Drupal::service('dashboard.time')->weeks($this->start);
      $table = $this->renderTable($weeks);
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => ["#markup" => "<h2>Ежемесячные отчеты → $title</h2>"],
        "table" => $table,
      ];
    }
  }

  /**
   * Quarter reports.
   */
  public function quart(string $plugin_name = NULL) : array {
    if (!$plugin_name) {
      return ['#markup' => 'miss'];
    }
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $plugin = $manager->createInstance($plugin_name);
    $this->setCurrentReport($plugin, $plugin_name);
    if (!\Drupal::service('dashboard.access')->check()) {
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к отчету «‎%s»</h2>", $plugin_name),
        ],
      ];
    }
    $title = $this->title;
    $table = ["#markup" => "<h2>access error</h2>"];
    if (\Drupal::service('dashboard.access')->check()) {
      $month = \Drupal::service('dashboard.time')->months($this->start);
      $table = $this->renderTable($month);
    }
    return [
      '#cache' => ['max-age' => 0],
      "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
      "info" => ["#markup" => "<h2>Ежемесячные отчеты → $title</h2>"],
      "table" => $table,
    ];
  }

  /**
   * Year reports.
   */
  public function year(string $plugin_name = NULL) : array {
    if (!$plugin_name) {
      return ['#markup' => 'miss'];
    }
    $manager = \Drupal::service('plugin.manager.dashboard_dinamic');
    $plugin = $manager->createInstance($plugin_name);
    $this->setCurrentReport($plugin, $plugin_name);
    if (!\Drupal::service('dashboard.access')->check()) {
      return [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к отчету «‎%s»</h2>", $plugin_name),
        ],
      ];
    }
    $title = $this->title;
    $table = ["#markup" => "<h2>access error</h2>"];
    if (\Drupal::service('dashboard.access')->check()) {
      // Нужно править для WebProjects!!!
      $weeks = \Drupal::service('dashboard.time')->weeks($this->start);
      switch ($plugin_name) {
        case 'WebProjects':
          $table = $this->renderTable($weeks);
          break;

        case 'AmoLeadsWebTypicalInWork':
        case 'AmoLeadsWebSupIncoming':
          $recent = \Drupal::service('dashboard.time')->recent();
          $options = (object) [
            'start' => $recent['year']['current']['start'],
            'finish' => $recent['year']['current']['finish'],
            'plugin_name' => $plugin_name,
          ];
          $result = $plugin->report($options);
          return [
            '#cache' => ['max-age' => 0],
          ['#markup' => "<a href='/dashboard'>← Отчеты</a>"],
            'result' => $result,
          ];
      }
    }
    return [
      '#cache' => ['max-age' => 0],
      "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
      "info" => ["#markup" => "<h2>Недельные отчеты → $title</h2>"],
      "table" => $table,
    ];
  }

  /**
   * Static reports.
   */
  public function static(string $plugin_name = NULL) {
    $manager = \Drupal::service('plugin.manager.dashboard_static');
    $plugin = $manager->createInstance($plugin_name);
    if (!\Drupal::service('dashboard.access')->check()) {
      $result = [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Нет доступа к сервису «‎%s»</h2>", $plugin_name),
        ],
      ];
      return $result;
    }
    if ($plugin) {
      $this->setCurrentReport($plugin, $plugin_name);
      $definition = $manager->getDefinition($plugin_name);
      $result = [
        '#cache' => ['max-age' => 0],
        "links" => ["#markup" => "<a class='dashboard-backlink' href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Статические отчеты → %s</h2>", $definition['title']),
        ],
        "table" => $plugin->report([]),
      ];
    }
    else {
      $result = [
        "links" => ["#markup" => "<a href='/dashboard'>← Отчеты</a>"],
        "info" => [
          "#markup" => sprintf("<h2>Сервис «‎%s» не найден</h2>", $plugin_name),
        ],
      ];
    }
    return $result;

  }

  /**
   * Service setter.
   */
  private function setCurrentReport(object $plugin, string $plugin_name) {
    $this->currentPlugin = $plugin;
    $this->currentPluginName = $plugin_name;
    $this->title = $plugin_name;
  }

  /**
   * Period Table.
   */
  private function renderTable($period) {
    $pre = "";
    if (\Drupal::service('dashboard.access')->check()) {
      $headers = [
      ['data' => '#'],
      ['data' => ['#markup' => 'Week'], 'class' => ['text-center']],
      ['data' => 'Q', 'class' => ['text-center']],
      ['data' => 'Month', 'class' => ['text-center']],
      ['data' => 'Start', 'class' => ['text-right']],
      ['data' => 'Finish', 'class' => ['text-right']],
      ['data' => 'Links', 'class' => ['text-left']],
      ];
      $rows = [];
      foreach ($period as $k => $week) {
        $rows[$k] = [
        ['data' => $k],
        ['data' => $week['week'] , 'class' => ['text-center']],
        ['data' => $week['quot'] , 'class' => ['text-center']],
        ['data' => $week['month'] , 'class' => ['text-center']],
        [
          'data' => $week['st'],
          'class' => ['text-right'],
        ],
        [
          'data' => $week['fi'],
          'class' => ['text-right'],
        ],
        [
          'data' => [
            $this->getLinksPeriod($week['start'], $week['finish']),
          ],
        ],
        ];
        if (isset($week['st_old'])) {
          $rows[$k][4] = [
            'data' => ['#markup' => $week['st'] . "<br>" . $week['st_old']],
            'class' => ['text-right'],
          ];
        }
        if (isset($week['fi_old'])) {
          $rows[$k][5] = [
            'data' => ['#markup' => $week['fi'] . "<br>" . $week['fi_old']],
            'class' => ['text-right'],
          ];
        }
        if (isset($week['start_old']) && isset($week['finish_old'])) {
          $rows[$k][6] = [
            'data' => [
              $this->getLinksPeriod($week['start'], $week['finish']),
              ["#markup" => "<br>"],
              $this->getLinksPeriod($week['start_old'], $week['finish_old']),
            ],
          ];
        }
      }
    }

    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows'   => $rows,
      '#attributes' => ['class' => ['table', 'table-striped', 'table-hover']],
      '#allowed_tags' => ['p', 'h2', 'small', 'br'],
    ];
  }

}
