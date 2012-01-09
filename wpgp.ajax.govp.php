<?php

function wpgp_create_session() {
    $name = $_POST['data']['session_name'];
    wpgp_db_create_session($name);
}

function wpgp_delete_session() {
  $id = $_POST['data']['session_id'];
  if (!wpgp_db_delete_session($id)) {
    die("non-empty");
  }
}

add_action('wp_ajax_create_session', 'wpgp_create_session');
add_action('wp_ajax_delete_session', 'wpgp_delete_session');

?>