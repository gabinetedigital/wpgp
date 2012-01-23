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

/* -- Helper functions, they just support the rest of the really useful
      code present here -- */

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

/* -- Public helper API -- */

function results_old($sql) {
  return get_results(get_old_link(), $sql);
}


function results_new($sql) {
  return get_results(get_new_link(), $sql);
}


function query_new($sql) {
  return mysql_query($sql, get_new_link());
}


function error_new() {
  return mysql_error(get_new_link());
}


/* -- Theme related functions -- */


function import_themes() {
  foreach (results_old("SELECT * FROM GD_TEMA") as $theme) {
    $id = $theme['NRO_INT_TEMA'];

    /* Avoiding duplications */
    if (($r = results_new("SELECT * FROM wpgp_govr_themes WHERE id = $id"))) {
      continue;
    }

    /* Inserting a new theme */
    $sql = "INSERT INTO wpgp_govr_themes (
                id, name, created_at
            ) VALUES (
                '$id','$theme[NOME_TEMA]', now()
            )";
    if (!query_new($sql)) {
      throw new Exception(error_new());
    }
  }
}


/* -- User import functions -- */


function import_users() {
  $usermap = array();
  $basesql = "SELECT distinct(TXT_EMAIL), NRO_INT_INTERNAUTA, DTH_CADASTRO
              FROM GD_INTERNAUTA";
  foreach (results_old($basesql) as $i) {
    $nroi = $i['NRO_INT_INTERNAUTA'];
    $email = $i['TXT_EMAIL'];

    /* avoiding duplication, useful for users that registered again in
     * the new site and for multiple executions of this script. Even
     * here we associate the user id to the bizarre NRO_INT_INTERNAUTA
     * code to be used in the question and voting import function */
    if (($r = results_new("SELECT * FROM wp_users WHERE user_login = '$email'"))) {
      $usermap[$nroi] = $r[0]['ID'];
      continue;
    }

    if (($r1 = results_old("SELECT * FROM CMS_CONTATO WHERE TXT_EMAIL = '$email'"))) {
      $u = $r1[0];
      $user = array("login" => $email,
                    "name"  => $u['TXT_NOME'],
                    "email" => $email,
                    "date"  => $u['CTR_DTH_INC']);
    } else {
      $user = array("login" => $email,
                    "name"  => $email,
                    "email" => $email,
                    "date"  => $i['DTH_CADASTRO']);
    }

    /* A new user! */
    $sql = "INSERT INTO wp_users (
                user_login,
                user_pass,
                user_nicename,
                user_email,
                user_status,
                user_registered,
                display_name
           ) VALUES (
                \"$user[email]\",
                '',
                \"$user[name]\",
                \"$user[email]\",
                1,
                \"$user[date]\",
                \"$user[name]\"
           )";

    if (!query_new($sql)) {
      print $sql;
      throw new Exception(error_new());
    }

    /* Associating the new user to the map that will be used in the
     * function that imports questions and votes */
    $usermap[$nroi] = mysql_insert_id(get_new_link());
  }
  return $usermap;
}


/* -- Import contribs for govr -- */


function import_contrib($usermap) {
  global $status;
  $contribmap = array();
  foreach (results_old("SELECT * FROM GD_PERGUNTA ORDER BY NRO_INT_PERGUNTA") as $c) {
    $oldcontrib = $c['NRO_INT_PERGUNTA'];
    $wpuserid = $usermap[$c['NRO_INTERNAUTA']];
    $status_str = $status[$c['TXT_STATUS']];

    /* Skipping already added contribs */
    $sql = "SELECT * FROM wpgp_govr_contribs WHERE title = '" .
      $c['NOME_PERGUNTA'] . "' AND user_id = " . $wpuserid;
    if (($r = results_new($sql))) {
      $contribmap[$oldcontrib] = $r[0]['id'];
      continue;
    }

    /* Resolving aggregated questions */
    $aggregated = '0';
    if (!empty($c['NRO_PERGUNTA_JUNTADO'])) {
      $aggregated = $contribmap[$c['NRO_PERGUNTA_JUNTADO']];
    }

    $sql = "INSERT INTO wpgp_govr_contribs (
                title,
                theme_id,
                content,
                user_id,
                original,
                created_at,
                status,
                parent,
                score,
                answer
            ) VALUES (
                '$c[NOME_PERGUNTA]',
                '$c[NRO_TEMA]',
                '$c[DESCR_PERGUNTA]',
                '$wpuserid',
                '$c[DESCR_PERGUNTA]',
                '$c[DTH_CRIACAO]',
                '$status_str',
                '$aggregated',
                '$c[NRO_VOTOS]',
                '$c[TXT_RESPOSTA]'
            )";
    if (!query_new($sql)) {
      throw new Exception(error_new());
    }
    $contribmap[$oldcontrib] = mysql_insert_id(get_new_link());
  }
  return $contribmap;
}


function import_votes($usermap, $contribs) {
  foreach (results_old("SELECT * FROM GD_PERGUNTA_TEM_VOTO") as $c) {
    $np = $c['NRO_INT_PERGUNTA'];
    $nu = $c['NRO_INT_INTERNAUTA'];
    $new_question_id = $contribs[$np];
    $new_user_id = $usermap[$nu];

    if (empty($new_question_id)) {
      print_r($c);
      throw new Exception('Could not find a qid for ' . $np);
    }

    if (empty($new_user_id)) {
      print_r($c);
      throw new Exception('Could not find a uid for ' . $nu);
    }

    /* Skipping duplicated registers */
    $sql = "SELECT * FROM wpgp_govr_user_votes
            WHERE user_id = $new_user_id AND
                  contrib_id = $new_question_id";
    if (($r = results_new($sql))) {
      print_r($r);
      continue;
    }

    $sql = "INSERT INTO wpgp_govr_user_votes (user_id, contrib_id, date) VALUES (
      $new_user_id, $new_question_id, '$c[DTH_VOTO]'
    )";

    try {
      if (!query_new($sql)) {
        throw new Exception(error_new());
      }
    } catch (Exception $exc) {
        print $sql . "\n";
        throw $exc;
    }
  }
}


function govr() {
  import_themes();

  $cachefile = "cache/usermap";
  if (file_exists($cachefile)) {
    $usermap = unserialize(file_get_contents($cachefile));
  } else {
    $usermap = import_users();
    file_put_contents($cachefile, serialize($usermap));
  }

  $cachefile = "cache/contribs";
  if (file_exists($cachefile)) {
    $contribs = unserialize(file_get_contents($cachefile));
  } else {
    $contribs = import_contrib($usermap);
    file_put_contents($cachefile, serialize($contribs));
  }

  import_votes($usermap, $contribs);
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
/* govp(); */
?>
