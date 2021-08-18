import { GuidePage } from '@wordpress/components'
import { useEffect } from '@wordpress/element'
import parse from 'html-react-parser'

const PageHOC = (props) => {
	const { page } = props

	useEffect(() => {
		const element = document.querySelector('.components-guide')

		if (!element) {
			return
		}

		element.scrollTop = 0
	}, [props])

	return <GuidePage>
		{parse(page.title.rendered)}
		{parse(page.content.rendered)}
	</GuidePage>
}

export default PageHOC
