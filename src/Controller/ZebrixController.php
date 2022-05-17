<?php

namespace Drupal\muntpunt_zebrix\Controller;

use Drupal\Core\Controller\ControllerBase;

class ZebrixController extends ControllerBase {
  public function hello() {
    return ['#markup' => $this->t("Hello World!")];
  }

}
