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

  Copyright (C) 2015-2017  Marat Nepomnyashy  http://maratbn.com  maratbn@gmail

  Version:        1.1.0-development_unreleased

  Module:         webpack.config.js

  Description:    Webpack configuration file that determines which resources
                  it packs into the published plugin, and how it packs them.

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

const path = require('path');

module.exports = {
    entry: ['./webpack_in/entry.js',
            './webpack_in/entry.jsx'],
    module: {
        loaders: [{
            test:     /\.jsx$/,
            loader:   'babel-loader',
            exclude:  [/node_modules/],
            query: {
                presets: ['es2015', 'react'],
                plugins: ['transform-object-rest-spread']
              }
          }]
      },
    output: {
        path:      path.join(__dirname, 'robotswhiz', 'webpack_out'),
        filename:  'robotswhiz.js'
      }
  };
