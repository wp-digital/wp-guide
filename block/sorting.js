import Sortable from 'sortablejs';

import domReady from '@wordpress/dom-ready';

domReady( () => {
	const guides = document.getElementsByClassName(
		'innocode-guide-sorting__guides'
	);

	[ ...guides ].forEach( ( list ) => {
		Sortable.create( list, {
			store: {
				set( sortable ) {
					const ids = sortable.toArray();
					const order = document.getElementById(
						`innocode-guide-order-${ list.dataset.screenId }`
					);

					order.value = ids.join( ',' );
				},
			},
		} );
	} );
} );
