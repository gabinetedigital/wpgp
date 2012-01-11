<?php
define('HOST', 'localhost');
define('USER', 'root');
define('PASS', '');
define('NAME', 'old_gab');

define('NHOST', 'localhost');
define('NUSER', 'root');
define('NPASS', '');
define('NNAME', 'gd');


$status = array('A' => 'approved',
                'B' => 'blocked',
                'J' => 'pending',//'Juntada',
                'P' => 'pending',
                'R' => 'responded');

$oldlink = null;
$newlink = null;
function get_old_link() {
  global $oldlink;
  if ($oldlink) return $oldlink;

  $link = mysql_connect(HOST, USER, PASS, true);
  if(!$link) throw new Exception(mysql_error($link));

  if (!mysql_select_db(NAME, $link)) {
    throw new Exception(mysql_error($link));
  }
  return $oldlink = $link;
}

function get_new_link() {
  global $newlink;
  if ($newlink) return $newlink;

  $link = mysql_connect(NHOST, NUSER, NPASS, true);
  if(!$link) throw new Exception(mysql_error($link));

  if (!mysql_select_db(NNAME, $link)) {
    throw new Exception(mysql_error($link));
  }
  return $newlink = $link;
}

function get_results($link, $sql) {
  $res = mysql_query($sql, $link);
  if (!$res) {
    throw new Exception(mysql_error($link));
  }

  $ret = array();
  while ($row = mysql_fetch_array($res)) {
    $ret[] = $row;
  }

  return $ret;
}

function import_themes() {
  foreach(get_results(get_old_link(), "SELECT * FROM GD_TEMA") as $theme) {
    $sql = "INSERT INTO wpgp_govr_themes (id, name, created_at) VALUES
          ('$theme[NRO_INT_TEMA]','$theme[NOME_TEMA]', now())";
    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
  }
}

function import_users() {
  $usermap = array();
  foreach(get_results(get_old_link(),"SELECT * FROM GD_INTERNAUTA") as $u) {
    $sql = "INSERT INTO wp_usersa
            (user_login,user_pass,user_nicename,user_email, user_status,
            display_name)
            VALUES
            ('$u[TXT_EMAIL]','','$u[NOME_INTERNAUTA]','$u[TXT_EMAIL]',
             1, '$u[NOME_INTERNAUTA]')";
    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
    $usermap[$u['NRO_INT_INTERNAUTA']] = mysql_insert_id(get_new_link());
  }
  return $usermap;
}

function import_contrib($usermap) {
  global $status;
  foreach(get_results(get_old_link(),"SELECT * FROM GD_PERGUNTA") as $c) {
    $sql = "INSERT INTO wpgp_govr_contribs
            (title,
             theme_id,
             content,
             user_id,
             original,
             created_at,
             status, parent, score, resposta)
            VALUES
            ('$c[NOME_PERGUNTA]',
             '$c[NRO_TEMA]',
             '$c[DESCR_PERGUNTA]',
             '".$usermap[$c['NRO_INTERNAUTA']]."',
             '$c[DESCR_PERGUNTA]',
             '$c[DTH_CRIACAO]',
             '".$status[$c['TXT_STATUS']]."',
             '$c[NRO_PERGUNTA_JUNTADO]', '$c[NRO_VOTOS]','$c[TXT_RESPOSTA]')";
    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
  }
}

import_themes();
import_contrib(import_users());
?>