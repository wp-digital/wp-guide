import parse from 'html-react-parser';

import { useEffect, useState } from '@wordpress/element';
import { Button, Guide } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Body( props ) {
	const { pages } = props;
	const [ isOpen, setOpen ] = useState( false );

	useEffect( () => {
		const element = document.querySelector( '.components-guide' );

		if ( ! element ) {
			return;
		}

		element.scrollTop = 0;
	}, [ props ] );

	return (
		<>
			<Button isPrimary onClick={ () => setOpen( true ) }>
				{ __( 'Open Guide', 'innocode-guide' ) }
			</Button>
			{ isOpen && (
				<Guide
					onFinish={ () => setOpen( false ) }
					pages={ pages.map(
						( {
							title: { rendered: title },
							content: { rendered: content },
						} ) => ( {
							content: (
								<>
									<h3 className="innocode-guide__title">
										{ parse( title ) }
									</h3>
									<div className="innocode-guide__content">
										{ parse( content ) }
									</div>
								</>
							),
						} )
					) }
					className="innocode-guide"
				/>
			) }
		</>
	);
}
