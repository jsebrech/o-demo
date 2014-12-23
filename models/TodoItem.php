<?php
namespace O;

class TodoItem {
  /**
   * @var int
   */
  public $id = -1;
  /**
   * @var string
   * @Size(max=200)
   * @NotEmpty
   */
  public $message = "";
  /**
   * @var bool
   */
  public $completed = FALSE;

  public static function nextId() {
    $session = new Session();
    if (!isset($session->nextItemID)) {
      $session->nextItemID = 0;
    };
    return $session->nextItemID++;
  }
}
