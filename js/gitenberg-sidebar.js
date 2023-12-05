( function( wp ) {
    var __ = wp.i18n.__;
    var el = wp.element.createElement;
    var SelectControl = wp.components.SelectControl;
    var withSelect = wp.data.withSelect;
    var withDispatch = wp.data.withDispatch;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;

    var GitenbergFileSelector = function( props ) {
        var markdownFiles = props.markdownFiles;
        var onUpdateMeta = props.onUpdateMeta;

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'gitenberg-file-selector',
                title: 'Gitenberg Markdown Files',
                className: 'gitenberg-file-selector'
            },
            el(
                SelectControl,
                {
                    label: __('Select a Markdown file'),
                    value: props.selectedFile,
                    options: [{ label: 'Select...', value: '' }].concat(
                        markdownFiles.map( function( file ) {
                            return { label: file.name, value: file.download_url };
                        })
                    ),
                    onChange: onUpdateMeta
                }
            )
        );
    };

    var mapStateToProps = function( select ) {
        return {
            markdownFiles: select( 'core' ).getEntityRecords( 'postType', 'markdown_file' ) || [],
            selectedFile: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_gitenberg_selected_file']
        };
    };

    var mapDispatchToProps = function( dispatch ) {
        return {
            onUpdateMeta: function( markdownFileUrl ) {
                dispatch( 'core/editor' ).editPost( { meta: { _gitenberg_selected_file: markdownFileUrl } } );
            }
        };
    };

    var GitenbergFileSelectorWithData = withSelect( mapStateToProps )( withDispatch( mapDispatchToProps )( GitenbergFileSelector ) );

    wp.plugins.registerPlugin( 'gitenberg-file-selector-plugin', {
        render: GitenbergFileSelectorWithData,
        icon: 'book' // Optional: add an appropriate icon
    });
} )( window.wp );