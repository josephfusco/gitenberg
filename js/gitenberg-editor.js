(function(wp) {
    // const remoteContent = gitenbergData.remoteContent;
    // const remoteContent = [
    //     {
    //         "blockName": "core/paragraph",
    //         "attrs": [],
    //         "innerBlocks": [],
    //         "innerHTML": "\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n",
    //         "innerContent": [
    //             "\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n"
    //         ]
    //     },
    //     {
    //         "blockName": "core/image",
    //         "attrs": [],
    //         "innerBlocks": [],
    //         "innerHTML": "\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n",
    //         "innerContent": [
    //             "\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n"
    //         ]
    //     },
    //     {
    //         "blockName": "core/paragraph",
    //         "attrs": [],
    //         "innerBlocks": [],
    //         "innerHTML": "\n<p>lorem ipsum</p>\n",
    //         "innerContent": [
    //             "\n<p>lorem ipsum</p>\n"
    //         ]
    //     },
    //     {
    //         "blockName": "core/heading",
    //         "attrs": {
    //             "style": {
    //                 "elements": {
    //                     "link": {
    //                         "color": {
    //                             "text": "var:preset|color|vivid-red"
    //                         }
    //                     }
    //                 }
    //             },
    //             "textColor": "vivid-red"
    //         },
    //         "innerBlocks": [],
    //         "innerHTML": "\n<h2 class=\"wp-block-heading has-vivid-red-color has-text-color has-link-color\">Heading</h2>\n",
    //         "innerContent": [
    //             "\n<h2 class=\"wp-block-heading has-vivid-red-color has-text-color has-link-color\">Heading</h2>\n"
    //         ]
    //     }
    // ];

    const remoteContent = `<!-- wp:paragraph -->
    <p>INJECTED</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p>paragraph in WordPress</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p>REMOTEE</p>
    <!-- /wp:paragraph -->`;

    wp.domReady( () => {
        wp.data.dispatch( 'core/block-editor' ).insertBlocks( wp.blocks.parse( remoteContent ) );
    });
})(window.wp);