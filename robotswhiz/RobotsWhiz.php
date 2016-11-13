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
  Robots Meta Whiz -- WordPress plugin that allows site administrators to
                      deploy custom meta robots tags to discourage search
                      engines from indexing, following links from, caching,
                      indexing images from, and / or have additional custom
                      restrictions on only for certain specifically-designated
                      pages and posts.

                      It is an easy way to discourage search engines from
                      indexing only specific pages / posts.

                      This only applies to search engine robots, and will not
                      effect the site's local search functionality.

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


    \add_action('wp_head', '\\plugin_RobotsWhiz\\action_wp_head');

    \register_activation_hook(__FILE__, '\\plugin_RobotsWhiz\\plugin_activation_hook');


    if (\is_admin()) {
        \add_action('admin_enqueue_scripts', '\\plugin_RobotsWhiz\\action_admin_enqueue_scripts');
        \add_action('admin_menu', '\\plugin_RobotsWhiz\\action_admin_menu');
        \add_action('admin_post_plugin_RobotsWhiz_settings',
                    '\\plugin_RobotsWhiz\\action_admin_post_plugin_RobotsWhiz_settings');
        \add_filter('plugin_action_links_' . \plugin_basename(__FILE__),
                    '\\plugin_RobotsWhiz\\filter_plugin_action_links');
    }


    function action_admin_enqueue_scripts($hook) {
        if ($hook != 'settings_page_plugin_RobotsWhiz_settings') return;

        \wp_enqueue_script(
            'plugin_RobotsWhiz_entry',
            \plugin_dir_url(__FILE__) . 'webpack_out/entry.js',
            ['jquery'],
            getUVArg(),
            false);
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
          ?><table class='robots-whiz--table' data--robots-whiz--role='posts'><?php
            ?><thead><?php
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
            ?></thead><?php
                $indexRow = 0;
                $arrPosts = [];
                while($w_p_query->have_posts()) {

                    $w_p_query->the_post();
                    $idPost       = $post->ID;
                    $strPostName  = $post->post_name;
                    $strPostMeta  = \get_post_meta($idPost, ROBOTS_WHIZ__META_CONTENT, true);
                    $dataPost     = $strPostMeta ? \json_decode($strPostMeta, true) : null;
                    $strData      = $dataPost ? $dataPost['robots'] : null;
                    $objPost = [
                            'id'         => $idPost,
                            'link_edit'  => \get_edit_post_link($idPost, ""),
                            'name'       => $strPostName,
                            'type'       => $post->post_type,
                            'template'   => \get_page_template_slug($idPost),
                            'status'     => \get_post_status($idPost),
                            'data'       => $strData
                        ];
                    \array_push($arrPosts, $objPost);
                ?><tbody data--robots-whiz--role='post-config'><?php
                ?></tbody><?php
                    $indexRow++;
                }
                \wp_reset_postdata();
          ?></table><?php
          ?><hr><input type='submit' value='<?=\__('Update Settings',
                                                   'domain-plugin-RobotsWhiz')
                                              ?>' class='button-primary'/><?php

              $totalPosts = \count($arrPosts);
          ?>
          <script type='text/javascript'>
              (function() {
                  var arrPosts = <?=\json_encode($arrPosts)?>,

                      strConfirmCustomClear
                            = "<?=\__('Are you sure you want to clear-out your custom content?',
                                      'domain-plugin-RobotsWhiz')?>",
                      strLabelAddCustom
                            = "<?=\__('add custom...',
                                      'domain-plugin-RobotsWhiz')?>",
                      strLabelClearCustom
                            = "<?=\__('clear custom',
                                      'domain-plugin-RobotsWhiz')?>",
                      strLabelModifyCustom
                            = "<?=\__('modify custom...',
                                      'domain-plugin-RobotsWhiz')?>",
                      strPromptCustomContent
                            = "<?=\__('Please specify your custom content:',
                                      'domain-plugin-RobotsWhiz')?>";

                  window._plugin_RobotsWhiz__setStrings({
                          strConfirmCustomClear:    strConfirmCustomClear,
                          strLabelAddCustom:        strLabelAddCustom,
                          strLabelClearCustom:      strLabelClearCustom,
                          strLabelModifyCustom:     strLabelModifyCustom,
                          strPromptCustomContent:   strPromptCustomContent
                      });


                  jQuery(document).ready(function($) {
                          var $tablePosts = $('table[data--robots-whiz--role=posts]'),
                              arr$tbody = $('tbody[data--robots-whiz--role=post-config]');

                          if ($tablePosts.length != 1) return;
                          if (arr$tbody.length != <?=$totalPosts?>) return;

                      <?php
                          for ($i = 0; $i < $totalPosts; $i++) {
                          ?>
                              window._plugin_RobotsWhiz__renderControls(
                                    $tablePosts[0],
                                    arr$tbody[<?=$i?>],
                                    {
                                        indexRow:             <?=$i?>,
                                        post:                 arrPosts[<?=$i?>]
                                    },
                                    $);
                          <?php
                          }
                      ?>
                      });
              })();
          </script><?php
          } else {
          ?><?=\__('No posts', 'domain-plugin-RobotsWhiz')?><?php
          }
      ?></form></div><?php
    }

?>
