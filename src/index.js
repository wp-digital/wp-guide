import GuidePanel from "./modules/panel"
import './sass/editor.scss'

const { registerPlugin } = wp.plugins
const { useSelect } = wp.data

const getGuidePages = () => useSelect( select => {
	const { getCurrentPostType } = select( 'core/editor' )
	const { getEntityRecords } = select( 'core' )

	let postType, terms, terms_ids, pages

	postType = getCurrentPostType()
	terms = getEntityRecords( 'taxonomy', 'wp_guide_posts', {
		fromMainSite: true
	} )

	terms_ids = terms ? terms.map( term => term.slug === postType ? term.id : '' ) : []
	terms_ids = terms_ids.filter( Number )

	if( terms_ids.length > 0 ) {
		let args = {
			wp_guide_posts: terms_ids,
			sorted: true,
			fromMainSite: true
		}
		pages = getEntityRecords( 'postType', 'wp_guide', args )
	} else {
		pages = []
	}

	return pages
})


const WPGuide = ( ) => {
	return <GuidePanel pages={getGuidePages()} />
}

registerPlugin( 'wp-guide-panel', {
	render: WPGuide,
	icon: 'book'
} )
