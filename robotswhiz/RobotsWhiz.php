<?php
/*
  Plugin Name: Robots Meta Whiz
  Plugin URI: https://wordpress.org/plugins/robotswhiz
  Plugin URI: https://github.com/maratbn/RobotsWhiz
  Description: An easy way to discourage search engines from indexing only specific pages / posts with custom meta tags.
  Author: Marat Nepomnyashy
  Author URI: http://www.maratbn.com
  License: GPL3
  Version: 1.1.0-development_unreleased
  Text Domain: domain-plugin-RobotsWhiz
*/

/*
  Robots Meta Whiz -- WordPress plugin that allows site administrators to discourage
                search engines from indexing, following links from, caching,
                indexing images from, and / or have additional custom
                restrictions on only for certain specifically-designated pages
                and posts.

                It is an easy way to discourage search engines from indexing
                only specific pages / posts.

                This only applies to search engine robots, and will not effect
                the site's local search functionality.

  https://github.com/maratbn/RobotsWhiz

  Copyright (C) 2015-2016  Marat Nepomnyashy  http://maratbn.com  maratbn@gmail

  Version:        1.1.0-development_unreleased

  Module:         robotswhiz/RobotsWhiz.php

  Description:    Main PHP file for the WordPress plugin 'Robots Meta Whiz'.

  This file is part of Robots Meta Whiz.

  Licensed under the GNU General Public License Version 3.

  Robots Meta Whiz is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Robots Meta Whiz is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Robots Meta Whiz.  If not, see <http://www.gnu.org/licenses/>.
*/

    namespace plugin_RobotsWhiz;


    const PLUGIN_VERSION = '1.1.0-development_unreleased';


    const IS_MODE_RELEASE = false;


    const PHP_VERSION_MIN_SUPPORTED = '5.4';
    const ROBOTS_WHIZ__META_CONTENT = 'robots_whiz__meta_content';

    $ARR_TOKENS_STANDARD = ['noindex', 'nofollow', 'noarchive', 'noimageindex'];

    \add_action('wp_head', '\\plugin_RobotsWhiz\\action_wp_head');

    \register_activation_hook(__FILE__, '\\plugin_RobotsWhiz\\plugin_activation_hook');


    if (\is_admin()) {
        \add_action('admin_menu', '\\plugin_RobotsWhiz\\action_admin_menu');
        \add_action('admin_post_plugin_RobotsWhiz_settings',
                    '\\plugin_RobotsWhiz\\action_admin_post_plugin_RobotsWhiz_settings');
        \add_filter('plugin_action_links_' . \plugin_basename(__FILE__),
                    '\\plugin_RobotsWhiz\\filter_plugin_action_links');
    }


    function action_admin_menu() {
        \add_options_page(\__('Robots Meta Whiz Settings', 'domain-plugin-RobotsWhiz'),
                          \__('Robots Meta Whiz', 'domain-plugin-RobotsWhiz'),
                          'manage_options',
                          'plugin_RobotsWhiz_settings',
                          '\\plugin_RobotsWhiz\\render_settings');
    }

    function action_admin_post_plugin_RobotsWhiz_settings() {
        //  Based on: http://jaskokoyn.com/2013/03/26/wordpress-admin-forms/
        if (!current_user_can('manage_options')) {
            \wp_die(\__('Insufficient user permissions to modify options.',
                        'domain-plugin-RobotsWhiz'));
        }

        // Check that nonce field
        \check_admin_referer('plugin_RobotsWhiz_settings_nonce');

        foreach ($_POST as $strFieldName => $strFieldValue) {
            \preg_match('/^post_(\d+)$/', $strFieldName, $arrMatch);
            if ($arrMatch && count($arrMatch) == 2) {
                $idPost = $arrMatch[1];
                $field = 'post_' . $idPost;
                $data = isset($_POST[$field]) ? $_POST[$field] : null;
                if ($data) {
                    \update_post_meta($idPost, ROBOTS_WHIZ__META_CONTENT, $data);
                } else {
                    \delete_post_meta($idPost, ROBOTS_WHIZ__META_CONTENT);
                }
            }
        }

        \wp_redirect(getUrlSettings());
        exit();
    }

    function action_wp_head() {
        if (!\is_singular()) return;

        global $wp_query;
        if ($wp_query->post_count != 1) return;

        $post = $wp_query->posts[0];
        if (!$post) return;

        $idPost = $post->ID;
        if ($idPost == null) return;

        $strPostMeta = \get_post_meta($idPost, ROBOTS_WHIZ__META_CONTENT, true);
        $dataPost = $strPostMeta ? \json_decode($strPostMeta, true) : null;
        $strData = $dataPost ? $dataPost['robots'] : null;
        if ($strData == null) return;

        ?><meta name='robots' content='<?=\addslashes(\implode(', ',
                                                               \preg_split('/\s+/',
                                                                           $strData)))?>' /><?php
    }

    function filter_plugin_action_links($arrLinks) {
        \array_push($arrLinks,
                    '<a href=\'' . getUrlSettings() . '\'>'
                                    . \__('Settings', 'domain-plugin-RobotsWhiz') . '</a>');
        return $arrLinks;
    }

    function getUrlSettings() {
        return \admin_url('options-general.php?page=plugin_RobotsWhiz_settings');
    }

    function getUVArg() {
        return 'uv=' . PLUGIN_VERSION . (IS_MODE_RELEASE ? "" : ('_' . \time() . \rand()));
    }

    function plugin_activation_hook() {
         if (\version_compare(\strtolower(PHP_VERSION), PHP_VERSION_MIN_SUPPORTED, '<')) {
            \wp_die(
                \__('Robots Meta Whiz plugin cannot be activated because the currently active PHP version on this server is < 5.4 and not supported.  PHP version >= 5.4 is required.',
                    'domain-plugin-RobotsWhiz'));
        }
    }

    function render_settings() {
        //  Based on http://codex.wordpress.org/Administration_Menus
        if (!\current_user_can('manage_options' ))  {
            \wp_die(\__('You do not have sufficient permissions to access this page.',
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
      ?><p><?=\sprintf(
        \__('Check the checkbox(es) corresponding to the post(s) you want to discourage ' .
            'robots on, then submit the form by clicking \'%1$s\' at the top or bottom.',
            'domain-plugin-RobotsWhiz'),
        \__('Update Settings',
            'domain-plugin-RobotsWhiz'));
             ?></p><?php
      ?><form method='post' action='admin-post.php'><?php
        ?><input type='hidden' name='action' value='plugin_RobotsWhiz_settings' /><?php
          \wp_nonce_field('plugin_RobotsWhiz_settings_nonce');

          $w_p_query = new \WP_Query(['order'           => 'ASC',
                                      'orderby'         => 'name',
                                      'post_status'     => 'any',
                                      'post_type'       => \get_post_types(['public' => true]),
                                      'posts_per_page'  => -1]);

          global $post;
          if ($w_p_query->have_posts()) {
          ?><input type='submit' value='<?=\__('Update Settings',
                                               'domain-plugin-RobotsWhiz')
                                          ?>' class='button-primary'/><hr><?php
          ?><table class='robots-whiz--table'><?php
            ?><tr><?php
              ?><th class='robots-whiz--column-header'><?=
                \__('ID', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                \__('Post Name', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                \__('Post Type', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                \__('Page Template', 'domain-plugin-RobotsWhiz')
              ?></th><?php
              ?><th class='robots-whiz--column-header'><?=
                \__('Post Status', 'domain-plugin-RobotsWhiz')
              ?></th><?php
            ?></tr><?php
                $indexRow = 0;
                while($w_p_query->have_posts()) {
                    $w_p_query->the_post();
                    $idPost = $post->ID;
                    $strPostName = $post->post_name;
                    $strPostMeta = \get_post_meta($idPost, ROBOTS_WHIZ__META_CONTENT, true);
                    $dataPost = $strPostMeta ? \json_decode($strPostMeta, true) : null;
                    $strData = $dataPost ? $dataPost['robots'] : null;
                ?><tbody data--robots-whiz--role='post-config'><?php
                  ?><input type='hidden'
                           name='post_<?=$idPost?>'
                           data-robots-whiz--role='data'
                           value='<?=\htmlspecialchars(
                                            \json_encode(['robots' => $strData]))?>'><?php
                  ?><tr <?=$indexRow % 2 == 0
                           ? 'class=\'robots-whiz--odd-row\''
                           : ""?>>
                      <td><a href='<?=\get_edit_post_link($idPost)?>'><?=$idPost?></a></td>
                      <td><a href='<?=\get_edit_post_link($idPost)?>'><?=$strPostName?></a></td>
                      <td><?=$post->post_type?></td>
                      <td><?=\get_page_template_slug($idPost)?></td>
                      <td><?=\get_post_status($idPost)?></td>
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
                              for ($i = 0; $i < \count($ARR_TOKENS_STANDARD); $i++) {
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
                \wp_reset_postdata();
          ?></table><?php
          ?><hr><input type='submit' value='<?=\__('Update Settings',
                                                   'domain-plugin-RobotsWhiz')
                                              ?>' class='button-primary'/><?php
          ?>
          <script type='text/javascript'>
              (function() {
                  var strConfirmCustomClear
                              = "<?=\__('Are you sure you want to clear-out your custom content?',
                                        'domain-plugin-RobotsWhiz')?>",
                      strPromptCustomContent = "<?=\__('Please specify your custom content:',
                                                       'domain-plugin-RobotsWhiz')?>";

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


                      var mapCheckboxesStandard = [];

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

                      function _getArrTokensNonStandard() {
                          var arrTokensNonStandard = [];

                          for (var i = 0; i < arrTokens.length; i++) {
                              var strToken = arrTokens[i];

                              if (!mapCheckboxesStandard[strToken]) {
                                  arrTokensNonStandard.push(strToken);
                              }
                          }

                          return arrTokensNonStandard;
                      }


                      var $inputData = $tbodyPost.find('input[data-robots-whiz--role=data]');

                      var strJSONTokensInitial = $inputData.val();
                      var dataTokensInitial = strJSONTokensInitial &&
                                              window.JSON.parse(strJSONTokensInitial);
                      var strTokensInitial = dataTokensInitial && dataTokensInitial['robots'];
                      var arrTokensInitial = strTokensInitial && strTokensInitial.split(/\s+/) || [];

                      for (var i = 0; i < arrTokensInitial.length; i++) {
                          _includeToken(arrTokensInitial[i]);
                      }


                      var $divReadout = $tbodyPost.find('div[data--robots-whiz--role=readout]');

                      function _updateReadout() {
                          if (arrTokens.length == 0) {
                              $divReadout.html("&nbsp;");
                          } else {
                              $divReadout.text("<meta name=\"robots\" content=\""
                                                          + arrTokens.join(", ")
                                                                     .replace(/\\/g, "\\\\")
                                                                     .replace(/"/g, "\\\"") + "\">");
                          }

                          $inputData.val(window.JSON.stringify({'robots': arrTokens.join(" ")}));
                      }


                      var $inputCheckbox = $tbodyPost.find('input[type=checkbox]');

                      var $inputCheckboxes_standard = $inputCheckbox
                                                           .filter('[data-robots-whiz--role=cb-std]');

                      for (var i = 0; i < $inputCheckboxes_standard.length; i++) {
                          var $inputCheckbox_standard = $($inputCheckboxes_standard[i]);
                          var strName = $inputCheckbox_standard.attr('name');
                          mapCheckboxesStandard[strName] = $inputCheckbox_standard;
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

                      function _updateCheckboxes() {
                          for (var strName in mapCheckboxesStandard) {
                              mapCheckboxesStandard[strName].prop('checked', _isIncluded(strName));
                          }

                          _updateCheckboxes_all_none();
                      }

                      _updateCheckboxes();

                      function _initCheckboxForInput(strName) {
                          var $checkboxStandard = mapCheckboxesStandard[strName];

                          $checkboxStandard.bind('change', function() {
                                  if ($checkboxStandard.is(':checked')) {
                                      _includeToken(strName);
                                  } else {
                                      _excludeToken(strName);
                                  }

                                  _updateCheckboxes_all_none();
                              });
                      }

                      for (var strName in mapCheckboxesStandard) {
                          _initCheckboxForInput(strName);
                      }

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


                      var $aAddCustom =    $tbodyPost.find('a[data--robots-whiz--role=add-custom]'),
                          $aClearCustom =  $tbodyPost.find('a[data--robots-whiz--role=clear-custom]');

                      function _updateLinks() {
                          var arrTokensPrev = _getArrTokensNonStandard();
                          if (arrTokensPrev.length == 0) {
                              $aAddCustom.text("add custom...");
                              $aClearCustom.css('display', 'none');
                          } else {
                              $aAddCustom.text("modify custom...");
                              $aClearCustom.css('display', "");
                          }
                      }

                      _updateLinks();

                      $aAddCustom.click(function(event) {
                              event.preventDefault();

                              var arrTokensPrev = _getArrTokensNonStandard();

                              var strTokens = window.prompt(strPromptCustomContent,
                                                            arrTokensPrev.join(" "));
                              if (strTokens == null) return;

                              var arrTokensNew = strTokens.split(/\s+/);
                              if (!arrTokensNew) return;

                              for (var i = 0; i < arrTokensNew.length; i++) {
                                  var strCustomToken = arrTokensNew[i];
                                  if (!strCustomToken) continue;

                                  _includeToken(strCustomToken);
                              }

                              for (var i = 0; i < arrTokensPrev.length; i++) {
                                  var strTokenPrev = arrTokensPrev[i];
                                  if (arrTokensNew.indexOf(strTokenPrev) >= 0) continue;

                                  _excludeToken(strTokenPrev);
                              }

                              _updateCheckboxes();
                              _updateLinks();
                          });

                      $aClearCustom.click(function(event) {
                              event.preventDefault();

                              var arrTokensPrev = _getArrTokensNonStandard();
                              if (arrTokensPrev.length == 0) return;

                              if (!window.confirm(strConfirmCustomClear))
                                  return;

                              for (var i = 0; i < arrTokensPrev.length; i++) {
                                  _excludeToken(arrTokensPrev[i]);
                              }

                              _updateReadout();
                              _updateLinks();
                          });
                  }

                  jQuery(document).ready(function($) {
                          var arrPosts = $('tbody[data--robots-whiz--role=post-config]');

                          for (var i = 0; i < arrPosts.length; i++) {
                              _processPost($, $(arrPosts[i]));
                          }
                      });
              })();
          </script><?php
          } else {
          ?><?=\__('No posts', 'domain-plugin-RobotsWhiz')?><?php
          }
      ?></form></div><?php
    }

?>
