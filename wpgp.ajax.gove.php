<?php /* -*- Mode: php; c-basic-offset:4; -*- */
/* Copyright (C) 2012  Governo do Estado do Rio Grande do Sul
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

function wpgp_ajax_gove_audience_new() {
    wpgp_db_gove_audience_new(
        $_POST['data']['title'],
        $_POST['data']['subject'],
        $_POST['data']['description'],
        $_POST['data']['date'],
        $_POST['data']['visible'] === 'checked',
        $_POST['data']['data'],
        date("Y-m-d H:i:s")
    );
    die('ok');
}


function wpgp_ajax_gove_audience_edit() {
    wpgp_db_gove_audience_edit(
        $_POST['data']['id'],
        $_POST['data']['title'],
        $_POST['data']['subject'],
        $_POST['data']['description'],
        $_POST['data']['date'],
        $_POST['data']['visible'] === 'checked',
        $_POST['data']['data']
    );
    die('ok');
}


function wpgp_ajax_gove_audience_remove() {
    wpgp_db_gove_audience_remove(
        $_POST['data']['id']
    );
    die('ok');
}


add_action('wp_ajax_gove_audience_new', 'wpgp_ajax_gove_audience_new');
add_action('wp_ajax_gove_audience_edit', 'wpgp_ajax_gove_audience_edit');
add_action('wp_ajax_gove_audience_remove', 'wpgp_ajax_gove_audience_remove');

?>
