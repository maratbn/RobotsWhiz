<?php
/*
  Plugin Name: RobotsWhiz
  Plugin URI: https://github.com/maratbn/RobotsWhiz
  Description: An easy way to discourage search engines from indexing only specific pages / posts.
  Author: Marat Nepomnyashy
  Author URI: http://www.maratbn.com
  License: GPL3
  Version: 0.1.0-development_unreleased
  Text Domain: domain-plugin-RobotsWhiz
*/

/*
  RobotsWhiz -- WordPress plugin that allows site administrators to discourage
                search engines from indexing, following links from, caching,
                indexing images from, and / or have additional custom
                restrictions on only for certain specifically-designated pages
                and posts.

                It is an easy way to discourage search engines from indexing
                only specific pages / posts.

                This only applies to search engine robots, and will not effect
                the site's local search functionality.

  https://github.com/maratbn/RobotsWhiz

  Copyright (C) 2015  Marat Nepomnyashy  http://maratbn.com  maratbn@gmail

  Version:        0.1.0-development_unreleased

  Module:         RobotsWhiz.php

  Description:    Main PHP file for the WordPress plugin 'RobotsWhiz'.

  This file is part of RobotsWhiz.

  Licensed under the GNU General Public License Version 3.

  RobotsWhiz is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  RobotsWhiz is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with RobotsWhiz.  If not, see <http://www.gnu.org/licenses/>.
*/

    namespace plugin_RobotsWhiz;


    const ROBOTS_WHIZ    = 'robots_whiz';

    $ARR_TOKENS_STANDARD = ['noindex', 'nofollow', 'noarchive', 'noimageindex'];

    add_action('admin_menu', '\\plugin_RobotsWhiz\\action_admin_menu');
    add_action('admin_post_plugin_RobotsWhiz_settings',
               '\\plugin_RobotsWhiz\\action_admin_post_plugin_RobotsWhiz_settings');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__),
                                        '\\plugin_RobotsWhiz\\filter_plugin_action_links');


    function action_admin_menu() {
        add_options_page( __('RobotsWhiz Settings', 'domain-plugin-RobotsWhiz'),
                          __('RobotsWhiz', 'domain-plugin-RobotsWhiz'),
                          'manage_options',
                          'plugin_RobotsWhiz_settings',
                          '\\plugin_RobotsWhiz\\render_settings');
    }

    function action_admin_post_plugin_RobotsWhiz_settings() {
        //  Based on: http://jaskokoyn.com/2013/03/26/wordpress-admin-forms/
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient user permissions to modify options.',
                      'domain-plugin-RobotsWhiz'));
        }

        // Check that nonce field
        check_admin_referer('plugin_RobotsWhiz_settings_nonce');

        foreach ($_POST as $strFieldName => $strFieldValue) {
            preg_match('/^post_(\d+)$/', $strFieldName, $arrMatch);
            if ($arrMatch && count($arrMatch) == 2) {
                $idPost = $arrMatch[1];
                $flagIsLocked = isset($_POST['data_' . $idPost]);
                if ($flagIsLocked) {
                    \update_post_meta($idPost, ROBOTS_WHIZ, $_POST['data']);
                } else {
                    \delete_post_meta($idPost, ROBOTS_WHIZ);
                }
            }
        }

        wp_redirect(getUrlSettings());
        exit();
    }

    function filter_plugin_action_links($arrLinks) {
        array_push($arrLinks,
                   '<a href=\'' . getUrlSettings() . '\'>'
                                    . __('Settings', 'domain-plugin-RobotsWhiz') . '</a>');
        return $arrLinks;
    }

    function getUrlSettings() {
        return admin_url('options-general.php?page=plugin_RobotsWhiz_settings');
    }

    function render_settings() {
        //  Based on http://codex.wordpress.org/Administration_Menus
        if (!current_user_can('manage_options' ))  {
            wp_die(__('You do not have sufficient permissions to access this page.',
                      'domain-plugin-RobotsWhiz'));
        }
    ?><style>
        .robots-whiz--table {
            border-collapse:                            collapse;
            width:                                      100%;
        }
        .robots-whiz--odd-row {
            background-color:                           #dde;
        }
        .robots-whiz--column-header {
            padding-right:                              15px;
            text-align:                                 left;
        }
        .robots-whiz--readout {
            font-weight:                                bold;
            text-align:                                 center;
        }
        .robots-whiz--td--checkboxes {
            padding-bottom:                             10px;
            text-align:                                 center;
        }
        .robots-whiz--td--checkboxes label {
            margin-right:                               10px;
        }
        a.robots-whiz--td--link {
            text-decoration:                            none;
        }
        a.robots-whiz--td--link:hover {
            text-decoration:                            underline;
        }
      </style>
      <div class="wrap"><?php
      ?><p><?=sprintf(
        __('Check the checkbox(es) corresponding to the post(s) you want to exclude ' .
           'from robots, then submit the form by clicking \'%1$s\' at the top or bottom.',
           'domain-plugin-RobotsWhiz'),
        __('Update Settings',
           'domain-plugin-RobotsWhiz'));
             ?></p><?php
      ?><form method='post' action='admin-post.php'><?php
        ?><input type='hidden' name='action' value='plugin_RobotsWhiz_settings' /><?php
          wp_nonce_field('plugin_RobotsWhiz_settings_nonce');

          $w_p_query = new \WP_Query(['order'           => 'ASC',
                                      'orderby'         => 'name',
                                      'post_status'     => 'any',
                                      'post_type'       => \get_post_types(['public' => true]),
                                      'posts_per_page'  => -1]);

          global $post;
          if ($w_p_query->have_posts()) {
          ?><input type='submit' value='<?=__('Update Settings',
                                              'domain-plugin-RobotsWhiz')
                                          ?>' class='button-primary'/><hr><?php
          ?><table class='robots-whiz--table'><?php
            ?><tr><?php
              ?><th class='robots-whiz--column-header'><?=
                __('ID', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                __('Post Name', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                __('Post Type', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                __('Page Template', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                __('Post Status', 'domain-plugin-RobotsWhiz')
              ?></th><?php
            ?></tr><?php
                $indexRow = 0;
                while($w_p_query->have_posts()) {
                    $w_p_query->the_post();
                    $idPost = $post->ID;
                    $strPostName = $post->post_name;
                ?><tbody data--robots-whiz--role='post-config'><?php
                  ?><input type='hidden' name='post_<?=$idPost?>'><?php
                  ?><tr <?=$indexRow % 2 == 0
                           ? 'class=\'robots-whiz--odd-row\''
                           : ""?>>
                      <td><a href='<?=get_edit_post_link($idPost)?>'><?=$idPost?></a></td>
                      <td><a href='<?=get_edit_post_link($idPost)?>'><?=$strPostName?></a></td>
                      <td><?=$post->post_type?></td>
                      <td><?=get_page_template_slug($idPost)?></td>
                      <td><?=get_post_status($idPost)?></td>
                    </tr>
                    <tr <?=$indexRow % 2 == 0
                           ? 'class=\'robots-whiz--odd-row\''
                           : ""?>>
                      <td colspan='5' class='robots-whiz--td--checkboxes'>
                        <div class='robots-whiz--readout'
                             data--robots-whiz--role='readout' type='text'></div>
                        <div>
                          <label><input type='checkbox' data-robots-whiz--role='cb-all'>
                              <i>all</i>
                            </input></label>
                          <label><input type='checkbox' data-robots-whiz--role='cb-none'>
                              <i>none</i>
                            </input></label>
                          <?php
                              global $ARR_TOKENS_STANDARD;
                              for ($i = 0; $i < count($ARR_TOKENS_STANDARD); $i++) {
                              ?><label><input type='checkbox' data-robots-whiz--role='cb-std'
                                              name='<?=$ARR_TOKENS_STANDARD[$i]?>'><?php
                                  ?><?=$ARR_TOKENS_STANDARD[$i]?><?php
                                ?></input></label><?php
                              }
                          ?>
                        </div>
                        <div>
                          <label><a class='robots-whiz--td--link'
                                    data--robots-whiz--role='add-custom'
                                    href='#'>add custom...</a></label>
                          <label><a class='robots-whiz--td--link'
                                    data--robots-whiz--role='clear-custom'
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
                                                  'domain-plugin-RobotsWhiz')
                                              ?>' class='button-primary'/><?php
          ?>
          <script type='text/javascript'>
              function _processPost($, $tbodyPost) {
                  var arrTokens = [];

                  function _excludeToken(strToken) {
                      var indexToken = arrTokens.indexOf(strToken);
                      if (indexToken >= 0) arrTokens.splice(indexToken, 1);
                  }

                  function _includeToken(strToken) {
                      if (arrTokens.indexOf(strToken) >= 0) return;
                      arrTokens.push(strToken);
                  }

                  function _isIncluded(strToken) {
                      return (arrTokens.indexOf(strToken) >= 0);
                  }


                  var $inputCheckbox = $tbodyPost.find('input[type=checkbox]');

                  var mapCheckboxesStandard      = [],
                      $inputCheckboxes_standard  = $inputCheckbox
                                                       .filter('[data-robots-whiz--role=cb-std]');

                  for (var i = 0; i < $inputCheckboxes_standard.length; i++) {
                      var $inputCheckbox_standard = $($inputCheckboxes_standard[i]);
                      var strName = $inputCheckbox_standard.attr('name');
                      mapCheckboxesStandard[strName] = $inputCheckbox_standard;
                  }

                  function _excludeAll() {
                      for (strName in mapCheckboxesStandard) {
                          _excludeToken(strName);
                      }
                  }

                  function _includeAll() {
                      for (strName in mapCheckboxesStandard) {
                          _includeToken(strName);
                      }
                  }

                  function _areAllExcluded() {
                      for (strName in mapCheckboxesStandard) {
                          if (_isIncluded(strName)) return false;
                      }
                      return true;
                  }

                  function _areAllIncluded() {
                      for (strName in mapCheckboxesStandard) {
                          if (!_isIncluded(strName)) return false;
                      }
                      return true;
                  }

                  var $divReadout =  $tbodyPost
                                           .find('div[data--robots-whiz--role=readout]');

                  function _updateReadout() {
                      if (arrTokens.length == 0) {
                          $divReadout.html("&nbsp;");
                          return;
                      }

                      $divReadout.text("<meta name=\"robots\" content=\"" +
                                          arrTokens.join(" ").replace("\\", "\\\\")
                                                             .replace("\"", "\\\"")
                                                                          + "\">");
                  }

                  var $inputCheckbox_all =   $inputCheckbox
                                                       .filter('[data-robots-whiz--role=cb-all]'),
                      $inputCheckbox_none =  $inputCheckbox
                                                      .filter('[data-robots-whiz--role=cb-none]');

                  function _updateCheckboxes_all_none() {
                      $inputCheckbox_all.prop('checked', _areAllIncluded());
                      $inputCheckbox_none.prop('checked', _areAllExcluded());

                      _updateReadout();
                  }

                  _updateCheckboxes_all_none();


                  var $inputCheckbox_noindex =       $inputCheckbox.filter('[name=noindex]'),
                      $inputCheckbox_nofollow =      $inputCheckbox.filter('[name=nofollow]'),
                      $inputCheckbox_noarchive =     $inputCheckbox.filter('[name=noarchive]'),
                      $inputCheckbox_noimageindex =  $inputCheckbox.filter('[name=noimageindex]');

                  function _processCheckboxes() {
                      if ($inputCheckbox_noindex.is(':checked')) {
                          _includeToken('noindex');
                      } else {
                          _excludeToken('noindex');
                      }
                      if ($inputCheckbox_nofollow.is(':checked')) {
                          _includeToken('nofollow');
                      } else {
                          _excludeToken('nofollow');
                      }
                      if ($inputCheckbox_noarchive.is(':checked')) {
                          _includeToken('noarchive');
                      } else {
                          _excludeToken('noarchive');
                      }
                      if ($inputCheckbox_noimageindex.is(':checked')) {
                          _includeToken('noimageindex');
                      } else {
                          _excludeToken('noimageindex');
                      }

                      _updateCheckboxes_all_none();
                  }

                  function _updateCheckboxes() {
                      $inputCheckbox_noindex.prop('checked', _isIncluded('noindex'));
                      $inputCheckbox_nofollow.prop('checked', _isIncluded('nofollow'));
                      $inputCheckbox_noarchive.prop('checked', _isIncluded('noarchive'));
                      $inputCheckbox_noimageindex.prop('checked', _isIncluded('noimageindex'));

                      _updateCheckboxes_all_none();
                  }

                  $inputCheckbox_noindex.bind('change', _processCheckboxes);
                  $inputCheckbox_nofollow.bind('change', _processCheckboxes);
                  $inputCheckbox_noarchive.bind('change', _processCheckboxes);
                  $inputCheckbox_noimageindex.bind('change', _processCheckboxes);

                  $inputCheckbox_all.bind('change', function() {
                          if (!$inputCheckbox_all.is(':checked')) {
                              $inputCheckbox_all.prop('checked', true);
                              return;
                          }

                          _includeAll();
                          _updateCheckboxes();
                      });

                  $inputCheckbox_none.bind('change', function() {
                          if (!$inputCheckbox_none.is(':checked')) {
                              $inputCheckbox_none.prop('checked', true);
                              return;
                          }

                          _excludeAll();
                          _updateCheckboxes();
                      });

                  var $aAddCustom = $tbodyPost
                                          .find('a[data--robots-whiz--role=add-custom]');
                  $aAddCustom.click(function(event) {
                          event.preventDefault();

                          var strTokens = window.prompt("Please specify your custom content:");
                          if (!strTokens) return;

                          var arrCustomTokens = strTokens.split(/\s+/);
                          if (!arrCustomTokens) return;

                          for (var i = 0; i < arrCustomTokens.length; i++) {
                              var strCustomToken = arrCustomTokens[i];
                              if (!strCustomToken) continue;

                              _includeToken(strCustomToken);
                          }
                          _updateCheckboxes();
                      });
              }

              jQuery(document).ready(function($) {
                      var arrPosts = $('tbody[data--robots-whiz--role=post-config]');

                      for (var i = 0; i < arrPosts.length; i++) {
                          _processPost($, $(arrPosts[i]));
                      }
                  });
          </script><?php
          } else {
          ?><?=__('No posts', 'domain-plugin-RobotsWhiz')?><?php
          }
      ?></form></div><?php
    }

?>
