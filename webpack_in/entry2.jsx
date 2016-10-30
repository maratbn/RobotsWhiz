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

  Module:         webpack_in/entry2.jsx

  Description:    Original JSX source code.

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

import React from 'react';
import ReactDOM from 'react-dom';

console.log("JSX entry logic.");

class Test extends React.Component {
  render() {
      return (<center>React component rendering logic.</center>);
    }
}

const elRenderTo = document.createElement('div');
window.document.body.appendChild(elRenderTo);

ReactDOM.render(<Test/>, elRenderTo);


class Controls extends React.Component {
  render() {
      return (
          <div>
            <a style={{margin: '0.25em'}}>add custom...</a>
            <a style={{margin: '0.25em'}}>modify custom...</a>
            <a style={{margin: '0.25em'}}>clear custom</a>
          </div>
        );
    }
}

const arrElControlsContainer = document.getElementsByClassName('robots-whiz--td--checkboxes');
if (arrElControlsContainer) {
  for (let i = 0; i < arrElControlsContainer.length; i++) {
    const elContainer = document.createElement('div');
    arrElControlsContainer[i].appendChild(elContainer);
    ReactDOM.render(<Controls/>, elContainer);
  }
}
