import { Guide } from '@wordpress/components'
import PageHOC from './PageHOC.js'

export default ( props ) => {
	const { pages } = props

	return (
		<Guide { ...props }>
			{ pages.length > 0 ?
				pages.map( page => (
					<PageHOC key={page.id} page={page} />
				) )
				: ''
			}
		</Guide>
	)
}
