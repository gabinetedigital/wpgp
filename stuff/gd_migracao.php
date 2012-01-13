<?php
define('HOST', 'localhost');
define('USER', 'root');
define('PASS', '');
define('NAME', 'old_gab');

define('NHOST', 'localhost');
define('NUSER', 'root');
define('NPASS', '');
define('NNAME', 'gd');


define('PHOST', 'localhost');
define('PUSER', 'root');
define('PPASS', '');
define('PNAME', 'pairwise');


$status = array('A' => 'approved',
                'B' => 'blocked',
                'J' => 'pending',//'Juntada',
                'P' => 'pending',
                'R' => 'responded');

$oldlink = null;
$newlink = null;

$pairwise_link = null;

function pairwise_link() {
  global $pairwise_link;
  if ($pairwise_link) return $pairwise_link;

  $link = mysql_connect(PHOST, PUSER, PPASS, true);
  if(!$link) throw new Exception(mysql_error($link));

  if (!mysql_select_db(PNAME, $link)) {
    throw new Exception(mysql_error($link));
  }
  return $pairwise_link = $link;
}


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
    $sql = "INSERT INTO wp_users
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

function govr() {
  import_themes();
  import_contrib(import_users());
}

function govp() {
  //mysql_set_charset("utf8", get_new_link());
  //die(mysql_client_encoding(get_new_link()));


  //inserting Saude session
  $sql = "INSERT INTO wpgp_govp_sessions (name, created_at)
          VALUES ('".utf8_decode('Saúde')."', now())";

  if (!mysql_query($sql, get_new_link())) {
    throw new Exception(mysql_error(get_new_link()));
  }

  //pairwise choices:
  $choices = array();
  foreach(get_results(pairwise_link(), 'SELECT * FROM choices') as $c) {
      $json = json_decode($c['data']);
      $choices[$json->id] = $c['score'];
  }

  $session_id = mysql_insert_id(get_new_link());

  //...its themes
  $themes = array('cuidado' => 1,
                  'familia' => 2,
                  'emergencia' => 3,
                  'medicamentos' => 4,
                  'regional' => 5);
  foreach($themes as $t => $id) {
    $sql = "INSERT INTO wpgp_govp_themes (id, session_id, name) VALUES
          ($id, '$session_id','$t')";
    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
  }

  //its contribs

  foreach(get_results(get_new_link(),"SELECT * FROM contrib") as $c) {
    $theme_id = $themes[$c['theme']];
    $status = $c['status'] == 0 ? 'pending' : 'approved';

    if ($c['status'] == 1) {
      $score = $choices[$c['id']];
    } else {
      $score = 0;
    }

    $content = utf8_decode(addslashes($c['content']));
    $title = utf8_decode(addslashes($c['title']));
    $original = utf8_decode(addslashes($c['original']
                                       ? $c['original'] : $c['content']));

    $sql = "INSERT INTO wpgp_govp_contribs
            (id,
             title,
             theme_id,
             content,
             user_id,
             original,
             created_at,
             status,
             parent,
             created_by_moderation,
             score)
            VALUES
            ($c[id],
             '$title',
             '$theme_id',
             '$content',
             '$c[user_id]',
             '$original',
             '$c[creation_date]',
             '$status',
             '$c[parent]',
             '$c[moderation]',
             '$score')";

    echo "$sql\n\n--\n";

    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
  }

  //childs
  foreach(get_results(get_new_link(),
                      'SELECT * FROM  contrib_children__contrib' )
          as $c) {
    $sql = "INSERT INTO wpgp_govp_contrib_children
            (inverse_id, children_id)
             VALUES
            ($c[inverse_id], $c[children_id])";
  }
    if (!mysql_query($sql, get_new_link())) {
      throw new Exception(mysql_error(get_new_link()));
    }
}

govr();
govp();
?>