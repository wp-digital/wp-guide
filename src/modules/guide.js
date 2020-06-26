import { Guide, GuidePage } from '@wordpress/components'
import parse from 'html-react-parser'

export default ( props ) => {
	const { pages } = props

	return (
		<Guide { ...props }>
			{ pages.length > 0 ?
				pages.map( page => (
					<GuidePage key={page.id}>
						{parse(page.title.rendered)}
						{parse(page.content.rendered)}
					</GuidePage>
				) )
				: ''
			}
		</Guide>
	)
}
