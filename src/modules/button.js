import { useState } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import GuideWindow from './guide'

export default ( props ) => {
	const [ isOpen, setOpen ] = useState( false )
	const { pages } = props

	return (
		<>
			<Button onClick={ () => setOpen( true ) }>
				{ __('Open Guide') }
			</Button>
			{ isOpen && (
				<GuideWindow
					pages={pages}
					onFinish={ () => setOpen( false ) }
				/>
			) }
		</>
	)
}
