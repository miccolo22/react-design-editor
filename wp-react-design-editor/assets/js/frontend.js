( function () {
    if ( window.wp && window.wp.i18n ) {
        const { __ } = window.wp.i18n;
        console.info( __( 'React Design Editor frontend assets loaded.', 'react-design-editor' ) );
    } else {
        console.info( 'React Design Editor frontend assets loaded.' );
    }
} )();
