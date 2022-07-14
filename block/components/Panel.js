import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';

import Body from './Body';

export default function Panel( props ) {
	const { pages } = props;

	return (
		pages.length > 0 && (
			<PluginDocumentSettingPanel
				name="innocode-guide-panel"
				title={ __( 'Useful Information', 'innocode-guide' ) }
			>
				<Body pages={ pages } />
			</PluginDocumentSettingPanel>
		)
	);
}
