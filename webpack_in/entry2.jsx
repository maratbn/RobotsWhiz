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


const arrTokensStandard = ['noindex', 'nofollow', 'noarchive', 'noimageindex'];


let mapStrings = null;


class Readout extends React.Component {
  render() {
      return (
          <div className='robots-whiz--readout'>
            { this.props.arrTokens.length == 0
                ? '\u00A0'
                : "<meta name=\"robots\" content=\"" + this.props.arrTokens.join(", ")
                                                                           .replace(/\\/g, "\\\\")
                                                                           .replace(/"/g, "\\\"")
                                                     + "\">" }
          </div>);
    }
}

Readout.propTypes = {
    arrTokens:                React.PropTypes.array.isRequired
  };


class Checkbox extends React.Component {
  render() {
      return (
          <label>
            <input type='checkbox'
                   checked={ this.props.is_checked }
                   data-robots-whiz--role={ this.props.role }
                   name={ this.props.name }
                   onChange={ (event) => {
                                if (this.props.on_change) {
                                  this.props.on_change({ checked: event.target.checked });
                                }
                              } } />
            { this.props.is_italic ? (<i>{ this.props.label }</i>) : this.props.label }
          </label>
        );
    }
}

Checkbox.propTypes = {
    role:                     React.PropTypes.string.isRequired,
    name:                     React.PropTypes.string,
    label:                    React.PropTypes.string.isRequired,
    is_italic:                React.PropTypes.bool.isRequired,
    is_checked:               React.PropTypes.bool,
    on_change:                React.PropTypes.func
  };


class CheckboxNonStd extends React.Component {
  render() {
    return (
        <Checkbox is_italic={ true }
                  is_checked={ this.props.is_checked }
                  role={ this.props.role }
                  label={ this.props.label }
                  on_change={ this.props.on_change } />
      );
  }
}

CheckboxNonStd.propTypes = {
    role:                     React.PropTypes.string.isRequired,
    label:                    React.PropTypes.string.isRequired,
    is_checked:               React.PropTypes.bool,
    on_change:                React.PropTypes.func
  };


class CheckboxNonStdAll extends React.Component {
  areAllIncluded() {
    for (let strName of arrTokensStandard) {
      if (!this.props.isIncluded(strName)) return false;
    }
    return true;
  }

  includeAll() {
    for (let strName of arrTokensStandard) {
      this.props.includeToken(strName);
    }
  }

  render() {
    return (
        <CheckboxNonStd role='cb-all'
                        label='all'
                        is_checked={ this.areAllIncluded() }
                        on_change={ (event) => {
                                      if (event.checked) {
                                        this.includeAll();
                                        this.props.updateReadout();
                                      }
                                    }} />
      );
  }
}

CheckboxNonStdAll.propTypes = {
    includeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired
  };


class CheckboxNonStdNone extends React.Component {
  areAllExcluded() {
    for (let strName of arrTokensStandard) {
      if (this.props.isIncluded(strName)) return false;
    }
    return true;
  }

  excludeAll() {
    for (let strName of arrTokensStandard) {
      this.props.excludeToken(strName);
    }
  }

  render() {
    return (
        <CheckboxNonStd role='cb-none'
                        label='none'
                        is_checked={ this.areAllExcluded() }
                        on_change={ (event) => {
                                      if (event.checked) {
                                        this.excludeAll();
                                        this.props.updateReadout();
                                      }
                                    }} />
      );
  }
}

CheckboxNonStdNone.propTypes = {
    excludeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired
  };


class CheckboxStd extends React.Component {
  render() {
    return (
        <Checkbox is_italic={ false }
                  is_checked={ this.props.isIncluded(this.props.token) }
                  role='cb-std'
                  label={ this.props.token }
                  name={ this.props.token }
                  on_change={ (event) => {
                                if (event.checked) {
                                  this.props.includeToken(this.props.token);
                                } else {
                                  this.props.excludeToken(this.props.token);
                                }
                                this.props.updateReadout();
                              }} />
      );
  }
}

CheckboxStd.propTypes = {
    excludeToken:             React.PropTypes.func.isRequired,
    includeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired,
    token:                    React.PropTypes.string.isRequired
  };


class Link extends React.Component {
  render() {
      return (
          <label>
            <a className='robots-whiz--td--link' style={{margin: '0.25em'}}
                onClick={event => { event.preventDefault();
                                    this.props.callback_click();
                                  }}>{ this.props.label }</a>
          </label>
        );
    }
}

Link.propTypes = {
    callback_click:           React.PropTypes.func.isRequired,
    label:                    React.PropTypes.string.isRequired
  };


class CheckboxRow extends React.Component {
  constructor(props) {
    super(props);

    this.isIncluded = (strToken) => (this.props.arrTokens.indexOf(strToken) >= 0);
  }

  render() {
    return (
        <div>
          <CheckboxNonStdAll includeToken={ this.props.includeToken }
                             isIncluded={ this.isIncluded }
                             updateReadout={ this.props.updateReadout } />
          <CheckboxNonStdNone excludeToken={ this.props.excludeToken }
                              isIncluded={ this.isIncluded }
                              updateReadout={ this.props.updateReadout } />
          <CheckboxStd token='noindex'
                       excludeToken={ this.props.excludeToken }
                       includeToken={ this.props.includeToken }
                       isIncluded={ this.isIncluded }
                       updateReadout={ this.props.updateReadout } />
          <CheckboxStd token='nofollow'
                       excludeToken={ this.props.excludeToken }
                       includeToken={ this.props.includeToken }
                       isIncluded={ this.isIncluded }
                       updateReadout={ this.props.updateReadout } />
          <CheckboxStd token='noarchive'
                       excludeToken={ this.props.excludeToken }
                       includeToken={ this.props.includeToken }
                       isIncluded={ this.isIncluded }
                       updateReadout={ this.props.updateReadout } />
          <CheckboxStd token='noimageindex'
                       excludeToken={ this.props.excludeToken }
                       includeToken={ this.props.includeToken }
                       isIncluded={ this.isIncluded }
                       updateReadout={ this.props.updateReadout } />
        </div>
      );
  }
}

CheckboxRow.propTypes = {
    arrTokens:                React.PropTypes.array.isRequired,
    excludeToken:             React.PropTypes.func.isRequired,
    includeToken:             React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired
  };


class CustomRow extends React.Component {

  clearCustom() {
    let arrTokensPrev = this.getArrTokensNonStandard();
    if (arrTokensPrev.length == 0) return;

    if (!window.confirm(mapStrings.strConfirmCustomClear)) return;

    arrTokensPrev.map(strToken => {
        this.props.excludeToken(strToken);
      });

    this.props.updateReadout();
  }

  getArrTokensNonStandard() {
    const isTokenNonStandard = strToken => !arrTokensStandard.includes(strToken);

    let arrTokensNonStandard = [];

    this.props.arrTokens.map(strToken => {
        if (isTokenNonStandard(strToken)) {
          arrTokensNonStandard.push(strToken);
        }
      });

    return arrTokensNonStandard;
  }

  modifyCustom() {
    var arrTokensPrev = this.getArrTokensNonStandard();

    var strTokens = window.prompt(mapStrings.strPromptCustomContent,
                                  arrTokensPrev.join(" "));
    if (strTokens == null) return;

    var arrTokensNew = strTokens.split(/\s+/);
    if (!arrTokensNew) return;

    arrTokensNew.map(strCustomToken => {
        if (!strCustomToken) return;

        this.props.includeToken(strCustomToken);
      });

    arrTokensPrev.map(strTokenPrev => {
        if (arrTokensNew.indexOf(strTokenPrev) >= 0) return;

        this.props.excludeToken(strTokenPrev);
      });

    this.props.updateReadout();
  }

  render() {
    if (this.getArrTokensNonStandard() == 0) {
      return (
          <div>
            <Link callback_click={() => { this.modifyCustom(); }}
                  label={ mapStrings.strLabelAddCustom } />
          </div>
        );
    } else {
      return (
          <div>
            <Link callback_click={() => { this.modifyCustom(); }}
                  label={ mapStrings.strLabelModifyCustom } />
            <Link callback_click={() => { this.clearCustom(); }}
                  label={ mapStrings.strLabelClearCustom } />
          </div>
        );
    }
  }
}

CustomRow.propTypes = {

    //  Data:
    arrTokens:                React.PropTypes.array.isRequired,

    //  Functions:
    excludeToken:             React.PropTypes.func.isRequired,
    includeToken:             React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired
  };


class Rows extends React.Component {
  constructor(props) {
    super(props);

    this.excludeToken = (strToken) => {
        const indexToken = this.props.arrTokens.indexOf(strToken);
        if (indexToken >= 0) this.props.arrTokens.splice(indexToken, 1);
      };
  }

  render() {
    return (
        <div>
          <Readout arrTokens              ={ this.props.arrTokens } />
          <CheckboxRow arrTokens          ={ this.props.arrTokens }
                       excludeToken       ={ this.excludeToken }
                       includeToken       ={ this.props.includeToken }
                       updateReadout      ={ this.props.updateReadout } />
          <CustomRow arrTokens            ={ this.props.arrTokens }
                     excludeToken         ={ this.excludeToken }
                     includeToken         ={ this.props.includeToken }
                     updateReadout        ={ this.props.updateReadout } />
        </div>
      );
  }
}

Rows.propTypes = {

    //  Data:
    arrTokens:                React.PropTypes.array.isRequired,

    //  Functions:
    includeToken:             React.PropTypes.func.isRequired,
    updateReadout:            React.PropTypes.func.isRequired
  };


window._plugin_RobotsWhiz__renderControls = function(tdCheckboxes,
                                                     objData) {

    const arrTokens = [];

    function includeToken(strToken) {
      if (arrTokens.indexOf(strToken) >= 0) return;
      arrTokens.push(strToken);
    }

    const $inputData = objData.$inputData;

    const strJSONTokensInitial = $inputData.val();
    const dataTokensInitial = strJSONTokensInitial &&
                              window.JSON.parse(strJSONTokensInitial);
    const strTokensInitial = dataTokensInitial && dataTokensInitial['robots'];
    const arrTokensInitial = strTokensInitial && strTokensInitial.split(/\s+/) || [];

    arrTokensInitial.map(strToken => {
        includeToken(strToken);
      });

    let rows = null;

    function updateReadout() {
      arrTokens.sort();

      $inputData.val(window.JSON.stringify({'robots': arrTokens.join(" ")}));
      if (rows) rows.forceUpdate();
    }

    updateReadout();


    const elContainerRows = document.createElement('div');
    tdCheckboxes.appendChild(elContainerRows);
    ReactDOM.render(<Rows arrTokens                     ={ arrTokens }
                          includeToken                  ={ includeToken }
                          updateReadout                 ={ updateReadout }
                          ref                           ={ (rowsNew) => {
                                                              rows = rowsNew;
                                                            }} />,
                    elContainerRows);
  };

window._plugin_RobotsWhiz__setStrings = function(mapStringsSet) {
    mapStrings = mapStringsSet;
  };
