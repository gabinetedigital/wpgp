<?php

function wpgp_ajax_govp_create_session() {
    $name = $_POST['data']['session_name'];
    wpgp_db_govp_create_session($name);
}

function wpgp_ajax_govp_delete_session() {
  $id = $_POST['data']['session_id'];
  if (!wpgp_db_govp_delete_session($id)) {
    die("non-empty");
  }
}

function wpgp_ajax_govp_create_theme() {
  $id = $_POST['data']['session_id'];
  $name = $_POST['data']['name'];
  if (wpgp_db_govp_create_theme($id, $name)) {
    die('ok');
  } else {
    die('error');
  }
}

function wpgp_ajax_govp_save_config() {
  wpgp_db_govp_save_config(
                           $_POST['data']['session_id'],
                           $_POST['data']['name'],
                           $_POST['data']['purl'],
                           $_POST['data']['phost'],
                           $_POST['data']['pname'],
                           $_POST['data']['puser'],
                           $_POST['data']['ppass']);
}

function wpgp_ajax_govp_create_contrib() {
  $current_user = wp_get_current_user();
  wpgp_db_govp_create_contrib($_POST['data']['title'],
                              $_POST['data']['theme_id'],
                              $_POST['data']['content'],
                              $current_user->ID,
                              $_POST['data']['part']);
}

function wpgp_ajax_govp_delete_contrib() {
  $id = $_POST['data']['id'];
  $org = wpgp_db_govp_get_contrib($id);
  wpgp_db_govp_delete_contrib($id, !!$org['created_by_moderation']);
}

function wpgp_ajax_govp_update_contrib() {
  error_log($_POST['data']['id']);

    $org = wpgp_db_govp_get_contrib($_POST['data']['id']);
    switch ($_POST['data']['field']) {
    case 'content':
    case 'title':
    case 'status':
    case 'theme_id':
      wpgp_db_govp_update_contrib($_POST['data']['id'],
                                  $_POST['data']['field'],
                                  $_POST['data']['value']);
      break;
    case 'parent':
      $_POST['data']['value'] =
        trim($_POST['data']['value']) === "" ? "0" : $_POST['data']['value'];

      if ($_POST['data']['value'] != "0") {
        $parent = wpgp_db_govp_get_contrib($_POST['data']['value']);
        if ($parent == null) {
          die("not-found");
        }
      }
      wpgp_db_govp_update_contrib($_POST['data']['id'],
                                  $_POST['data']['field'],
                                  $_POST['data']['value']);
      break;
    case 'part':
      $org = wpgp_db_govp_get_contrib($_POST['data']['id']);
      if ($_POST['data']['value'] != "0") {
        /* If the string comes empty the user want to nuke all
         * children contribs, let's grant his/her wish */
        if (empty($_POST['data']['value'])) {
          foreach (wpgp_govp_contrib_get_parents($org) as $parent) {
            wpgp_govp_contrib_remove_part($parent, $org);
          }
          die('ok');
        }
        if (wpgp_govp_contrib_insert_parts($org,$_POST['data']['value'])) {
          die('ok');
        } else {
          die('not-found');
        }
      }
      die("ok");
      break;
    }
}

add_action('wp_ajax_govp_create_session', 'wpgp_ajax_govp_create_session');
add_action('wp_ajax_govp_delete_session', 'wpgp_ajax_govp_delete_session');
add_action('wp_ajax_govp_create_theme', 'wpgp_ajax_govp_create_theme');
add_action('wp_ajax_govp_save_config', 'wpgp_ajax_govp_save_config');
add_action('wp_ajax_govp_create_contrib', 'wpgp_ajax_govp_create_contrib');
add_action('wp_ajax_govp_delete_contrib', 'wpgp_ajax_govp_delete_contrib');
add_action('wp_ajax_govp_update_contrib', 'wpgp_ajax_govp_update_contrib');

?>