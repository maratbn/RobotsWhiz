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

class Control extends React.Component {
  render() {
      return (
          <label>
            <a className='robots-whiz--td--link' style={{margin: '0.25em'}}
                onClick={event => { event.preventDefault();
                                    this.props.callback_click();
                                  }}>{this.props.label}</a>
          </label>
        );
    }
}

Control.propTypes = {
    callback_click:   React.PropTypes.func.isRequired,
    label:            React.PropTypes.string.isRequired
  };

class Controls extends React.Component {
  render() {
    if (this.props.getArrTokensNonStandard() == 0) {
      return (
          <div>
            <Control callback_click={() => { this.props.modifyCustom(); }}
                     label={ this.props.labelAddCustom } />
          </div>
        );
    } else {
      return (
          <div>
            <Control callback_click={() => { this.props.modifyCustom(); }}
                     label="modify custom..." />
            <Control callback_click={() => { this.props.clearCustom(); }}
                     label="clear custom" />
          </div>
        );
    }
  }
}

Controls.propTypes = {
    clearCustom:              React.PropTypes.func.isRequired,
    getArrTokensNonStandard:  React.PropTypes.func.isRequired,
    modifyCustom:             React.PropTypes.func.isRequired
  };


window._plugin_RobotsWhiz__renderControls = function(tdCheckboxes, objAPI) {
    const elContainer = document.createElement('div');
    tdCheckboxes.appendChild(elContainer);
    ReactDOM.render(<Controls clearCustom              ={objAPI.clearCustom}
                              getArrTokensNonStandard  ={objAPI.getArrTokensNonStandard}
                              labelAddCustom           ={objAPI.strLabelAddCustom}
                              modifyCustom             ={objAPI.modifyCustom}
                              ref                      ={(controls) =>
                                                            objAPI.callbackControls(controls)} />,
                    elContainer);
  };
