<?php

namespace O;
include "vendor/autoload.php";
O::init();

include("models/TodoItem.php");

$session = new Session();

if (!is_array($session->items)) {
// if no tasks yet, create one
  $session->items = array(o(array(
    "id" => TodoItem::nextId(),
    "message" => "Create a demo app",
    "completed" => TRUE
  ))->cast("TodoItem"));
};

$errorMsg = "";

if ($session->isCSRFProtected()) {

  // list of ids of submitted items
  $ids = ca($_REQUEST)->keys()
    ->filter(function($key) { return s($key)->pos("item-") === 0; })
       ->map(function($key) { return s($key)->substr(5); });

  // read the submitted items and delete / update
  foreach ($ids as $id) {

    $item = o(array(
      "id" => $id,
      "message" => isset($_REQUEST["message-".$id]) ? $_REQUEST["message-".$id] : "",
      "completed" => isset($_REQUEST["completed-".$id]) ? $_REQUEST["completed-".$id] : FALSE
    ))->cast("TodoItem");

    // if item should be deleted
    if (s($item->message)->trim() == "") {
      $session->items = a($session->items)->filter(
        function($o) use($id) { return $o->id != $id; }
      );
    // if item should be updated
    } else {
      if (o($item)->validate($errors)) {
        // save to session if valid
        foreach ($session->items as $i => $stored) {
          if ($stored->id === $item->id) {
            $session->items[$i] = $item;
          };
        };
      } else {
        $errorMsg = $errors[0]->message;
      };
    };
  };

  // if an item should be added
  if (isset($_REQUEST["action-add"])) {
    $item = o(array(
      "id" => TodoItem::nextId(),
      "message" => isset($_REQUEST["new-todo"]) ? $_REQUEST["new-todo"] : "",
      "completed" => FALSE
    ))->cast("TodoItem");
    $errors = Validator::validate($item);
    // save to session if valid
    if (count($errors) == 0) {
      $session->items[] = $item;
    } else {
      $errorMsg = $errors[0]->message;
    };
  };

  // if the completed items should be deleted
  if (isset($_REQUEST["action-clear-completed"])) {
    $session->items = a($session->items)->filter(
      function($o) { return !$o->completed; }
    );
  };
};

$completedCount = ca($session->items)->filter(
  function($o) { return $o->completed; }
  )->count();
$todoCount = count($session->items) - $completedCount;

o(compact(
  "session", "errorMsg", "completedCount", "todoCount"
))->render("views/main.phtml");
