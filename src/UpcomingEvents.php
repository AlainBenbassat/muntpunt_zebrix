<?php

namespace Drupal\muntpunt_zebrix;

class UpcomingEvents {
  private const EVENT_TYPE_NORMAL = '11,24,25,30,26,27,28,31,29,36,44,33,32,9,35,6,20,49,50';
  private const EVENT_TYPE_TE_GAST = '39,48';

  public function printUpcomingEvents() {
    $this->printHtmlHeader();
    $this->printTodaysDate();
    $this->printEvents();
    $this->printEventsTeGast();
    $this->printHtmlFooter();
  }

  private function printHtmlHeader() {
    echo '
      <!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta http-equiv="X-UA-Compatible" content="ie=edge">
          <meta http-equiv="refresh" content="300" >
          <title>Muntpunt evenementen</title>
          <link rel="stylesheet" href="css/style.css">
        </head>
        <body>
    ';
  }

  private function printTodaysDate() {
    echo '<p style="font-size: 54px;" class="datumvandaag">';
    echo $this->getDateWeekDay() . ' ';
    echo $this->getDateDay() . ' ';
    echo $this->getDateMonth();
    echo '</p>';
  }

  private function printEvents() {
    $dao = $this->getEvents(self::EVENT_TYPE_NORMAL);

    while ($dao->fetch()) {
      echo '<p><span style="font-size: 40px;">' . $dao->title . '</span><br><span style="font-size: 32px;">';
      $today = date('Y-m-d');
      $einddatum = $dao->Einddatum;
      if ($einddatum !== $today) {
        echo 'DOORLOPEND';
      }
      else {
        echo $dao->Startuur . ' -  ' . $dao->Einduur;
      }

      echo ' / ' . strtoupper(preg_replace("/\x01/",", ",substr($dao->Zaal,1,-1))) . '</span></p>';
      echo '<hr >';
    }
  }

  private function printEventsTeGast() {
    $dao = $this->getEvents(self::EVENT_TYPE_TE_GAST);

    if ($dao->N > 0) {
      echo '<p  style="font-size: 60px;" class="tegast">TE GAST</p>';

      while ($dao->fetch()) {
        echo '<p><span style="font-size: 40px;">' . $dao->title . '</span><br/><span style="font-size: 32px;">';

        echo $dao->Startuur . ' -  ' . $dao->Einduur;
        echo ' / ' . strtoupper(preg_replace("/\x01/", ", ", substr($dao->Zaal, 1, -1))) . '</span></p>';
        echo '<hr >';
      }
    }
  }

  private function printHtmlFooter() {
    echo '
      </body>
      </html>
    ';
  }

  private function getDateDay() {
    return date('j');
  }

  private function getDateMonth() {
    $months = [
      'January' => 'JANUARI',
      'February' => 'FEBRUARI',
      'March' => 'MAART',
      'April' => 'APRIL',
      'May' => 'MEI',
      'June' => 'JUNI',
      'July' => 'JULI',
      'August' => 'AUGUSTUS',
      'September' => 'SEPTEMBER',
      'October' => 'OKTOBER',
      'November' => 'NOVEMBER',
      'December' => 'DECEMBER'
    ];

    $month = date('F');

    return $months[$month];
  }

  private function getDateWeekDay() {
    $days = [
      'Monday' => 'MAANDAG',
      'Tuesday' => 'DINSDAG',
      'Wednesday' => 'WOENSDAG',
      'Thursday' => 'DONDERDAG',
      'Friday' => 'VRIJDAG',
      'Saturday' => 'ZATERDAG',
      'Sunday' => 'ZONDAG'
    ];

    $day = date('l');

    return $days[$day];
  }

  private function getEvents($eventTypeList) {
    $sql = "
      SELECT
        title,
        start_date,
        end_date,
        DATE_FORMAT(start_date,'%H:%i') AS Startuur,
        DATE_FORMAT(end_date,'%H:%i') AS Einduur,
        DATE_FORMAT(start_date,'%Y-%m-%d') AS Startdatum,
        DATE_FORMAT(end_date,'%Y-%m-%d') AS Einddatum,
        d.muntpunt_zalen As Zaal,
        d.activiteit_status,
        event_type_id,
        c.label
      FROM
        civicrm_event a
      LEFT JOIN
        civicrm_value_extra_evenement_info d ON a.id = d.entity_id
      LEFT JOIN
        civicrm_option_value c ON event_type_id = c.value
      WHERE
        (
          DATE_FORMAT(now(),'%d %M %Y') = DATE_FORMAT (start_date, '%d %M %Y')
        OR
          (
            DATE_FORMAT(start_date,'%Y %m %d')  <= DATE_FORMAT(now(),'%Y %m %d')
          AND
            DATE_FORMAT(end_date,'%Y %m %d') >= DATE_FORMAT(now(),'%Y %m %d')
          )
        )
      AND
        d.muntpunt_zalen NOT LIKE ''
      AND
        d.activiteit_status IN (2,5)
      AND
        c.option_group_id = 15
      AND
        event_type_id IN ($eventTypeList)
      ORDER BY
        start_date, Startuur, title
      LIMIT
        0,10
    ";

    \Drupal::service('civicrm')->initialize();
    return \CRM_Core_DAO::executeQuery($sql);
  }

}
