import { useSelect } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';

import Panel from './components/Panel';

import './editor.scss';

const Guide = () => (
	<Panel
		pages={ useSelect( ( select ) => {
			const { getEntityRecords } = select( 'core' );
			const terms = getEntityRecords( 'taxonomy', 'wp_guide_posts' );

			if ( ! Array.isArray( terms ) ) {
				return [];
			}

			const { getCurrentPostType } = select( 'core/editor' );
			const postType = getCurrentPostType();

			const termsIds = terms
				.map( ( term ) => ( term.slug === postType ? term.id : '' ) )
				.filter( Number );

			if ( ! termsIds.length ) {
				return [];
			}

			return (
				getEntityRecords( 'postType', 'wp_guide', {
					wp_guide_posts: termsIds,
					sorted: true,
					per_page: -1
				} ) || []
			);
		} ) }
	/>
);

registerPlugin( 'innocode-guide', {
	render: Guide,
	icon: 'book',
} );
