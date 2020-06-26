import GuideButton from "./button"

const { PluginDocumentSettingPanel } = wp.editPost

export default ( props ) => {
	const { pages } = props

	return (
		<>
			<PluginDocumentSettingPanel
				name="wp-guide-panel"
				title="Useful Information"
			>
				<GuideButton pages={pages}/>
			</PluginDocumentSettingPanel>
		</>
	)
}
