( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var Placeholder = wp.components.Placeholder;
	var Spinner = wp.components.Spinner;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'logo-showcase/showcase', {
		edit: function ( props ) {
			var id = props.attributes.showcaseId;
			var state = useState( null );
			var items = state[ 0 ];
			var setItems = state[ 1 ];
			useEffect( function () {
				wp.apiFetch( { path: '/logo-showcase/v1/showcases' } ).then( function ( r ) {
					setItems( r.items );
				} ).catch( function () {
					setItems( [] );
				} );
			}, [] );

			var options = [ { label: __( 'Select a showcase', 'logo-showcase' ), value: 0 } ].concat(
				( items || [] ).map( function ( i ) {
					return { label: i.title + ' (#' + i.id + ')', value: i.id };
				} )
			);
			var picker = el( SelectControl, {
				label: __( 'Showcase', 'logo-showcase' ),
				value: id,
				options: options,
				onChange: function ( v ) {
					props.setAttributes( { showcaseId: parseInt( v, 10 ) || 0 } );
				},
			} );
			var body;
			if ( items === null ) {
				body = el( Placeholder, { label: 'Logo Showcase' }, el( Spinner ) );
			} else if ( ! id ) {
				body = el( Placeholder, { icon: 'images-alt2', label: 'Logo Showcase', instructions: __( 'Choose a showcase to display.', 'logo-showcase' ) }, picker,
					el( 'a', { href: 'admin.php?page=logo-showcase#/new', target: '_blank', rel: 'noopener' }, __( 'Create a new showcase', 'logo-showcase' ) ) );
			} else {
				body = el( ServerSideRender, { block: 'logo-showcase/showcase', attributes: { showcaseId: id } } );
			}
			return el( 'div', useBlockProps(), el( InspectorControls, null, el( PanelBody, { title: __( 'Showcase', 'logo-showcase' ) }, picker,
				id ? el( 'a', { href: 'admin.php?page=logo-showcase#/edit/' + id, target: '_blank', rel: 'noopener' }, __( 'Edit this showcase', 'logo-showcase' ) ) : null ) ), body );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
