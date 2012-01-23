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


function wpgp_db_govr_get_contrib_status() {
    /* Please remember to update the enum in the SQL that builds the
       table that stores contribs in the `wpgp.php' module. */
    return array('pending', 'blocked', 'approved', 'responded');
}


function wpgp_db_govr_create_theme($name) {
    global $wpdb;
    $sql = $wpdb->prepare("
		INSERT INTO ".WPGP_GOVR_THEME_TABLE."
		( name, created_at )
		VALUES ( %s, now() )",
                          array($name));
    $wpdb->query($sql);
}

function wpgp_db_govr_get_themes() {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVR_THEME_TABLE;
    $themes = $wpdb->get_results($sql, ARRAY_A);
    foreach ($themes as &$t) {
        $t['total_contributions'] =
            $wpdb->get_var("SELECT COUNT(*) FROM ".WPGP_GOVR_CONTRIB_TABLE."
                        WHERE deleted=0 AND theme_id={$t[id]}");
    }
    return $themes;
}

function wpgp_db_govr_delete_theme($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM ".WPGP_GOVR_CONTRIB_TABLE."
            WHERE theme_id=%d", array($id));

    $count = $wpdb->get_var($sql);
    if ($count == 0) {
        $sql = $wpdb->prepare("DELETE FROM ".WPGP_GOVR_THEME_TABLE."
                             WHERE ID=%d", array($id));
        $wpdb->query($sql);
        return true;
    }
    return false;
}

function wpgp_db_govr_get_theme($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".WPGP_GOVR_THEME_TABLE."
                             WHERE ID=%d", array($id));
    return $wpdb->get_row($sql, ARRAY_A);
}

function wpgp_db_govr_get_contribs($theme_id = null,
                                   $page = '0',
                                   $sortby = 'contrib.id',
                                   $from = null,
                                   $to = null,
                                   $status = '',
                                   $filter = null,
                                   $perpage = WPGP_CONTRIBS_PER_PAGE) {
    global $wpdb;
    $offset = $page * $perpage;
    $sortfields = array('id' => 'contrib.id' ,
                        'status' => 'contrib.status',
                        'date'  => 'contrib.created_at',
                        'author' => 'user.display_name',
                        'title' => 'contrib.title',
                        'score' => 'contrib.score'
                        );
    if (isset($sortfields[$sortby])) {
        $sortfield = $sortfields[$sortby];
    } else {
        $sortfield = 'contrib.id';
    }

    $statusfilter = '';
    if (!empty($status)) {
        $statusfilter = " AND contrib.status = '$status' ";
    }

    $fromto = '';
    if($from && $to) {
        $from = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'', $from);
        $to = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'', $to);
        $fromto = " AND (DATE(contrib.created_at) > DATE($from) AND
                         DATE(contrib.created_at) < DATE($to)) ";
    }

    if ($theme_id && !empty($theme_id)) {
        $sql_base = $wpdb->prepare("
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." cchild,
                 wp_users user
            WHERE (contrib.theme_id = %d AND
                   contrib.user_id = user.ID AND
                   contrib.deleted = 0)
                   $fromto $statusfilter $filter
                   ORDER BY $sortfield",
            array($theme_id));
    } else {
        $sql_base = "
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." cchild,
                 wp_users user
            WHERE (contrib.user_id = user.ID AND
                   contrib.deleted = 0)
                   $fromto $statusfilter $filter
                   ORDER BY $sortfield";
    }

    /* Finish building the select and execute it */
    $sql = "SELECT contrib.*, user.display_name as display_name  $sql_base ";
    $sql = $wpdb->prepare($sql . " LIMIT %d, %d", array($offset, $perpage));
    $listing = $wpdb->get_results($sql, ARRAY_A);

    /* Counting how many results were returned (without the LIMIT
     * statement) */
    $sql = $wpdb->prepare("SELECT COUNT(*) $sql_base");
    $count = $wpdb->get_var($sql);
    return array($listing, $count);
}


function wpgp_db_govr_get_theme_contribs($theme_id,
                                         $page = '0',
                                         $sortby = 'contrib.id',
                                         $from = null,
                                         $to = null,
                                         $status = 0,
                                         $filter = null,
                                         $perpage = WPGP_CONTRIBS_PER_PAGE) {

    return wpgp_db_govr_get_contribs(
        $theme_id, $page, $sortby, $from, $to,
        $status, $filter, $perpage);
}


/**
 * Shortcut to lists contribs that are able to be voted
 *
 * The difference between this function and the generic _get_contribs()
 * or the _get_theme_contribs() shortcut is that we'll not list contribs
 * that were aggregated to another one.
 *
 * To be clear, if a contrib is a dupplication or a child of another
 * one, it will not be listed here.
 */
function wpgp_db_govr_get_voting_contribs($theme_id,
                                          $page = '0',
                                          $sortby,
                                          $from = null,
                                          $to = null,
                                          $perpage = WPGP_CONTRIBS_PER_PAGE) {
    $status = 'approved';
    $filter = " AND (
        contrib.parent = 0 AND
        contrib.id != cchild.children_id
    )";
    return wpgp_db_govr_get_contribs(
        $theme_id, $page, $sortby, $from, $to,
        $status, $filter, $perpage);
}


function wpgp_db_govr_get_theme_counts() {
    global $wpdb;
    $sql = "SELECT theme_id, count(*) as count
            FROM ".WPGP_GOVR_CONTRIB_TABLE." group by theme_id
            WHERE deleted=0";
    $ret = array();
    foreach ($wpdb->get_results($wpdb->prepare($sql), ARRAY_A) as $row) {
        $ret[$row['theme_id']] = $row['count'];
    }
    return $ret;
}

function wpgp_db_govr_get_contrib_count() {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT COUNT(*)
                           FROM ".WPGP_GOVR_CONTRIB_TABLE."
                           WHERE deleted=0");
    return $wpdb->get_var($sql);
}

function wpgp_db_govr_get_contrib($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".WPGP_GOVR_CONTRIB_TABLE."
                           WHERE id=%d",array($id));
    return $wpdb->get_row($sql, ARRAY_A);
}


/**
 * Lists all contributions aggregated to a given contrib
 *
 * All contributions marked as dupplicated or as part of the `$parent'
 * contrib are going to be listed here.
 */
function wpgp_db_govr_get_aggregated_contribs($parent) {
    global $wpdb;

    $sql = "SELECT contrib.*
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." cchild
            WHERE
                 contrib.parent = %d OR
                 (contrib.id = cchild.children_id AND
                  cchild.inverse_id = %d)";
    return $wpdb->get_results(
        $wpdb->prepare($sql, array($parent, $parent)),
        ARRAY_A);
}


/**
 * Returns true if a contrib is aggregated to another one
 */
function wpgp_db_govr_contrib_is_aggregated($contrib) {
    global $wpdb;
    $sql = "SELECT count(contrib.id)
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." cchild
            WHERE
                 contrib.id = %d AND
                 contrib.parent = 0 AND
                 contrib.id <> cchild.children_id";
    return $wpdb->get_var($wpdb->prepare($sql, array($contrib))) == 0;
}


/**
 * Returns a bool value indicating if an user can vote in a contrib
 *
 * This is needed because an user can't vote in the same contrib more
 * than once. So we store each vote made by a user and test it before
 * inserting a new vote.
 */
function wpgp_db_govr_contrib_user_can_vote($contrib, $user) {
    global $wpdb;
    $sql = "SELECT count(user_id) FROM " . WPGP_GOVR_USER_VOTES . "
            WHERE contrib_id = %d AND user_id = %d";
    return $wpdb->get_var($wpdb->prepare($sql, array($contrib, $user))) == 0;
}


/**
 * Increments a new vote to the score field of a contrib
 *
 * This field does not care about aggregated contribs, once it receives
 * an Id, it's score will be incremented plus one.
 */
function wpgp_db_govr_contrib_vote($contrib, $user) {
    global $wpdb;

    if (!wpgp_db_govr_contrib_user_can_vote($contrib, $user))
        return false;

    $sql = "UPDATE ".WPGP_GOVR_CONTRIB_TABLE."
            SET score = score + 1
            WHERE id = %d";
    $wpdb->query($wpdb->prepare($sql, array($contrib)));
    $wpdb->insert(WPGP_GOVR_USER_VOTES,
                  array("contrib_id" => $contrib,
                        "user_id" => $user));
    return wpgp_db_govr_get_contrib_score($contrib);
}


/**
 * Returns the score of a contrib and all its aggregations
 *
 * Instead of just returning the value of the `contrib.score' field,
 * this function finds all aggregated contribs (based on the `$contrib'
 * param) and sum all of its score fields.
 */
function wpgp_db_govr_get_contrib_score($contrib) {
    global $wpdb;
    $sql = "SELECT SUM(contrib.score)
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." cchild
            WHERE
                 contrib.id = %d OR
                 contrib.parent = %d OR
                 (contrib.id = cchild.children_id AND
                  cchild.inverse_id = %d)";
    return $wpdb->get_var(
        $wpdb->prepare($sql, array($contrib, $contrib, $contrib)));
}


function wpgp_db_govr_get_contribs_count_by_theme($theme_id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM ".WPGP_GOVR_CONTRIB_TABLE."
                           WHERE deleted=0 AND theme_id=%d",array($theme_id));
    return $wpdb->get_var($sql);
}


function wpgp_db_govr_create_contrib($title
                                     , $theme_id
                                     , $content
                                     , $user_id
                                     , $part
                                     , $moderation = 1
                                     , $parent = 0) {

    global $wpdb;
    $sql = $wpdb->prepare("
		INSERT INTO ".WPGP_GOVR_CONTRIB_TABLE."
		( title, theme_id, content, user_id, original, created_at,
                  status, parent, created_by_moderation )
		VALUES ( %s, %d, %s, %d, %s, now(), 'pending', %d, %d )",
                          array($title, $theme_id, $content, $user_id,
                                $content, $parent, $moderation));

    $ret = $wpdb->query($sql);
    if (strlen(trim($part)) > 0) {
        $contrib = wpgp_db_govr_get_contrib($wpdb->insert_id);
        wpgp_govr_contrib_insert_parts($contrib, $part);
    }
}

function wpgp_db_govr_delete_contrib($id, $hard = false) {
    global $wpdb;
    if ($hard) {
        $wpdb->query($wpdb->prepare("DELETE FROM ".WPGP_GOVR_CONTRIB_TABLE."
                                     WHERE id=%d", array($id)));
    } else {
        $wpdb->update(WPGP_GOVR_CONTRIB_TABLE,
                      array('deleted' => 1),
                      array('id' => $id));
    }
    // reset parent
    $wpdb->update(WPGP_GOVR_CONTRIB_TABLE,
                  array('parent' => 0),
                  array('parent' => $id));

    // reset parts
    die($wpdb->update(WPGP_GOVR_CONTRIB_TABLE,
                      array('part' => 0),
                      array('part' => $id)));
}


function wpgp_db_govr_update_contrib($id, $field, $value) {
    global $wpdb;
    return $wpdb->update(WPGP_GOVR_CONTRIB_TABLE,
                         array($field => $value),
                         array('id' => $id));
}


function wpgp_govr_contrib_get_parents($contrib) {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                           ".WPGP_GOVR_CONTRIBC_TABLE." children
      WHERE
        children.children_id = ${contrib[id]} AND
        children.inverse_id = contrib.id";
    return $wpdb->get_results($sql, ARRAY_A);
}

function wpgp_govr_contrib_remove_part($contrib, $child) {
    global $wpdb;
    $sql = "DELETE FROM ".WPGP_GOVR_CONTRIBC_TABLE." WHERE
      inverse_id = ${contrib[id]} AND
      children_id = ${child[id]};";
    $wpdb->query($sql);
}

function wpgp_govr_contrib_remove_all_parts($contrib) {
    global $wpdb;
    $sql = "DELETE FROM ".WPGP_GOVR_CONTRIBC_TABLE." WHERE
      inverse_id = ${contrib[id]};";
    $wpdb->query($wpdb->prepare($sql));
}

function wpgp_govr_contrib_append_part($contrib, $child) {
    global $wpdb;
    $wpdb->insert(
                  WPGP_GOVR_CONTRIBC_TABLE,
                  array("inverse_id"  => $contrib['id'],
                        "children_id" => $child['id'])
                  );
}

function wpgp_govr_contrib_has_duplicates($contrib) {
    global $wpdb;

    if ($contrib['parent'] > 0) return true;

    $sql = "SELECT COUNT(*)
            FROM ".WPGP_GOVR_CONTRIB_TABLE."
            WHERE parent=%d AND deleted=0";
    return $wpdb->get_var($wpdb->prepare($sql, $contrib['id'])) > 0;
}

function wpgp_govr_contrib_get_duplicates($contrib) {
    global $wpdb;
    $sql = "SELECT * FROM ".WPGP_GOVR_CONTRIB_TABLE."
            WHERE parent=%d AND deleted=0";
    return $wpdb->get_results($wpdb->prepare($sql, $contrib['id']), ARRAY_A);
}

function wpgp_govr_contrib_has_children($contrib) {
    global $wpdb;
    $sql = "SELECT count(*)
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." ch
      WHERE
        ch.inverse_id = ${contrib[id]} AND
        ch.children_id = contrib.id";
    return $wpdb->get_var($wpdb->prepare($sql)) > 0;
}

function wpgp_govr_contrib_get_children($contrib) {
    global $wpdb;
    $sql = "SELECT *
            FROM ".WPGP_GOVR_CONTRIB_TABLE." contrib,
                 ".WPGP_GOVR_CONTRIBC_TABLE." ch
      WHERE
        ch.inverse_id = ${contrib[id]} AND
        ch.children_id = contrib.id";
    return $wpdb->get_results($sql, ARRAY_A);
}


function wpgp_govr_contrib_insert_parts($org,$parts) {
    $pids = explode(" ", $parts);

    /* FIXME: avoid calling _get_contrib() twice for each
     * contrib in this function */
    foreach ($pids as $pid) {
        $parent = wpgp_db_govr_get_contrib($pid);
        if ($parent == null) {
            return false;
        }
    }

    /* Removing all parts previously added */
    wpgp_govr_contrib_remove_all_parts($org);

    /* Now we're sure that everything's good, so we can insert
     * the new parts. */
    foreach ($pids as $pid) {
        $parent = wpgp_db_govr_get_contrib($pid);
        wpgp_govr_contrib_append_part($parent, $org);
    }
    return true;
}

function wpgp_db_govr_contribs_scores($theme_id,
                                      $page,
                                      $from,
                                      $to,
                                      $perpage = WPGP_CONTRIBS_PER_PAGE) {
    global $wpdb;
    $offset = $page * $perpage;

    $fromto = '';
    if($from && $to) {
        $from = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$from);
        $to = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$to);
        $fromto = " AND (DATE(contrib.created_at) > DATE($from)
                 AND DATE(contrib.created_at) < DATE($to)) ";
    }

    $sql_base = $wpdb->prepare("
      FROM
          ".WPGP_GOVR_CONTRIB_TABLE." contrib, wp_users user
      WHERE
          (contrib.theme_id=%d
           AND contrib.user_id=user.ID
           AND contrib.deleted=0
           $fromto)
      ORDER BY score DESC", array($theme_id));

    $sql = "SELECT contrib.*,
          user.display_name as display_name" . $sql_base;

    $sql = $wpdb->prepare($sql
                          ." LIMIT %d, %d",array($offset,$perpage));
    $listing = $wpdb->get_results($sql, ARRAY_A);

    $sql = $wpdb->prepare("SELECT COUNT(*) $sql_base");
    $count = $wpdb->get_var($sql);

    $sql = $wpdb->prepare("SELECT SUM(contrib.score) $sql_base");
    $votes = $wpdb->get_var($sql);
    return array($listing, $count, $votes);
}


function wpgp_db_govr_contrib_count_grouped_by_date($theme_id) {
    global $wpdb;
    $sql = $wpdb->prepare(
                          "SELECT
        year(c.created_at) AS year,
        month(c.created_at) AS month,
        day(c.created_at) AS day,
        date(c.created_at) AS date,
        count(c.id) AS count
      FROM ".WPGP_GOVR_CONTRIB_TABLE." AS c
      WHERE c.theme_id=%d
      GROUP BY DATE(c.created_at);", array($theme_id));
    return $wpdb->get_results($sql, ARRAY_A);
}

function wpgp_db_govr_get_summary($from, $to) {

    $fromto = '';
    if($from && $to) {
        $from = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$from);
        $to = preg_replace('/(\d+)\/(\d+)\/(\d+)/','\'${3}-${2}-${1}\'',$to);
        $fromto = " AND (DATE(created_at) > DATE($from)
                 AND DATE(created_at) < DATE($to)) ";
    }

    global $wpdb;
    $sql = "SELECT
             COUNT(*) as total_contribs,
             SUM(score) as total_votes
            FROM wpgp_govr_contribs
            WHERE 1=1 $fromto";
    return $wpdb->get_row($sql, ARRAY_A);
}
?>
