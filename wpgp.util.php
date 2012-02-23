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
 * Small hammer to convert a date string into an us formatted
 * date. Currently, the input format is the brazillian one d/m/YY, but
 * if you want help to improve it, please make a configurable system and
 * help us to change all hardcoded dates in the code :)
 */
function wpgp__date_to_us($input) {
    return preg_replace('/(\d+)\/(\d+)\/(\d+)/','${3}-${2}-${1}', $input);
}


/**
 * Does exactly the contrary of the above function. Transforms a date
 * string from the us format to the "current one".
 */
function wpgp__date_from_us($input) {
    return preg_replace('/(\d+)\-(\d+)\-(\d+).*/','${3}/${2}/${1}', $input);
}


/**
 * Govr Helper function to get the proper css class of a contribution row
 */
function wpgp__govr_get_class($contrib) {
    $klass = array();
    if ($contrib['parent'] > 0) {
        array_push($klass, "is-duplicated");
        array_push($klass, "duplication-of-${contrib[parent]}");
    } else if (wpgp_govr_contrib_get_parents($contrib)){
        array_push($klass, "wpgp-part");
    } else {
        if ($contrib['status'] == 'approved') {
            array_push($klass, "wpgp-approved");
        } else {
            array_push($klass, "wpgp-disapproved");
        }
    }
    return join(" ", $klass);
}

function wpgp__govr_get_parents_string($contrib) {
    $parents = array();
    foreach (wpgp_govr_contrib_get_parents($contrib) as $c) {
        array_push($parents, $c['id']);
    }
    return join(" ", $parents);
}

function wpgp__govr_get_part_string($contrib) {
    $parts = array();
    foreach (wpgp_govr_contrib_get_children($contrib) as $c) {
        array_push($parts, $c['id']);
    }
    return join(" ", $parts);
}



/**
 * Govp Helper function to get the proper css class of a contribution row
 */
function wpgp__govp_get_class($contrib) {
    $klass = array();
    if ($contrib['parent'] > 0) {
        array_push($klass, "is-duplicated");
        array_push($klass, "duplication-of-${contrib[parent]}");
    } else if (wpgp_govp_contrib_get_parents($contrib)){
        array_push($klass, "wpgp-part");
    } else {
        if ($contrib['status'] == 'approved') {
            array_push($klass, "wpgp-approved");
        } else {
            array_push($klass, "wpgp-disapproved");
        }
    }
    return join(" ", $klass);
}

function wpgp__govp_get_parents_string($contrib) {
    $parents = array();
    foreach (wpgp_govp_contrib_get_parents($contrib) as $c) {
        array_push($parents, $c['id']);
    }
    return join(" ", $parents);
}

function wpgp__govp_get_part_string($contrib) {
    $parts = array();
    foreach (wpgp_govr_contrib_get_children($contrib) as $c) {
        array_push($parts, $c['id']);
    }
    return join(" ", $parts);
}
?>