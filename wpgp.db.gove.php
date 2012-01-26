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


/**
 * Create a new audience in the database
 */
function wpgp_db_gove_audience_new($title,
                                   $subject,
                                   $description,
                                   $date,
                                   $visible,
                                   $data) {
    global $wpdb;
    $wpdb->insert(WPGP_GOVE_AUDIENCE_TABLE,
                  array("title"        => $title,
                        "subject"      => $subject,
                        "description"  => $description,
                        "date"         => $date,
                        "visible"      => $visible,
                        "data"         => $data,
                        "created_at"   => date("Y-m-d H:i:s")));
}


function wpgp_db_gove_audience_edit($id,
                                    $title,
                                    $subject,
                                    $description,
                                    $date,
                                    $visible,
                                    $data) {
    global $wpdb;
    $wpdb->insert(WPGP_GOVE_AUDIENCE_TABLE,
                  array("title"        => $title,
                        "subject"      => $subject,
                        "description"  => $description,
                        "date"         => $date,
                        "visible"      => $visible,
                        "data"         => $data),
                  array("id"           => $id));
}


function wpgp_db_gove_audience_list($sortby='id',
                                    $search='',
                                    $page=0,
                                    $perpage=WPGP_RESULTS_PER_PAGE) {
    global $wpdb;

    /* Deciding the ASC/DESC direction depending on the first char of
     * the $sortby var. If it starts with `-', it will be DESC. */
    $direction = 'ASC';
    if ($sortby[0] === '-') {
        $direction = 'DESC';
        $sortby = substr($sortby, 1, strlen($sortby));
    }
    $sortby = "ORDER BY audience.$sortby $direction";

    /* Text search -- let's try to find a string in all text fields we
     * have in the audiences table: title, subject and description. */
    $textsearch = 'true';
    if (!empty($search)) {
        $textsearch =
            "(title       LIKE '%$search% OR " .
            " subject     LIKE '%$search% OR " .
            " description LIKE '%$search%)";
    }

    /* The query itself, we just concatenate all details of the query
     * built above. */
    $offset = $page * $perpage;
    $sql = "SELECT audience.* " .
        " FROM " . WPGP_GOVE_AUDIENCE_TABLE .
        " audience ";
    $sql = $wpdb->prepare(
        "$sql WHERE $textsearch $sortby LIMIT %d, %d",
        array($offset, $perpage));
    $listing = $wpdb->get_results($sql, ARRAY_A);

    /* We also need to know the number of contribs we have in the table
     * to build the pagination. */
    $count = $wpdb->get_var(
        "SELECT count(id) FROM " . WPGP_GOVE_AUDIENCE_TABLE);
    return array($listing, $count);
}


function wpgp_db_gove_audience_remove() {
}

?>
