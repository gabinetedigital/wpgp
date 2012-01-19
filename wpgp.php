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

/*
Plugin Name: WpGP
Plugin URI: http://trac.gabinetedigital.rs.gov.br
Description: Wordpress Government Participation Plugin
Version: 0.1.0
Author URI: http://gabinetedigital.rs.gov.br
License: AGPL3
*/

define('WPGP_GOVR_THEME_TABLE','wpgp_govr_themes');
define('WPGP_GOVR_CONTRIB_TABLE','wpgp_govr_contribs');
define('WPGP_GOVR_CONTRIBC_TABLE', 'wpgp_govr_contrib_children');
define('WPGP_GOVP_SESSION_TABLE','wpgp_govp_sessions');
define('WPGP_GOVP_CONTRIB_TABLE','wpgp_govp_contribs');
define('WPGP_GOVP_CONTRIBC_TABLE', 'wpgp_govp_contrib_children');
define('WPGP_GOVP_THEME_TABLE','wpgp_govp_themes');

define('WPGP_CONTRIBS_PER_PAGE', 50);

include_once('wpgp.templating.php');
include_once('wpgp.util.php');
include_once('wpgp.db.govr.php');
include_once('wpgp.db.govp.php');
include_once('wpgp.admin.panel.php');
include_once('wpgp.ajax.govr.php');
include_once('wpgp.ajax.govp.php');
include_once('wpgp.xmlrpc.php');

function wpgp_install() {
    add_role('wpgp_moderator', 'Moderador',
             array('read' => true,
                   'moderate_contrib' => true));

    $role_object = get_role('administrator');
    $role_object->add_cap('moderate_contrib');

    global $wpdb;
    $sql = "CREATE TABLE " . WPGP_GOVR_THEME_TABLE . " (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(1000) NOT NULL,
      created_at DATETIME,
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE " . WPGP_GOVR_CONTRIB_TABLE . " (
      id int NOT NULL AUTO_INCREMENT,
      title varchar(1000) NOT NULL,
      theme_id int NOT NULL,
      content text NOT NULL,
      user_id int(11) NOT NULL,
      original text,
      created_at datetime,
      status enum('pending','blocked','approved','responded') default 'pending',
      deleted int default 0,
      parent int default 0,
      created_by_moderation int default 0,
      score float default 0,
      resposta text,
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE ". WPGP_GOVR_CONTRIBC_TABLE. " (
      inverse_id int not null,
      children_id int not null,
      PRIMARY KEY (inverse_id,children_id),
      KEY contrib_children_inverse_fk (children_id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE ". WPGP_GOVP_SESSION_TABLE . " (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(1000) NOT NULL,
      active int default 0,
      created_at DATETIME,
      pairwise_url varchar(1000),
      pairwise_db_host varchar(1000),
      pairwise_db_name varchar(1000),
      pairwise_db_user varchar(1000),
      pairwise_db_pass varchar(1000),
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE " . WPGP_GOVP_THEME_TABLE . " (
      id int NOT NULL AUTO_INCREMENT,
      session_id int NOT NULL,
      name varchar(1000) NOT NULL,
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE " . WPGP_GOVP_CONTRIB_TABLE . " (
      id int NOT NULL AUTO_INCREMENT,
      title varchar(1000) NOT NULL,
      theme_id int NOT NULL,
      content text NOT NULL,
      user_id int(11) NOT NULL,
      original text,
      created_at datetime,
      status enum('pending','approved') default 'pending',
      deleted int default 0,
      parent int default 0,
      created_by_moderation int default 0,
      score float default 0,
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    CREATE TABLE ". WPGP_GOVP_CONTRIBC_TABLE. " (
      inverse_id int not null,
      children_id int not null,
      PRIMARY KEY (inverse_id,children_id),
      KEY contrib_children_inverse_fk (children_id)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    error_log($sql);
    $wpdb->query ($sql);
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function wpgp_uninstall() {
    remove_role('wpgp_moderator');
    $role_object = get_role('administrator');
    $role_object->remove_cap('moderate_contrib');

    /* //...really, don't drop it */
    /* global $wpdb; */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVR_THEME_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVR_CONTRIB_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVP_SESSION_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVP_CONTRIB_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVP_THEME_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVR_CONTRIBC_TABLE); */
    /* $wpdb->query ("DROP TABLE IF EXISTS ".WPGP_GOVP_CONTRIBC_TABLE); */
}

register_activation_hook(__FILE__, 'wpgp_install');
register_deactivation_hook(__FILE__, 'wpgp_uninstall');

?>
