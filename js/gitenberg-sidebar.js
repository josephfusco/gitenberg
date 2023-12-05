( function( wp ) {
    var __ = wp.i18n.__;
    var el = wp.element.createElement;
    var SelectControl = wp.components.SelectControl;
    var withSelect = wp.data.withSelect;
    var withDispatch = wp.data.withDispatch;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;

    var GitenbergFileSelector = function( props ) {
        var markdownFiles = window.gitenbergData.markdownFiles || [];
        var linkedMarkdownFile = window.gitenbergData.linkedMarkdownFile;
        var onUpdateMeta = props.onUpdateMeta;

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'gitenberg-file-selector',
                title: 'Gitenberg',
                className: 'gitenberg-file-selector'
            },
            el(
                SelectControl,
                {
                    label: __('Select a Markdown file'),
                    value: linkedMarkdownFile || props.selectedFile,
                    options: [{ label: 'Select...', value: '' }].concat(
                        markdownFiles.map( function( file ) {
                            return {
                                label: file.name,
                                value: file.path
                            };
                        })
                    ),
                    onChange: onUpdateMeta
                }
            )
        );
    };

    var mapStateToProps = function( select ) {
        var postMeta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
        return {
            selectedFile: postMeta ? postMeta['_gitenberg_selected_file'] : ''
        };
    };

    var mapDispatchToProps = function( dispatch ) {
        return {
            onUpdateMeta: function( markdownFilePath ) {
                dispatch( 'core/editor' ).editPost( { meta: { _gitenberg_selected_file: markdownFilePath } } );
            }
        };
    };

    var GitenbergFileSelectorWithData = withSelect( mapStateToProps )( withDispatch( mapDispatchToProps )( GitenbergFileSelector ) );

    wp.plugins.registerPlugin( 'gitenberg-file-selector-plugin', {
        render: GitenbergFileSelectorWithData,
        icon: 'book' // Optional: add an appropriate icon
    });
} )( window.wp );