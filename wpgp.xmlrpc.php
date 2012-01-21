<?php /* -*- Mode: php; c-basic-offset:4; -*- */
/* Copyright (C) 2012  Governo do Estado do Rio Grande do Sul
 *
 *   Author: Lincoln de Sousa <lincoln@gg.rs.gov.br>
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

function wpgp_govr_getThemes($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_themes();
}


function wpgp_govr_createContrib($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }

    /* Reading args */
    return wpgp_db_govr_create_contrib(
        $args[1], // title
        $args[2], // themeid
        $args[3], // content
        $args[4], // userid
        $args[5], // part
        $args[6], // moderation
        $args[7]  // parent
    );
}


function wpgp_govr_getTheme($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_theme($args[1]);
}


function wpgp_govr_getContribs($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_contribs(
        $args[1], // theme_id
        $args[2], // page
        $args[3], // sortby
        $args[4], // from
        $args[5], // to
        $args[6], // status
        $args[7], // filter
        $args[8]  // perpage
    );
}


/* Filter that registers our methods in the wordpress xmlrpc provider */
add_filter('xmlrpc_methods', function ($methods) {
    $methods['govr.getTheme'] = 'wpgp_govr_getTheme';
    $methods['govr.getThemes'] = 'wpgp_govr_getThemes';
    $methods['govr.createContrib'] = 'wpgp_govr_createContrib';
    $methods['govr.getContribs'] = 'wpgp_govr_getContribs';
    return $methods;
});

?>
