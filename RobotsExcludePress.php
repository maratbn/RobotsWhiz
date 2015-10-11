<?php
/*
  Plugin Name: RobotsExcludePress
  Plugin URI: https://github.com/maratbn/RobotsExcludePress
  Description: An easy way to discourage search engines from indexing only specific pages / posts.
  Author: Marat Nepomnyashy
  Author URI: http://www.maratbn.com
  License: GPL3
  Version: 0.1.0-development_unreleased
  Text Domain: domain-plugin-RobotsExcludePress
*/

/*
  RobotsExcludePress -- WordPress plugin that allows site administrators to
                        discourage search engines from indexing, following
                        links from, caching, indexing images from, and / or
                        have additional custom restrictions on only for certain
                        specifically-designated pages and posts.

                        It is an easy way to discourage search engines from
                        indexing only specific pages / posts.

                        This only applies to search engine robots, and will
                        not effect the site's local search functionality.

  https://github.com/maratbn/RobotsExcludePress

  Copyright (C) 2015  Marat Nepomnyashy  http://maratbn.com  maratbn@gmail

  Version:        0.1.0-development_unreleased

  Module:         RobotsExcludePress.php

  Description:    Main PHP file for the WordPress plugin 'RobotsExcludePress'.

  This file is part of RobotsExcludePress.

  Licensed under the GNU General Public License Version 3.

  RobotsExcludePress is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  RobotsExcludePress is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with RobotsExcludePress.  If not, see <http://www.gnu.org/licenses/>.
*/

    namespace plugin_RobotsExcludePress;


    const ROBOTS_EXCLUDE_PRESS    = 'robots_exclude_press';

    add_action('admin_menu', '\\plugin_RobotsExcludePress\\action_admin_menu');
    add_action('admin_post_plugin_RobotsExcludePress_settings',
               '\\plugin_RobotsExcludePress\\action_admin_post_plugin_RobotsExcludePress_settings');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__),
                                        '\\plugin_RobotsExcludePress\\filter_plugin_action_links');


    function action_admin_menu() {
        add_options_page( __('RobotsExcludePress Settings', 'domain-plugin-RobotsExcludePress'),
                          __('RobotsExcludePress', 'domain-plugin-RobotsExcludePress'),
                          'manage_options',
                          'plugin_RobotsExcludePress_settings',
                          '\\plugin_RobotsExcludePress\\render_settings');
    }

    function action_admin_post_plugin_RobotsExcludePress_settings() {
        //  Based on: http://jaskokoyn.com/2013/03/26/wordpress-admin-forms/
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient user permissions to modify options.',
                      'domain-plugin-RobotsExcludePress'));
        }

        // Check that nonce field
        check_admin_referer('plugin_RobotsExcludePress_settings_nonce');

        foreach ($_POST as $strFieldName => $strFieldValue) {
            preg_match('/^post_(\d+)$/', $strFieldName, $arrMatch);
            if ($arrMatch && count($arrMatch) == 2) {
                $idPost = $arrMatch[1];
                $flagIsLocked = isset($_POST['data_' . $idPost]);
                if ($flagIsLocked) {
                    \update_post_meta($idPost, ROBOTS_EXCLUDE_PRESS, $_POST['data']);
                } else {
                    \delete_post_meta($idPost, ROBOTS_EXCLUDE_PRESS);
                }
            }
        }

        wp_redirect(getUrlSettings());
        exit();
    }

    function filter_plugin_action_links($arrLinks) {
        array_push($arrLinks,
                   '<a href=\'' . getUrlSettings() . '\'>'
                                    . __('Settings', 'domain-plugin-RobotsExcludePress') . '</a>');
        return $arrLinks;
    }

    function getUrlSettings() {
        return admin_url('options-general.php?page=plugin_RobotsExcludePress_settings');
    }

    function render_settings() {
        //  Based on http://codex.wordpress.org/Administration_Menus
        if (!current_user_can('manage_options' ))  {
            wp_die(__('You do not have sufficient permissions to access this page.',
                      'domain-plugin-RobotsExcludePress'));
        }
    ?><style>
        .robots-exclude-press--odd-row {
            background-color:                           #dde;
        }
      </style>
      <div class="wrap"><?php
      ?><p><?=sprintf(
        __('Check the checkbox(es) corresponding to the post(s) you want to exclude ' .
           'from robots, then submit the form by clicking \'%1$s\' at the top or bottom.',
           'domain-plugin-RobotsExcludePress'),
        __('Update Settings',
           'domain-plugin-RobotsExcludePress'));
             ?></p><?php
      ?><form method='post' action='admin-post.php'><?php
        ?><input type='hidden' name='action' value='plugin_RobotsExcludePress_settings' /><?php
          wp_nonce_field('plugin_RobotsExcludePress_settings_nonce');

          $w_p_query = new \WP_Query(['order'           => 'ASC',
                                      'orderby'         => 'name',
                                      'post_status'     => 'any',
                                      'post_type'       => \get_post_types(['public' => true]),
                                      'posts_per_page'  => -1]);

          global $post;
          if ($w_p_query->have_posts()) {
          ?><input type='submit' value='<?=__('Update Settings',
                                              'domain-plugin-RobotsExcludePress')
                                          ?>' class='button-primary'/><hr><?php
          ?><table style='border-collapse:collapse'><?php
            ?><tr><?php
              ?><th style='padding-right:15px;text-align:left'><?=
                __('ID', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th style='padding-right:15px;text-align:left'><?=
                __('Post Name', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th style='padding-right:15px;text-align:left'><?=
                __('Post Type', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th style='padding-right:15px;text-align:left'><?=
                __('Page Template', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th style='padding-right:15px;text-align:left'><?=
                __('Post Status', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
            ?></tr><?php
                $indexRow = 0;
                while($w_p_query->have_posts()) {
                    $w_p_query->the_post();
                    $idPost = $post->ID;
                    $strPostName = $post->post_name;
                ?><input type='hidden' name='post_<?=$idPost?>'><?php
                ?><tr <?=$indexRow % 2 == 0
                         ? 'class=\'robots-exclude-press--odd-row\''
                         : ""?>>
                    <td><a href='<?=get_edit_post_link($idPost)?>'><?=$idPost?></a></td>
                    <td><a href='<?=get_edit_post_link($idPost)?>'><?=$strPostName?></a></td>
                    <td><?=$post->post_type?></td>
                    <td><?=get_page_template_slug($idPost)?></td>
                    <td><?=get_post_status($idPost)?></td>
                  </tr><?php
                    $indexRow++;
                }
                wp_reset_postdata();
          ?></table><?php
          ?><hr><input type='submit' value='<?=__('Update Settings',
                                                  'domain-plugin-RobotsExcludePress')
                                              ?>' class='button-primary'/><?php
          } else {
          ?><?=__('No posts', 'domain-plugin-RobotsExcludePress')?><?php
          }
      ?></form></div><?php
    }

?>
