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
import { createStore } from 'redux';
import { Provider, connect } from 'react-redux';

console.log("JSX entry logic.");


const ACTION__ADD_POST      = 'ADD_POST',
      ACTION__EXCLUDE_TOKEN = 'EXCLUDE_TOKEN',
      ACTION__INCLUDE_TOKEN = 'INCLUDE_TOKEN',
      ARR_EMPTY             = [],
      ARR_TOKENS_STANDARD   = ['noindex', 'nofollow', 'noarchive', 'noimageindex'];


const createActionToAddPost = (post) => {
          return {type:     ACTION__ADD_POST,
                  post};
        },
      createActionToExcludeToken = (post_id, strToken) => {
          return {type:     ACTION__EXCLUDE_TOKEN,
                  post_id:  post_id,
                  token:    strToken};
        },
      createActionToIncludeToken = (post_id, strToken) => {
          return {type:     ACTION__INCLUDE_TOKEN,
                  post_id:  post_id,
                  token:    strToken};
        };


const processActionAddPost = (state = {}, action) => {
    const { post } = action;

    const arrTokensInitial = post.data &&
                             (typeof post.data == 'string') &&
                             post.data.split(/\s+/) || ARR_EMPTY,
          stateNew = {...state};

    stateNew[post.id] = {id:         post.id,
                         link_edit:  post.link_edit,
                         name:       post.name,
                         type:       post.type,
                         template:   post.template,
                         status:     post.status,
                         data:       arrTokensInitial};

    return stateNew;
  };


const reducer = (state = {}, action) => {
          switch(action.type) {
            case ACTION__ADD_POST:
              return processActionAddPost(state, action);

            case ACTION__EXCLUDE_TOKEN:
            case ACTION__INCLUDE_TOKEN:

              const { post_id, token } = action;

              const arrTokensOld = state[post_id] && state[post_id].data || ARR_EMPTY,
                    stateNew = {...state};

              if (action.type == ACTION__EXCLUDE_TOKEN) {
                var indexToken = arrTokensOld.indexOf(token);
                if (indexToken != -1) {
                  stateNew[post_id] = {
                                        ...state[post_id],
                                        data: arrTokensOld.slice(0, indexToken)
                                                          .concat(arrTokensOld
                                                                      .slice(indexToken + 1,
                                                                             arrTokensOld.length))
                                      };
                }
              } else if (action.type == ACTION__INCLUDE_TOKEN) {
                if (arrTokensOld.indexOf(token) == -1) {
                  stateNew[post_id] = {
                                        ...state[post_id],
                                        data: [...arrTokensOld, token].sort()
                                      };
                }
              }

              return stateNew;
          }

          return state;
        };


const store = createStore(reducer,
                          window.devToolsExtension && window.devToolsExtension());


const mapDispatchToProps_Post = (dispatch, ownProps) => ({
          excludeToken: (strToken) => dispatch(createActionToExcludeToken(ownProps.post_id,
                                                                          strToken)),
          includeToken: (strToken) => dispatch(createActionToIncludeToken(ownProps.post_id,
                                                                          strToken))
        });


const mapStateToProps_Post = (state, propsIn) => {
          let propsOut = {
              post_tokens: state[propsIn.post_id] &&
                           state[propsIn.post_id].data || ARR_EMPTY
            };

          propsOut.isIncluded = (strToken) => (propsOut.post_tokens.indexOf(strToken) >= 0);

          return propsOut;
        };


let mapStrings = null;


class TrHeader extends React.Component {
  render() {
    return (
        <tr>
          <th className='robots-whiz--column-header'>{ mapStrings.strColumnID }</th>
          <th className='robots-whiz--column-header'>{ mapStrings.strColumnName }</th>
          <th className='robots-whiz--column-header'>{ mapStrings.strColumnType }</th>
          <th className='robots-whiz--column-header'>{ mapStrings.strColumnTemplate }</th>
          <th className='robots-whiz--column-header'>{ mapStrings.strColumnStatus }</th>
        </tr>);
  }
}


class Readout extends React.Component {
  render() {
      return (
          <div className='robots-whiz--readout'>
            { this.props.post_tokens.length == 0
                ? '\u00A0'
                : "<meta name=\"robots\" content=\"" + this.props
                                                              .post_tokens
                                                                .join(", ")
                                                                .replace(/\\/g, "\\\\")
                                                                .replace(/"/g, "\\\"")
                                                     + "\">" }
          </div>);
    }
}

Readout.propTypes = {
    post_id:                  React.PropTypes.number.isRequired,
    post_tokens:              React.PropTypes.arrayOf(React.PropTypes.string).isRequired
  };

Readout = connect(mapStateToProps_Post)(Readout);


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
    for (let strName of ARR_TOKENS_STANDARD) {
      if (!this.props.isIncluded(strName)) return false;
    }
    return true;
  }

  includeAll() {
    for (let strName of ARR_TOKENS_STANDARD) {
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
                                      }
                                    }} />
      );
  }
}

CheckboxNonStdAll.propTypes = {
    //  Data:
    post_id:                  React.PropTypes.number.isRequired,

    //  Functions:
    includeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired
  };

CheckboxNonStdAll = connect(mapStateToProps_Post, mapDispatchToProps_Post)(CheckboxNonStdAll);


class CheckboxNonStdNone extends React.Component {
  areAllExcluded() {
    for (let strName of ARR_TOKENS_STANDARD) {
      if (this.props.isIncluded(strName)) return false;
    }
    return true;
  }

  excludeAll() {
    for (let strName of ARR_TOKENS_STANDARD) {
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
                                      }
                                    }} />
      );
  }
}

CheckboxNonStdNone.propTypes = {
    //  Data:
    post_id:                  React.PropTypes.number.isRequired,

    //  Functions:
    excludeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired
  };

CheckboxNonStdNone = connect(mapStateToProps_Post, mapDispatchToProps_Post)(CheckboxNonStdNone);


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
                              }} />
      );
  }
}

CheckboxStd.propTypes = {
    //  Data:
    post_id:                  React.PropTypes.number.isRequired,
    token:                    React.PropTypes.string.isRequired,

    //  Functions:
    excludeToken:             React.PropTypes.func.isRequired,
    includeToken:             React.PropTypes.func.isRequired,
    isIncluded:               React.PropTypes.func.isRequired
  };

CheckboxStd = connect(mapStateToProps_Post, mapDispatchToProps_Post)(CheckboxStd);


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


class HiddenDataField extends React.Component {
  render() {
    return (
        <input type='hidden'
               name={ 'post_' + this.props.post_id }
               value={ JSON.stringify({'robots': this.props.post_tokens.join(" ")}) } />
      );
  }
}

HiddenDataField.propTypes = {
    post_id:                  React.PropTypes.number.isRequired,
    post_tokens:              React.PropTypes.arrayOf(React.PropTypes.string).isRequired
  };

HiddenDataField = connect(mapStateToProps_Post)(HiddenDataField);


class CheckboxRow extends React.Component {
  render() {
    return (
        <div>
          <CheckboxNonStdAll post_id={ this.props.post_id } />
          <CheckboxNonStdNone post_id={ this.props.post_id } />
          <CheckboxStd post_id={ this.props.post_id }
                       token='noindex' />
          <CheckboxStd post_id={ this.props.post_id }
                       token='nofollow' />
          <CheckboxStd post_id={ this.props.post_id }
                       token='noarchive' />
          <CheckboxStd post_id={ this.props.post_id }
                       token='noimageindex' />
        </div>
      );
  }
}

CheckboxRow.propTypes = {
    //  Data:
    post_id:                  React.PropTypes.number.isRequired
  };


class CustomRow extends React.Component {

  clearCustom() {
    let arrTokensPrev = this.getArrTokensNonStandard();
    if (arrTokensPrev.length == 0) return;

    if (!window.confirm(mapStrings.strConfirmCustomClear)) return;

    arrTokensPrev.map(strToken => {
        this.props.excludeToken(strToken);
      });
  }

  getArrTokensNonStandard() {
    const isTokenNonStandard = strToken => !ARR_TOKENS_STANDARD.includes(strToken);

    let arrTokensNonStandard = [];

    this.props.post_tokens.map(strToken => {
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
    post_id:                  React.PropTypes.number.isRequired,
    post_tokens:              React.PropTypes.arrayOf(React.PropTypes.string).isRequired,

    //  Functions:
    excludeToken:             React.PropTypes.func.isRequired,
    includeToken:             React.PropTypes.func.isRequired
  };

CustomRow = connect(mapStateToProps_Post, mapDispatchToProps_Post)(CustomRow);


class TrData extends React.Component {
  render() {
    return (
        <tr className={ 'robots-whiz--2nd-row' + ((this.props.indexRow % 2 == 0)
                                                                ? ' robots-whiz--odd-row'
                                                                : "") }>
          <td><a href={ this.props.post.link_edit }>{ this.props.post.id }</a></td>
          <td><a href={ this.props.post.link_edit }>{ this.props.post.name }</a></td>
          <td>{ this.props.post.type }</td>
          <td>{ this.props.post.template }</td>
          <td>{ this.props.post.status }</td>
        </tr>
      );
  }
}

TrData.propTypes = {

    //  Data:
    indexRow:                 React.PropTypes.number.isRequired,
    post:                     React.PropTypes.shape({
      id:                         React.PropTypes.number.isRequired,
      link_edit:                  React.PropTypes.string.isRequired,
      name:                       React.PropTypes.string.isRequired,
      type:                       React.PropTypes.string.isRequired,
      template:                   React.PropTypes.oneOfType([React.PropTypes.bool,
                                                             React.PropTypes.string]).isRequired,
      status:                     React.PropTypes.string.isRequired
                                }).isRequired
  };


class TrControls extends React.Component {
  render() {
    return (
        <tr className={ 'robots-whiz--2nd-row' + ((this.props.indexRow % 2 == 0)
                                                                ? ' robots-whiz--odd-row'
                                                                : "") }>
          <td colSpan='5' className='robots-whiz--td--checkboxes'>
            <Readout post_id            ={ this.props.post.id } />
            <CheckboxRow post_id        ={ this.props.post.id } />
            <CustomRow post_id          ={ this.props.post.id } />
            <HiddenDataField post_id    ={ this.props.post.id } />
          </td>
        </tr>
      );
  }
}

TrControls.propTypes = TrData.propTypes;


class TableOfPosts extends React.Component {
  render() {
    return (
        <table className='robots-whiz--table'>
          <thead><TrHeader /></thead>
          { this.props.posts.map((objPost, i) => {
                return (
                    <tbody key={ objPost.id }>
                      <TrData indexRow={ i } post={ objPost } />
                      <TrControls indexRow={ i } post={ objPost } />
                    </tbody>
                  );
              }) }
        </table>
      );
  }
}

TableOfPosts.propTypes = {
    posts:                    React.PropTypes.arrayOf(TrData.propTypes.post).isRequired
  };


window._plugin_RobotsWhiz__renderTable = function(elContainer, arrPosts) {
    arrPosts && arrPosts.map(objPost => {
        store.dispatch(createActionToAddPost(objPost));
      });

    ReactDOM.render(<Provider store={ store }><TableOfPosts posts={ arrPosts } /></Provider>,
                    elContainer);
  };

window._plugin_RobotsWhiz__setStrings = function(mapStringsSet) {
    mapStrings = mapStringsSet;
  };
