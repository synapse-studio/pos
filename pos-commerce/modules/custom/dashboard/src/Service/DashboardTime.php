<?php

namespace Drupal\dashboard\Service;

/**
 * CommerceML CheckAuth service.
 */
class DashboardTime {

  /**
   * Constructs a new Export object.
   */
  public function __construct() {
    $this->df = \Drupal::service('date.formatter');
  }

  /**
   * Get.
   */
  public function recent() : array {
    $time = time();
    $day = 60 * 60 * 24;
    $week0 = strtotime('Monday this week', $time);
    $week1 = strtotime('Monday this week', $time - 7 * $day);
    $week2 = strtotime('Monday this week', $time - 14 * $day);
    $month0 = strtotime('first day of this month');
    $month1 = strtotime('first day of -1 month');
    $month2 = strtotime('first day of -2 month');
    $q0 = $this->quartMap(strtotime('first day of this month'));
    $q1 = $this->quartMap(strtotime('first day of -3 month'));
    $q2 = $this->quartMap(strtotime('first day of -6 month'));
    $year0 = strtotime('first day of this year');
    $year1 = strtotime('first day of -1 year');
    $year2 = strtotime('first day of -2 year');
    $dates = [
      'week' => [
        'current' => [
          'start' => strtotime('Monday this week', $time),
          'finish' => strtotime('Monday next week', $time) - 1,
          'name' => $this->df->format($week0, "custom", 'W'),
        ],
        '-1' => [
          'start' => strtotime('Monday this week', $time - 7 * $day),
          'finish' => strtotime('Monday next week', $time - 7 * $day) - 1,
          'name' => $this->df->format($week1, "custom", 'W'),
        ],
        '-2' => [
          'start' => strtotime('Monday this week', $time - 14 * $day),
          'finish' => strtotime('Monday next week', $time - 14 * $day) - 1,
          'name' => $this->df->format($week2, "custom", 'W'),
        ],
      ],
      'month' => [
        'current' => [
          'start' => strtotime('first day of today this month'),
          'finish' => strtotime('last day of today this month') + 60 * 60 * 24 - 1,
          'name' => $this->df->format($month0, "custom", 'M'),
        ],
        '-1' => [
          'start' => strtotime('first day of today -1 month'),
          'finish' => strtotime('first day of today this month') - 1,
          'name' => $this->df->format($month1, "custom", 'M'),
        ],
        '-2' => [
          'start' => strtotime('first day of today -2 month'),
          'finish' => strtotime('first day of today -1 month') - 1,
          'name' => $this->df->format($month2, "custom", 'M'),
        ],
      ],
      'year' => [
        'current' => [
          'start' => strtotime('first day of january this year'),
          'finish' => strtotime('last day of december this year') + 60 * 60 * 24 - 1,
          'name' => $this->df->format($year0, "custom", 'Y'),
        ],
        '-1' => [
          'start' => strtotime('first day of january this year -1 year'),
          'finish' => strtotime('last day of december this year -1 year') + 60 * 60 * 24 - 1,
          'name' => $this->df->format($year1, "custom", 'Y'),
        ],
        '-2' => [
          'start' => strtotime('first day of january this year -2 year'),
          'finish' => strtotime('last day of december this year -2 year') + 60 * 60 * 24 - 1,
          'name' => $this->df->format($year2, "custom", 'Y'),
        ],
      ],
      'quart' => [
        'current' => [
          'start' => $q0['start'],
          'finish' => $q0['finish'],
          'name' => $q0['name'],
        ],
        '-1' => [
          'start' => $q1['start'],
          'finish' => $q1['finish'],
          'name' => $q1['name'],
        ],
        '-2' => [
          'start' => $q2['start'],
          'finish' => $q2['finish'],
          'name' => $q2['name'],
        ],
      ],
    ];
    return $dates;
  }

  /**
   * Get.
   */
  public function quartMap($time) {
    $month = $this->df->format($time, "custom", 'm');
    $year = $this->df->format($time, "custom", 'Y');
    $map = [
      '01' => 'Q1',
      '02' => 'Q1',
      '03' => 'Q1',
      '04' => 'Q2',
      '05' => 'Q2',
      '06' => 'Q2',
      '07' => 'Q3',
      '08' => 'Q3',
      '09' => 'Q3',
      '10' => 'Q4',
      '11' => 'Q4',
      '12' => 'Q4',
    ];
    $q = $map[$month];
    $quarts = [
      'Q1' => ['start' => '01', 'finish' => '03'],
      'Q2' => ['start' => '04', 'finish' => '06'],
      'Q3' => ['start' => '07', 'finish' => '09'],
      'Q4' => ['start' => '10', 'finish' => '12'],
    ];
    $start = $quarts[$q]['start'];
    $quart_start = strtotime("01.$start.$year");
    $quart = [
      'q' => "$q/$year",
      'name' => $map[$month],
      'start' => strtotime('first day of this month', $quart_start),
      'finish' => strtotime('last day of +2 month', $quart_start),
    ];
    return $quart;
  }

  /**
   * Get.
   */
  public function weeks($start) {
    $time = strtotime($start);
    $rweeks = [];
    while ($time < time() + 60 * 60 * 24 * 3) {
      $st = strtotime('Monday this week', $time);
      $st_old = strtotime('Friday 18:00:00 previous week', $time);
      $mon = strtotime('Monday this week', $time);
      $th = strtotime('Thursday this week', $time);
      $fi = strtotime('Monday next week', $time) - 1;
      $fi_old = strtotime('Friday 18:00:00 this week', $time);
      $w = date('W', $th);
      $q = intdiv(date('n', $th) - 1, 3) + 1;
      $y = date('Y', $mon);
      $rweeks["$y-Q$q-$w"] = [
        'week' => $w,
        'year' => $y,
        'quot' => "Q$q",
        'month' => $this->df->format($th, "custom", 'M'),
        'monday' => $this->df->format($mon, "custom", 'D d.m.y'),
        'thursday' => $this->df->format($th, "custom", 'D d.m.y'),
        'st' => $this->df->format($st, "custom", 'D d.m.y H:i'),
        'fi' => $this->df->format($fi, "custom", 'D d.m.y H:i'),
        'st_old' => $this->df->format($st_old, "custom", 'D d.m.y H:i'),
        'fi_old' => $this->df->format($fi_old, "custom", 'D d.m.y H:i'),
        'start' => $st,
        'finish' => $fi,
        'start_old' => $st_old,
        'finish_old' => $fi_old,
      ];
      // +7 days.
      $time = $time + 60 * 60 * 24 * 7;
    }
    $weeks = array_reverse($rweeks);
    return $weeks;
  }

  /**
   * Get.
   */
  public function months($start) {
    $time = strtotime('first day of this month - 1 year');
    $rweeks = [];
    while ($time < time() + 60 * 60 * 24 * 60) {
      $time = strtotime('last day of next month', $time);
      $st = strtotime('first day of this month 00:00', $time);
      $mon = strtotime('Monday this week', $time);
      $th = strtotime('Thursday this week', $time);
      $fi = strtotime('last day of this month 23:59', $time);
      $w = date('W', $th);
      $q = intdiv(date('n', $th) - 1, 3) + 1;
      $y = date('Y', $mon);
      $rweeks["$y-Q$q-$w"] = [
        'time' => $this->df->format($mon, "custom", 'd M Y'),
        'week' => date('n', $th),
        'year' => $y,
        'quot' => "Q$q",
        'month' => $this->df->format($mon, "custom", 'M'),
        'monday' => $this->df->format($mon, "custom", 'D d.m.y'),
        'thursday' => $this->df->format($th, "custom", 'D d.m.y'),
        'st' => $this->df->format($st, "custom", 'D d.m.y H:i'),
        'fi' => $this->df->format($fi, "custom", 'D d.m.y H:i'),
        'start' => $st,
        'finish' => $fi,
      ];
    }
    $weeks = array_reverse($rweeks);
    return $weeks;
  }

}
