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

function wpgp_show_gov_responde() {

    function show_themes() {
        $renderer = wpgp_renderer();
        $ctx = array();
        $ctx['listing'] = wpgp_db_govr_get_themes();
        echo $renderer->render('admin/gov_responde_main.html', $ctx);
    }

    function show_contributions() {
        $renderer = wpgp_renderer();
        $ctx = array();
        $page = (int) (isset($_GET["paged"]) ? $_GET["paged"] : '1');

        list($ctx['listing'],$ctx['count'])
            = wpgp_db_govr_get_theme_contribs($_GET['theme_id']
                                              , $page-1
                                              , $_GET["sort"]
                                              , $_GET['from']
                                              , $_GET['to']
                                              , $_GET['status']
                                              );

        $ctx['theme'] = wpgp_db_govr_get_theme($_GET['theme_id']);
        $ctx['themes'] = wpgp_db_govr_get_themes();
        $ctx['s'] = $_GET['s'];
        $ctx['from'] = $_GET['from'];
        $ctx['to'] = $_GET['to'];
        $ctx['status'] = $_GET['status'];
        $ctx['total_count'] = wpgp_db_govr_get_contrib_count();
        $ctx['siteurl'] = get_bloginfo('siteurl');
        $ctx['sortby'] = get_query_var("sort");
        $ctx['paged'] =  $page;
        $ctx['numpages'] = ceil($ctx['count'] / WPGP_CONTRIBS_PER_PAGE);
        $ctx['perpage'] = WPGP_CONTRIBS_PER_PAGE;
        $ctx['pageurl'] = remove_query_arg("sort");
        $ctx['pageurl'] = remove_query_arg("paged");
        echo $renderer->render('admin/gov_responde_contribs.html', $ctx);
    }

    function show_scores() {
        $renderer = wpgp_renderer();
        $page = (int) (isset($_GET["paged"]) ? $_GET["paged"] : '1');

        $ctx = array();

        $ctx['theme'] = wpgp_db_govr_get_theme($_GET['theme_id']);

        list($ctx['listing'], $ctx['count'], $ctx['votes']) =
            wpgp_db_govr_contribs_scores($_GET['theme_id'], $page-1);

        $ctx['siteurl'] = get_bloginfo('siteurl');
        $ctx['paged'] =  $page;
        $ctx['numpages'] = ceil($ctx['count'] / WPGP_CONTRIBS_PER_PAGE);
        $ctx['perpage'] = WPGP_CONTRIBS_PER_PAGE;
        $ctx['pageurl'] = remove_query_arg("paged");
        echo $renderer->render('admin/gov_responde_scores.html', $ctx);
    }

    function show_stats() {
        $renderer = wpgp_renderer();
        $ctx = array();

        $theme_id = $_GET['theme_id'];
        $ctx['theme'] = wpgp_db_govr_get_theme($theme_id);

        $st = wpgp_db_govr_contrib_count_grouped_by_date($theme_id);
        $ctx['chart_byday'] = json_encode($st);

        echo $renderer->render('admin/gov_responde_stats.html', $ctx);
    }

    switch($_GET['subpage']) {
    case 'contributions':
        show_contributions();
        break;
    case 'scores':
        show_scores();
        break;
    case 'stats':
        show_stats();
        break;
    default:
        show_themes();
        break;
    }
}


function wpgp_show_gov_pergunta() {
    function show_sessions() {
        $renderer = wpgp_renderer();
        $ctx = array();
        $ctx['listing'] = wpgp_db_govp_get_sessions();
        echo $renderer->render('admin/gov_pergunta_main.html', $ctx);
    }

    function show_session_configuration() {
        $renderer = wpgp_renderer();
        $ctx = array();
        $ctx['session'] = wpgp_db_govp_get_session($_GET['session_id']);
        $ctx['themes'] = wpgp_db_govp_get_themes($_GET['session_id']);
        echo $renderer->render('admin/gov_pergunta_config.html', $ctx);
    }

    function show_contributions() {
        $session_id = $_GET['session_id'];

        $renderer = wpgp_renderer();
        $ctx = array();
        $page = (int) (isset($_GET["paged"]) ? $_GET["paged"] : '1');

        list($ctx['listing'],$ctx['count'])
            = wpgp_db_govp_get_session_contribs($session_id,
                                                $page-1,
                                                $_GET['sort'],
                                                $_GET['theme_id'],
                                                $_GET['status']);

        $ctx['theme_id'] = $_GET['theme_id'];
        $ctx['themecounts'] = wpgp_db_get_theme_counts($session_id);
        $ctx['themes'] = wpgp_db_govp_get_themes($session_id);
        $ctx['session'] = wpgp_db_govp_get_session($session_id);
        $ctx['s'] = $_GET['s'];
        $ctx['status'] = $_GET['status'];
        $ctx['total_count'] = wpgp_db_govp_get_contrib_count($session_id);
        $ctx['siteurl'] = get_bloginfo('siteurl');
        $ctx['sortby'] = get_query_var("sort");
        $ctx['paged'] =  $page;
        $ctx['numpages'] = ceil($ctx['count'] / WPGP_CONTRIBS_PER_PAGE);
        $ctx['perpage'] = WPGP_CONTRIBS_PER_PAGE;
        $ctx['pageurl'] = remove_query_arg("sort");
        $ctx['pageurl'] = remove_query_arg("paged");
        echo $renderer->render('admin/gov_pergunta_contribs.html', $ctx);
    }

    function show_total_scores() {
        $renderer = wpgp_renderer();
        $page = (int) (isset($_GET["paged"]) ? $_GET["paged"] : '1');

        $ctx = array();

        $ctx['session'] = wpgp_db_govp_get_session($_GET['session_id']);

        list($ctx['listing'], $ctx['count']) =
            wpgp_db_govp_contribs_scores($_GET['session_id'], $page-1);

        $ctx['siteurl'] = get_bloginfo('siteurl');
        $ctx['paged'] =  $page;
        $ctx['numpages'] = ceil($ctx['count'] / WPGP_CONTRIBS_PER_PAGE);
        $ctx['perpage'] = WPGP_CONTRIBS_PER_PAGE;
        $ctx['pageurl'] = remove_query_arg("paged");
        echo $renderer->render('admin/gov_pergunta_scores.html', $ctx);
    }

    function show_theme_list_score() {
        $renderer = wpgp_renderer();
        $ctx = array();
        $ctx['listing'] = wpgp_db_govp_get_themes($_GET['session_id']);
        echo $renderer->render('admin/gov_pergunta_theme_list_score.html',
                               $ctx);
    }

    function show_contrib_scores() {
        $renderer = wpgp_renderer();
        $page = (int) (isset($_GET["paged"]) ? $_GET["paged"] : '1');

        $ctx = array();

        $ctx['theme'] = wpgp_db_govp_get_session($_GET['theme_id']);

        $ctx['session'] =
            wpgp_db_govp_get_session($ctx['theme']['session_id']);

        list($ctx['listing'], $ctx['count']) =
            wpgp_db_govp_contribs_theme_scores($_GET['theme_id'],
                                               $page-1);

        $ctx['siteurl'] = get_bloginfo('siteurl');
        $ctx['paged'] =  $page;
        $ctx['numpages'] = ceil($ctx['count'] / WPGP_CONTRIBS_PER_PAGE);
        $ctx['perpage'] = WPGP_CONTRIBS_PER_PAGE;
        $ctx['pageurl'] = remove_query_arg("paged");
        echo $renderer->render('admin/gov_pergunta_theme_scores.html',
                               $ctx);
    }

    function show_stats() {
        $renderer = wpgp_renderer();
        $ctx = array();

        $sid = $_GET['session_id'];
        $ctx['session'] = wpgp_db_govp_get_session($sid);

        $ctx['chart_byday'] =
            json_encode(wpgp_db_govp_contrib_count_grouped_by_date($sid));

        $ctx['chart_bytheme'] =
            json_encode(wpgp_db_govp_contrib_count_grouped_by_theme($sid));

        $ctx['chart_bythemedate'] =
            json_encode(wpgp_db_govp_contrib_count_grouped_by_themedate($sid));
        /* $ctx['chart_votebyday'] = */
        /*     json_encode(wpgd_db_get_vote_count_grouped_by_date($sid)); */

        echo $renderer->render('admin/gov_pergunta_stats.html', $ctx);
    }

    switch($_GET['subpage']) {
    case 'contributions':
        show_contributions();
        break;
    case 'session_config':
        show_session_configuration();
        break;
    case 'total_scores':
        show_total_scores();
        break;
    case 'theme_list_score':
        show_theme_list_score();
        break;
    case 'theme_contrib_score':
        show_contrib_scores();
        break;
    case 'stats':
        show_stats();
        break;
    default:
        show_sessions();
    }
}


function wpgp_admin_menu() {
    $rootslug = __FILE__;

    add_menu_page('Gabinete Digital', 'Gabinete Digital',
                  'administrator',  $rootslug);

    $govp = add_submenu_page($rootslug, 'Governador Pergunta',
                     'Governador Pergunta', 'administrator',
                     $rootslug,'wpgp_show_gov_pergunta');

    $govr = add_submenu_page($rootslug, 'Governador Responde',
                     'Governador Responde', 'administrator',
                     "gov-responde","wpgp_show_gov_responde");


    /* Loading javascript */
    add_action('admin_enqueue_scripts', function($hooksufix) use ($govp,$govr){
            wp_enqueue_script('jquery-param',
                              plugins_url("static/js/util.js",
                                              __FILE__));

            wp_enqueue_script(
                'flot',
                plugins_url('static/js/jquery.flot.min.js', __FILE__));

            wp_enqueue_script(
                'flot-pie',
                plugins_url('static/js/jquery.flot.pie.js', __FILE__));

            $sufix = $_GET['subpage'] ? '-'.$_GET['subpage'] : '-main';
            switch ($hooksufix) {
            case $govp:
                wp_enqueue_script('wpgp-govp',
                                  plugins_url("static/js/govp${sufix}.js",
                                              __FILE__));
                wp_enqueue_style(
                                 'wpgp-govr-css',
                                 plugins_url('static/css/govp.css', __FILE__));
                break;
            case $govr:
                wp_enqueue_script(
                                  'wpgp-govr',
                                  plugins_url("static/js/govr${sufix}.js",
                                              __FILE__));
                wp_enqueue_style(
                                 'wpgp-govr-css',
                                 plugins_url('static/css/govr.css', __FILE__));
                break;
            }
        });
}

add_action('admin_menu', 'wpgp_admin_menu');
?>
