/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./js/gitenberg-sidebar.js ***!
  \*********************************/
(function (wp) {
  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var SelectControl = wp.components.SelectControl;
  var withSelect = wp.data.withSelect;
  var withDispatch = wp.data.withDispatch;
  var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
  var GitenbergFileSelector = function (props) {
    const markdownFiles = window.gitenbergData.markdownFiles || [];
    const meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
    const onUpdateMeta = props.onUpdateMeta;
    const {
      gitenberg_linked_markdown_file: selectedFile
    } = meta;
    return el(PluginDocumentSettingPanel, {
      name: 'gitenberg-file-selector',
      title: 'Gitenberg Markdown Files',
      className: 'gitenberg-file-selector'
    }, el(SelectControl, {
      label: __('Select a Markdown file'),
      value: selectedFile,
      options: [{
        label: 'None',
        value: ''
      }].concat(markdownFiles.map(function (file) {
        return {
          label: file.name,
          value: file.path
        };
      })),
      onChange: function (value) {
        onUpdateMeta(value);
      }
    }));
  };
  var mapStateToProps = function (select) {
    var postMeta = select('core/editor').getEditedPostAttribute('meta');
    return {
      selectedFile: postMeta ? postMeta['gitenberg_linked_markdown_file'] : ''
    };
  };
  var mapDispatchToProps = function (dispatch) {
    return {
      onUpdateMeta: function (markdownFilePath) {
        // TODO: Completely wiping out the original content is temporary for demonstration purposes.
        // Ideally the plugin would capture the post content when a user first selects a markdown file, and return that content if they select "None".

        if ('' === markdownFilePath) {
          // "None"
          dispatch('core/editor').editPost({
            content: ''
          });
        }
        dispatch('core/editor').editPost({
          meta: {
            gitenberg_linked_markdown_file: markdownFilePath
          }
        });
        dispatch('core/editor').savePost();
      }
    };
  };
  var GitenbergFileSelectorWithData = withSelect(mapStateToProps)(withDispatch(mapDispatchToProps)(GitenbergFileSelector));
  wp.plugins.registerPlugin('gitenberg-file-selector-plugin', {
    render: GitenbergFileSelectorWithData,
    icon: 'book' // Optional: add an appropriate icon
  });
})(window.wp);
/******/ })()
;
//# sourceMappingURL=gitenberg-sidebar.js.map