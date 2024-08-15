import { createRoot } from '@wordpress/element';

import DesignPackNotice from './components/DesignPackNotice';

const container = document.getElementById( 'fork-design-pack-notice' );

if ( container ) {
	createRoot( container ).render( <DesignPackNotice /> );
}
