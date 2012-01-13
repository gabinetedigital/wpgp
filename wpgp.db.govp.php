<?php /* -*- Mode: php; c-basic-offset:4; -*- */
/* Copyright (C) 2011  Governo do Estado do Rio Grande do Sul
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function wpgp_db_govp_create_session($name) {
    global $wpdb;
    $sql = $wpdb->prepare("
		INSERT INTO ".WPGP_GOVP_SESSION_TABLE."
		( name, created_at )
		VALUES ( %s, now() )",
                          array($name));
    $wpdb->query($sql);
}

function wpgp_db_govp_get_sessions() {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVP_SESSION_TABLE;
    $sessions = $wpdb->get_results($sql, ARRAY_A);
    foreach ($sessions as &$s) {
      $s['total_contributions'] =
        $wpdb->get_var("SELECT COUNT(*)
                        FROM ".WPGP_GOVP_CONTRIB_TABLE." contrib, "
                        .WPGP_GOVP_THEME_TABLE." theme
                        WHERE contrib.deleted = 0
                        AND contrib.theme_id=theme.id
                        AND theme.session_id={$s[id]}");
    }
    return $sessions;
}

function wpgp_db_govp_get_session($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".WPGP_GOVP_SESSION_TABLE, array($id));
    return $wpdb->get_row($sql, ARRAY_A);
}


function wpgp_db_govp_delete_session($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM ".WPGP_GOVP_CONTRIB_TABLE."
            WHERE theme_id=%d", array($id));

    $count = $wpdb->get_var($sql);
    if ($count == 0) {
      $sql = $wpdb->prepare("DELETE FROM ".WPGP_GOVP_SESSION_TABLE."
                             WHERE ID=%d", array($id));
      $wpdb->query($sql);
      return true;
    }
    return false;
}

function wpgp_db_govp_get_themes($session_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".WPGP_GOVP_THEME_TABLE,
                          array($session_id));
    $themes = $wpdb->get_results($sql, ARRAY_A);
    foreach ($themes as &$t) {
      $t['total_contributions'] =
        $wpdb->get_var("SELECT COUNT(*) FROM ".WPGP_GOVP_CONTRIB_TABLE."
                        WHERE deleted=0 AND theme_id={$t[id]}");
    }
    return $themes;
}

function wpgp_db_govp_create_theme($session_id, $name) {
    global $wpdb;
    return $wpdb->insert(
                         WPGP_GOVP_THEME_TABLE,
                         array("session_id"  => $session_id,
                               "name" => $name));
}

function wpgp_db_govp_save_config($session_id, $name,
                                  $pairwise_url, $pairwise_db_host,
                                  $pairwise_db_name, $pairwise_db_user,
                                  $pairwise_db_pass) {
    global $wpdb;
    $wpdb->update(WPGP_GOVP_SESSION_TABLE,
                  array('name' => $name,
                        'pairwise_url' => $pairwise_url,
                        'pairwise_db_host' => $pairwise_db_host,
                        'pairwise_db_name' => $pairwise_db_name,
                        'pairwise_db_user' => $pairwise_db_user,
                        'pairwise_db_pass' => $pairwise_db_pass),
                  array('id' => $session_id));
}

function wpgp_db_govp_get_session_contribs($session_id,
                                           $page = '0',
                                           $sortby = 'contrib.id',
                                           $theme_id = null,
                                           $status = 0,
                                           $from = null,
                                           $to = null,
                                           $s = null,
                                           $filter = null,
                                           $perpage = WPGP_CONTRIBS_PER_PAGE) {
  global $wpdb;
  $offset = $page * $perpage;
  $sortfields = array(
                      'id' => 'contrib.id' ,
                      'status' => 'contrib.status',
                      'theme' => 'contrib.theme_id',
                      'date'  => 'contrib.created_at',
                      'author' => 'user.display_name',
                      'title' => 'contrib.title'
                      );

  if (isset($sortfields[$sortby])) {
      $sortfield = $sortfields[$sortby];
  } else {
      $sortfield = 'contrib.id';
  }

  $themefilter = '';
  if ($theme_id) {
      $themefilter = " AND contrib.theme_id = $theme_id ";
  }

  $statusfilter = '';
  if ($status == 1) { //approved
      $statusfilter = " AND contrib.status='approved' ";
  } else if ($status == -1) { //pending
      $statusfilter = " AND contrib.status <> 'approved' ";
  }

  $fromto = '';
  if($from && $to) {
      $from = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$from);
      $to = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$to);
      $fromto = " AND (DATE(contrib.created_at) > DATE($from)
                 AND DATE(contrib.created_at) < DATE($to)) ";
  }

  $sql_base = $wpdb->prepare("
      FROM
          ".WPGP_GOVP_CONTRIB_TABLE." contrib,
          ".WPGP_GOVP_THEME_TABLE." theme,
          wp_users user
      WHERE
          (theme.session_id=%d
           AND theme.id=contrib.theme_id
           AND contrib.user_id=user.ID
           AND contrib.deleted=0)
           $themefilter
           $fromto $statusfilter
           $filter $search
      ORDER BY $sortfield
    ", array($session_id));

  $sql = "SELECT contrib.*,
          theme.name as theme_name,
          user.display_name as display_name  $sql_base ";
  $sql = $wpdb->prepare($sql
                        ." LIMIT %d, %d",array($offset,$perpage));
  $listing = $wpdb->get_results($sql, ARRAY_A);

  $sql = $wpdb->prepare("SELECT COUNT(*) $sql_base");
  $count = $wpdb->get_var($sql);
  return array($listing, $count);
}

function wpgp_db_govp_get_contrib($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".WPGP_GOVP_CONTRIB_TABLE."
                           WHERE id=%d",array($id));
    return $wpdb->get_row($sql, ARRAY_A);
}

function wpgp_govp_contrib_remove_all_parts($contrib) {
    global $wpdb;
    $sql = "DELETE FROM ".WPGP_GOVP_CONTRIBC_TABLE." WHERE
      inverse_id = ${contrib[id]};";
    $wpdb->query($wpdb->prepare($sql));
}


function wpgp_govp_contrib_append_part($contrib, $child) {
    global $wpdb;
    $wpdb->insert(
        WPGP_GOVP_CONTRIBC_TABLE,
        array("inverse_id"  => $contrib['id'],
              "children_id" => $child['id'])
    );
}

function wpgp_govp_contrib_insert_parts($org,$parts) {
    $pids = explode(" ", $parts);

    /* FIXME: avoid calling _get_contrib() twice for each
     * contrib in this function */
    foreach ($pids as $pid) {
        $parent = wpgp_db_govp_get_contrib($pid);
        if ($parent == null) {
            return false;
        }
    }

    /* Removing all parts previously added */
    wpgp_govp_contrib_remove_all_parts($org);

    /* Now we're sure that everything's good, so we can insert
     * the new parts. */
    foreach ($pids as $pid) {
        $parent = wpgp_db_govp_get_contrib($pid);
        wpgp_govp_contrib_append_part($parent, $org);
    }
    return true;
}

function wpgp_db_govp_create_contrib($title
                                     , $theme_id
                                     , $content
                                     , $user_id
                                     , $part
                                     , $moderation = 1
                                     , $parent = 0) {
    global $wpdb;
    $sql = $wpdb->prepare("
		INSERT INTO ".WPGP_GOVP_CONTRIB_TABLE."
		( title, theme_id, content, user_id, original, created_at,
                  status, parent, created_by_moderation )
		VALUES ( %s, %d, %s, %d, %s, now(), 'pending', %d, %d )",
                          array($title, $theme_id, $content, $user_id,
                                $content, $parent, $moderation));

    $ret = $wpdb->query($sql);
    if (strlen(trim($part)) > 0) {
        $contrib = wpgp_db_govp_get_contrib($wpdb->insert_id);
        wpgp_govp_contrib_insert_parts($contrib, $part);
    }
}

function wpgp_db_govp_get_contrib_count($session_id) {
    global $wpdb;
    $sql = $wpdb->prepare(
           "SELECT count(*) FROM
           ".WPGP_GOVP_CONTRIB_TABLE." contrib,
           ".WPGP_GOVP_THEME_TABLE." theme
            WHERE theme.session_id=%d
            AND contrib.theme_id=theme.id
            AND contrib.deleted=0", array($session_id));
    return $wpdb->get_var($sql);
}

function wpgp_db_get_theme_counts() {
    return array();
}

function wpgp_db_govp_delete_contrib($id, $hard = false) {
    global $wpdb;
    if ($hard) {
        $wpdb->query($wpdb->prepare("DELETE FROM ".WPGP_GOVP_CONTRIB_TABLE."
                                     WHERE id=%d", array($id)));
    } else {
        $wpdb->update(WPGP_GOVP_CONTRIB_TABLE,
                      array('deleted' => 1),
                      array('id' => $id));
    }
    // reset parent
    $wpdb->update(WPGP_GOVP_CONTRIB_TABLE,
                  array('parent' => 0),
                  array('parent' => $id));

    // reset parts
    die($wpdb->update(WPGP_GOVP_CONTRIB_TABLE,
                      array('part' => 0),
                      array('part' => $id)));
}

function wpgp_db_govp_update_contrib($id, $field, $value) {
    global $wpdb;
    return $wpdb->update(WPGP_GOVP_CONTRIB_TABLE,
                         array($field => $value),
                         array('id' => $id));
}

function wpgp_govp_contrib_get_parents($contrib) {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVP_CONTRIB_TABLE." contrib,
                           ".WPGP_GOVP_CONTRIBC_TABLE." children
      WHERE
        children.children_id = ${contrib[id]} AND
        children.inverse_id = contrib.id";
    return $wpdb->get_results($sql, ARRAY_A);
}

function wpgp_govp_contrib_has_duplicates($contrib) {
    global $wpdb;

    if ($contrib['parent'] > 0) return true;

    $sql = "SELECT COUNT(*)
            FROM ".WPGP_GOVP_CONTRIB_TABLE."
            WHERE parent=%d AND deleted=0";
    return $wpdb->get_var($wpdb->prepare($sql, $contrib['id'])) > 0;
}

function wpgp_govp_contrib_get_duplicates($contrib) {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVP_CONTRIB_TABLE."
            WHERE parent=%d AND deleted=0";
    return $wpdb->get_results($wpdb->prepare($sql, $contrib['id']), ARRAY_A);
}

function wpgp_govp_contrib_has_children($contrib) {
    global $wpdb;
    $sql = "SELECT count(*)
            FROM ".WPGP_GOVP_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVP_CONTRIBC_TABLE." ch
      WHERE
        ch.inverse_id = ${contrib[id]} AND
        ch.children_id = contrib.id";
    return $wpdb->get_var($wpdb->prepare($sql)) > 0;
}

function wpgp_govp_contrib_get_children($contrib) {
    global $wpdb;
    $sql = "SELECT *
            FROM ".WPGP_GOVP_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVP_CONTRIBC_TABLE." ch
      WHERE
        ch.inverse_id = ${contrib[id]} AND
        ch.children_id = contrib.id";
    return $wpdb->get_results($sql, ARRAY_A);
}

function wpgp_db_govp_contribs_scores($session_id,
                                      $page,
                                      $perpage = WPGP_CONTRIBS_PER_PAGE) {
  global $wpdb;
  $offset = $page * $perpage;

  $sql_base = $wpdb->prepare("
      FROM
          ".WPGP_GOVP_CONTRIB_TABLE." contrib,
          ".WPGP_GOVP_THEME_TABLE." theme,
          wp_users user
      WHERE
          (theme.session_id=%d
           AND contrib.theme_id=theme.id
           AND contrib.user_id=user.ID
           AND contrib.deleted=0)
      ORDER BY score DESC", array($session_id));

  $sql = "SELECT contrib.*,
          theme.name as theme_name,
          user.display_name as display_name" . $sql_base;

  $sql = $wpdb->prepare($sql
                        ." LIMIT %d, %d",array($offset,$perpage));
  $listing = $wpdb->get_results($sql, ARRAY_A);
  $sql = $wpdb->prepare("SELECT COUNT(*) $sql_base");
  $count = $wpdb->get_var($sql);

  return array($listing, $count);
}

function
wpgp_db_govp_contribs_theme_scores($theme_id,
                                   $page,
                                   $perpage = WPGP_CONTRIBS_PER_PAGE) {
  global $wpdb;
  $offset = $page * $perpage;

  $sql_base = $wpdb->prepare("
      FROM
          ".WPGP_GOVP_CONTRIB_TABLE." contrib,
          ".WPGP_GOVP_THEME_TABLE." theme,
          wp_users user
      WHERE
          (theme.id=%d
           AND contrib.theme_id=theme.id
           AND contrib.user_id=user.ID
           AND contrib.deleted=0)
      ORDER BY score DESC", array($theme_id));

  $sql = "SELECT contrib.*,
          theme.name as theme_name,
          user.display_name as display_name" . $sql_base;

  $sql = $wpdb->prepare($sql
                        ." LIMIT %d, %d",array($offset,$perpage));
  $listing = $wpdb->get_results($sql, ARRAY_A);
  $sql = $wpdb->prepare("SELECT COUNT(*) $sql_base");
  $count = $wpdb->get_var($sql);

  return array($listing, $count);
}

function wpgp_db_govp_contrib_count_grouped_by_date($session_id) {
    global $wpdb;
    $sql = $wpdb->prepare(
    "SELECT
      year(c.created_at) AS year,
      month(c.created_at) AS month,
      day(c.created_at) AS day,
      date(c.created_at) AS date,
      count(c.id) AS count
    FROM ".WPGP_GOVP_CONTRIB_TABLE." AS c,
         ".WPGP_GOVP_THEME_TABLE." AS t
    WHERE t.session_id=%d
          AND c.theme_id=t.id
    GROUP BY DATE(c.created_at);", array($session_id));
    return $wpdb->get_results($sql, ARRAY_A);
}


function wpgp_db_govp_contrib_count_grouped_by_theme($session_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT
      t.name, count(c.id) AS count
    FROM ".WPGP_GOVP_CONTRIB_TABLE." AS c,
         ".WPGP_GOVP_THEME_TABLE." AS t
    WHERE t.session_id=%d
          AND c.theme_id=t.id
    GROUP BY t.id;", array($session_id));
    return $wpdb->get_results($sql, ARRAY_A);
}

function wpgp_db_govp_contrib_count_grouped_by_themedate($session_id) {
    global $wpdb;
    $sql = $wpdb->prepare(
    "SELECT
      c.theme,
      date(c.creation_date) AS date,
      count(c.id) AS count,
      year(c.creation_date) AS year,
      month(c.creation_date) AS month,
      day(c.creation_date) AS day,
      date(c.creation_date) AS date
    FROM contrib AS c GROUP BY c.theme, date(c.creation_date);",
    array($session_id));
    return $wpdb->get_results($sql, ARRAY_A);
}

?>
