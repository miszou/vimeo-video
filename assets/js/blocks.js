( function ( blocks, element, components, blockEditor, serverSideRender ) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var SelectControl = components.SelectControl;
    var ServerSideRender = serverSideRender.default || serverSideRender;

    function numberValue( value ) {
        var parsed = parseInt( value, 10 );
        return isNaN( parsed ) ? 0 : parsed;
    }

    blocks.registerBlockType( 'mfvv/video', {
        title: 'Single Vimeo Video',
        description: 'Embed one Vimeo Video post by ID.',
        icon: 'video-alt3',
        category: 'embed',
        edit: function ( props ) {
            var attrs = props.attributes;
            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Video settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Video post ID',
                            type: 'number',
                            value: attrs.id || '',
                            onChange: function ( value ) {
                                props.setAttributes( { id: numberValue( value ) } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Autoplay',
                            checked: !! attrs.autoplay,
                            onChange: function ( value ) {
                                props.setAttributes( { autoplay: value } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Loop',
                            checked: !! attrs.loop,
                            onChange: function ( value ) {
                                props.setAttributes( { loop: value } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Muted',
                            checked: !! attrs.muted,
                            onChange: function ( value ) {
                                props.setAttributes( { muted: value } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Show controls',
                            checked: attrs.controls !== false,
                            onChange: function ( value ) {
                                props.setAttributes( { controls: value } );
                            },
                        } )
                    ),
                    el(
                        PanelBody,
                        { title: 'Dimensions', initialOpen: false },
                        el( TextControl, {
                            label: 'Width',
                            help: 'Use pixels or CSS units, for example 640, 640px, or 100%.',
                            value: attrs.width || '',
                            onChange: function ( value ) {
                                props.setAttributes( { width: value } );
                            },
                        } ),
                        el( TextControl, {
                            label: 'Height',
                            help: 'Use pixels or CSS units, for example 360, 360px, or 50vh.',
                            value: attrs.height || '',
                            onChange: function ( value ) {
                                props.setAttributes( { height: value } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Stretch to full width',
                            help: 'Overrides the width setting and makes the video fill its container.',
                            checked: !! attrs.stretch,
                            onChange: function ( value ) {
                                props.setAttributes( { stretch: value } );
                            },
                        } )
                    )
                ),
                el( ServerSideRender, { block: 'mfvv/video', attributes: attrs } )
            );
        },
        save: function () {
            return null;
        },
    } );

    blocks.registerBlockType( 'mfvv/gallery', {
        title: 'Vimeo Video Gallery',
        description: 'Display a query of Vimeo Video posts, optionally filtered by Media Tag slug.',
        icon: 'grid-view',
        category: 'widgets',
        edit: function ( props ) {
            var attrs = props.attributes;
            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Gallery settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Media Tag slug',
                            help: 'Leave blank to show recent videos.',
                            value: attrs.tag || '',
                            onChange: function ( value ) {
                                props.setAttributes( { tag: value } );
                            },
                        } ),
                        el( RangeControl, {
                            label: 'Number of videos',
                            value: attrs.posts_per_page || 6,
                            min: 1,
                            max: 24,
                            onChange: function ( value ) {
                                props.setAttributes( { posts_per_page: value } );
                            },
                        } ),
                        el( RangeControl, {
                            label: 'Columns',
                            value: attrs.columns || 3,
                            min: 1,
                            max: 6,
                            onChange: function ( value ) {
                                props.setAttributes( { columns: value } );
                            },
                        } ),
                        el( SelectControl, {
                            label: 'Order by',
                            value: attrs.orderby || 'date',
                            options: [
                                { label: 'Date', value: 'date' },
                                { label: 'Title', value: 'title' },
                                { label: 'Menu order', value: 'menu_order' },
                                { label: 'Random', value: 'rand' },
                            ],
                            onChange: function ( value ) {
                                props.setAttributes( { orderby: value } );
                            },
                        } ),
                        el( SelectControl, {
                            label: 'Order',
                            value: attrs.order || 'DESC',
                            options: [
                                { label: 'Descending', value: 'DESC' },
                                { label: 'Ascending', value: 'ASC' },
                            ],
                            onChange: function ( value ) {
                                props.setAttributes( { order: value } );
                            },
                        } )
                    )
                ),
                el( ServerSideRender, { block: 'mfvv/gallery', attributes: attrs } )
            );
        },
        save: function () {
            return null;
        },
    } );

    blocks.registerBlockType( 'mfvv/related-videos', {
        title: 'Related Vimeo Videos',
        description: 'Display videos that share Media Tags with the current or selected Vimeo Video post.',
        icon: 'playlist-video',
        category: 'widgets',
        edit: function ( props ) {
            var attrs = props.attributes;
            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Related videos settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Source video post ID',
                            help: 'Leave blank on a single video page to use the current video.',
                            type: 'number',
                            value: attrs.id || '',
                            onChange: function ( value ) {
                                props.setAttributes( { id: numberValue( value ) } );
                            },
                        } ),
                        el( RangeControl, {
                            label: 'Number of videos',
                            value: attrs.posts_per_page || 4,
                            min: 1,
                            max: 12,
                            onChange: function ( value ) {
                                props.setAttributes( { posts_per_page: value } );
                            },
                        } ),
                        el( RangeControl, {
                            label: 'Columns',
                            value: attrs.columns || 4,
                            min: 1,
                            max: 6,
                            onChange: function ( value ) {
                                props.setAttributes( { columns: value } );
                            },
                        } )
                    )
                ),
                el( ServerSideRender, { block: 'mfvv/related-videos', attributes: attrs } )
            );
        },
        save: function () {
            return null;
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
    window.wp.serverSideRender
);
