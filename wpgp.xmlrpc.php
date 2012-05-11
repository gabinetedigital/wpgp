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
    return wpgp_db_govr_get_themes($args[1]);
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


function wpgp_govr_getContrib($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_contrib($args[1], $args[2]);
}


function wpgp_govr_contribIsAggregated($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_contrib_is_aggregated($args[1]);
}


function wpgp_govr_getContribs($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_contribs(
        $args[1], // theme_id
        $args[2], // user_id
        $args[3], // page
        $args[4], // sortby
        $args[5], // from
        $args[6], // to
        $args[7], // status
        $args[8], // filter
        $args[9] ? $args[8] : WPGP_RESULTS_PER_PAGE, // perpage
		$args[10]
    );
}


function wpgp_govr_getVotingContribs($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_voting_contribs(
        $args[1], // theme_id
        $args[2], // user_id
        $args[3], // page
        $args[4], // sortby
        $args[5], // from
        $args[6], // to
        $args[7] ? $args[7] : WPGP_RESULTS_PER_PAGE // perpage
    );
}


function wpgp_govr_getAggregatedContribs($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_aggregated_contribs($args[1]);
}


function wpgp_govr_contribUserCanVote($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_contrib_user_can_vote($args[1], $args[2]);
}


function wpgp_govr_contribVote($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_contrib_vote($args[1], $args[2]);
}


function wpgp_govr_getContribScore($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_contrib_score($args[1]);
}


function wpgp_govr_getUserStats($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_govr_get_user_stats($args[1]);
}


function wpgp_gove_getAudiences($args) {
    if (!is_array($args = _exapi_method_header($args))) {
        return $args;
    }
    return wpgp_db_gove_audience_list(
        $args[1], /* sortby */
        $args[2], /* search */
        $args[3], /* filter */
        $args[4], /* page */
        $args[5] ? $args[5] : WPGP_RESULTS_PER_PAGE /* perpage */
    );
}


/* Filter that registers our methods in the wordpress xmlrpc provider */
add_filter('xmlrpc_methods', function ($methods) {
    $methods['govr.getTheme'] = 'wpgp_govr_getTheme';
    $methods['govr.getThemes'] = 'wpgp_govr_getThemes';
    $methods['govr.createContrib'] = 'wpgp_govr_createContrib';
    $methods['govr.getContrib'] = 'wpgp_govr_getContrib';
    $methods['govr.getContribs'] = 'wpgp_govr_getContribs';
    $methods['govr.getVotingContribs'] = 'wpgp_govr_getVotingContribs';
    $methods['govr.getAggregatedContribs'] = 'wpgp_govr_getAggregatedContribs';
    $methods['govr.contribVote'] = 'wpgp_govr_contribVote';
    $methods['govr.contribUserCanVote'] = 'wpgp_govr_contribUserCanVote';
    $methods['govr.getContribScore'] = 'wpgp_govr_getContribScore';
    $methods['govr.contribIsAggregated'] = 'wpgp_govr_contribIsAggregated';
    $methods['govr.getUserStats'] = 'wpgp_govr_getUserStats';

    $methods['gove.getAudiences'] = 'wpgp_gove_getAudiences';
    return $methods;
});

?>
