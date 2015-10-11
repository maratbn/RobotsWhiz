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
        .robots-exclude-press--table {
            border-collapse:                            collapse;
            width:                                      100%;
        }
        .robots-exclude-press--odd-row {
            background-color:                           #dde;
        }
        .robots-exclude-press--column-header {
            padding-right:                              15px;
            text-align:                                 left;
        }
        .robots-exclude-press--readout {
            font-weight:                                bold;
            text-align:                                 center;
        }
        .robots-exclude-press--td--checkboxes {
            padding-bottom:                             10px;
            text-align:                                 center;
        }
        .robots-exclude-press--td--checkboxes label {
            margin-right:                               10px;
        }
        a.robots-exclude-press--td--link {
            text-decoration:                            none;
        }
        a.robots-exclude-press--td--link:hover {
            text-decoration:                            underline;
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
          ?><table class='robots-exclude-press--table'><?php
            ?><tr><?php
              ?><th class='robots-exclude-press--column-header'><?=
                __('ID', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th class='robots-exclude-press--column-header'><?=
                __('Post Name', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th class='robots-exclude-press--column-header'><?=
                __('Post Type', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th class='robots-exclude-press--column-header'><?=
                __('Page Template', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
              ?><th class='robots-exclude-press--column-header'><?=
                __('Post Status', 'domain-plugin-RobotsExcludePress')
              ?></th><?php
            ?></tr><?php
                $indexRow = 0;
                while($w_p_query->have_posts()) {
                    $w_p_query->the_post();
                    $idPost = $post->ID;
                    $strPostName = $post->post_name;
                ?><tbody data--robots-exclude-press--role='post-config'><?php
                  ?><input type='hidden' name='post_<?=$idPost?>'><?php
                  ?><tr <?=$indexRow % 2 == 0
                           ? 'class=\'robots-exclude-press--odd-row\''
                           : ""?>>
                      <td><a href='<?=get_edit_post_link($idPost)?>'><?=$idPost?></a></td>
                      <td><a href='<?=get_edit_post_link($idPost)?>'><?=$strPostName?></a></td>
                      <td><?=$post->post_type?></td>
                      <td><?=get_page_template_slug($idPost)?></td>
                      <td><?=get_post_status($idPost)?></td>
                    </tr>
                    <tr <?=$indexRow % 2 == 0
                           ? 'class=\'robots-exclude-press--odd-row\''
                           : ""?>>
                      <td colspan='5' class='robots-exclude-press--td--checkboxes'>
                        <div class='robots-exclude-press--readout'
                             data--robots-exclude-press--role='readout' type='text'></div>
                        <div>
                          <label><input type='checkbox' name='all'><i>all</i></input></label>
                          <label><input type='checkbox' name='none'><i>none</i></input></label>
                          <label><input type='checkbox' name='noindex'>noindex</input></label>
                          <label><input type='checkbox' name='nofollow'>nofollow</input></label>
                          <label><input type='checkbox' name='noarchive'>noarchive</input></label>
                          <label><input type='checkbox'
                                        name='noimageindex'>noimageindex</input></label>
                        </div>
                        <div>
                          <label><a class='robots-exclude-press--td--link'
                                    data--robots-exclude-press--role='add-custom'
                                    href='#'>add custom...</a></label>
                          <label><a class='robots-exclude-press--td--link'
                                    data--robots-exclude-press--role='clear-custom'
                                    href='#'>clear custom</a></label>
                        </div>
                      </td>
                    </tr>
                  </tbody><?php
                    $indexRow++;
                }
                wp_reset_postdata();
          ?></table><?php
          ?><hr><input type='submit' value='<?=__('Update Settings',
                                                  'domain-plugin-RobotsExcludePress')
                                              ?>' class='button-primary'/><?php
          ?>
          <script type='text/javascript'>
              function _processPost($tbodyPost) {
                  var arrTokens = [];

                  function _excludeToken(strToken) {
                      var indexToken = arrTokens.indexOf(strToken);
                      if (indexToken >= 0) arrTokens.splice(indexToken, 1);
                  }

                  function _includeToken(strToken) {
                      if (arrTokens.indexOf(strToken) >= 0) return;
                      arrTokens.push(strToken);
                  }

                  var $divReadout =     $tbodyPost
                                           .find('div[data--robots-exclude-press--role=readout]'),
                      $inputCheckbox =  $tbodyPost.find('input[type=checkbox]');

                  var $inputCheckbox_all =           $inputCheckbox.filter('[name=all]'),
                      $inputCheckbox_none =          $inputCheckbox.filter('[name=none]'),
                      $inputCheckbox_noindex =       $inputCheckbox.filter('[name=noindex]'),
                      $inputCheckbox_nofollow =      $inputCheckbox.filter('[name=nofollow]'),
                      $inputCheckbox_noarchive =     $inputCheckbox.filter('[name=noarchive]'),
                      $inputCheckbox_noimageindex =  $inputCheckbox.filter('[name=noimageindex]');


                  function _updateReadout() {
                      var flagUncheckAll   = false,
                          flagUncheckNone  = false;

                      if ($inputCheckbox_noindex.is(':checked')) {
                          _includeToken('noindex');
                          flagUncheckNone = true;
                      } else {
                          _excludeToken('noindex');
                          flagUncheckAll = true;
                      }
                      if ($inputCheckbox_nofollow.is(':checked')) {
                          _includeToken('nofollow');
                          flagUncheckNone = true;
                      } else {
                          _excludeToken('nofollow');
                          flagUncheckAll = true;
                      }
                      if ($inputCheckbox_noarchive.is(':checked')) {
                          _includeToken('noarchive');
                          flagUncheckNone = true;
                      } else {
                          _excludeToken('noarchive');
                          flagUncheckAll = true;
                      }
                      if ($inputCheckbox_noimageindex.is(':checked')) {
                          _includeToken('noimageindex');
                          flagUncheckNone = true;
                      } else {
                          _excludeToken('noimageindex');
                          flagUncheckAll = true;
                      }

                      if (flagUncheckAll) {
                          $inputCheckbox_all.prop('checked', false);
                      }
                      if (flagUncheckNone) {
                          $inputCheckbox_none.prop('checked', false);
                      }

                      if (arrTokens.length == 0) {
                          $divReadout.html("&nbsp;");
                          return;
                      }

                      $divReadout.text("<meta name=\"robots\" content=\"" +
                                          arrTokens.join(" ").replace("\\", "\\\\")
                                                             .replace("\"", "\\\"")
                                                                          + "\">");
                  }
                  _updateReadout();
                  $inputCheckbox_noindex.bind('change', _updateReadout);
                  $inputCheckbox_nofollow.bind('change', _updateReadout);
                  $inputCheckbox_noarchive.bind('change', _updateReadout);
                  $inputCheckbox_noimageindex.bind('change', _updateReadout);

                  $inputCheckbox_all.bind('change', function() {
                          if (!$inputCheckbox_all.is(':checked')) {
                              $inputCheckbox_all.prop('checked', true);
                              return;
                          }

                          $inputCheckbox_noindex.prop('checked', true);
                          $inputCheckbox_nofollow.prop('checked', true);
                          $inputCheckbox_noarchive.prop('checked', true);
                          $inputCheckbox_noimageindex.prop('checked', true);
                          _updateReadout();
                      });

                  $inputCheckbox_none.bind('change', function() {
                          if (!$inputCheckbox_none.is(':checked')) {
                              $inputCheckbox_none.prop('checked', true);
                              return;
                          }

                          $inputCheckbox_noindex.prop('checked', false);
                          $inputCheckbox_nofollow.prop('checked', false);
                          $inputCheckbox_noarchive.prop('checked', false);
                          $inputCheckbox_noimageindex.prop('checked', false);
                          _updateReadout();
                      });
              }

              jQuery(document).ready(function($) {
                      var arrPosts = $('tbody[data--robots-exclude-press--role=post-config]');

                      for (var i = 0; i < arrPosts.length; i++) {
                          _processPost($(arrPosts[i]));
                      }
                  });
          </script><?php
          } else {
          ?><?=__('No posts', 'domain-plugin-RobotsExcludePress')?><?php
          }
      ?></form></div><?php
    }

?>
